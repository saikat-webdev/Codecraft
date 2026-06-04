# Module 07 — n8n Workflow

## Purpose

Provides a no-code/low-code orchestration of the entire AI Teacher pipeline using n8n. Mirrors the Node.js service exactly — useful for rapid prototyping, non-developer teams, or running as a standalone webhook service.

---

## Workflow Overview

```
Webhook Input
    │
    ▼
Validate Input
    │
    ├──────────────────────┐
    ▼                      ▼
Fetch User Progress    Fetch Memory
    │                      │
    └──────────┬───────────┘
               ▼
        OpenAI Embedding
               │
               ▼
        Supabase RAG Query
               │
               ▼
        Context Builder (Code node)
               │
               ▼
           Grok API
               │
               ▼
        Parse Response (Code node)
               │
    ┌──────────┴──────────┐
    ▼                     ▼
Send Response        Save to Memory
(Respond to Webhook)  (Supabase insert)
```

---

## Node Reference

### 1. Webhook Input

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.webhook` |
| Method | `POST` |
| Path | `/ai-teacher` |
| Response mode | `responseNode` (manual — waits for "Send Response" node) |

**Receives:**
```json
{
  "question": "What is a closure?",
  "user_id":  "uuid-here",
  "lesson_id": "uuid-here (optional)"
}
```

---

### 2. Validate Input

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.code` |
| Language | JavaScript |

Extracts and validates `question`, `user_id`, `lesson_id`. Throws if either required field is missing.

```js
const body    = $input.first().json.body ?? $input.first().json;
const question = body.question?.trim();
const userId   = body.user_id;
const lessonId = body.lesson_id ?? null;

if (!question || !userId) throw new Error('question and user_id are required');
return [{ json: { question, userId, lessonId } }];
```

---

### 3. Fetch User Progress *(runs in parallel with Fetch Memory)*

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.httpRequest` |
| Method | `GET` |
| URL | `{SUPABASE_URL}/rest/v1/user_progress` |
| Auth | Supabase service key in `apikey` and `Authorization` headers |

**Query parameters:**
```
user_id=eq.{userId}
select=*,lessons(title,order_index,modules(title,roadmaps(title)))
order=updated_at.desc
limit=20
```

---

### 4. Fetch Memory *(runs in parallel with Fetch User Progress)*

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.httpRequest` |
| Method | `GET` |
| URL | `{SUPABASE_URL}/rest/v1/ai_memory` |

**Query parameters:**
```
user_id=eq.{userId}
select=role,content
order=created_at.desc
limit=5
```

Returns the last 5 messages (reversed to chronological order in Context Builder).

---

### 5. OpenAI Embedding

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.httpRequest` |
| Method | `POST` |
| URL | `https://api.openai.com/v1/embeddings` |
| Auth | OpenAI API key in `Authorization: Bearer` header |

**Body:**
```json
{
  "model": "text-embedding-3-small",
  "input": "{{ $('Validate Input').first().json.question }}",
  "encoding_format": "float"
}
```

**Output used:** `data[0].embedding` (1536-dim float array)

---

### 6. Supabase RAG Query

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.httpRequest` |
| Method | `POST` |
| URL | `{SUPABASE_URL}/rest/v1/rpc/match_lesson_chunks` |

**Body:**
```json
{
  "query_embedding": "{{ $json.data[0].embedding }}",
  "p_roadmap_id":    "{{ roadmapId from progress }}",
  "match_threshold": 0.7,
  "match_count":     5
}
```

---

### 7. Context Builder *(Code node)*

Assembles the `messages` array for Grok. Reads from all upstream nodes:

- `$('Validate Input')` → question
- `$('Fetch User Progress')` → progress array
- `$('Fetch Memory')` → memory array
- `$('Supabase RAG Query')` → RAG chunks

Outputs: `{ messages: [...], question, userId }`

---

### 8. Grok API

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.httpRequest` |
| Method | `POST` |
| URL | `https://api.x.ai/v1/chat/completions` |
| Auth | Grok API key in `Authorization: Bearer` header |
| Timeout | 30 000 ms |

**Body:**
```json
{
  "model":       "grok-3-mini",
  "messages":    "{{ $json.messages }}",
  "temperature": 0.7,
  "max_tokens":  1500
}
```

---

### 9. Parse Response *(Code node)*

Extracts `choices[0].message.content` and prepares two memory insert objects:

```js
const answer = $('Grok API').first().json.choices[0].message.content;
return [{
  json: {
    answer,
    userId,
    userMsg:      { user_id: userId, role: 'user',      content: question },
    assistantMsg: { user_id: userId, role: 'assistant', content: answer },
    usage:        grokResponse.usage,
    model:        grokResponse.model,
  }
}];
```

---

### 10. Send Response *(Respond to Webhook)*

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.respondToWebhook` |
| Status code | `200` |

**Returns:**
```json
{
  "success": true,
  "answer":  "...",
  "model":   "grok-3-mini",
  "usage":   { "prompt_tokens": 420, "completion_tokens": 310 }
}
```

---

### 11. Save to Memory *(runs in parallel with Send Response)*

| Property | Value |
|----------|-------|
| Type | `n8n-nodes-base.httpRequest` |
| Method | `POST` |
| URL | `{SUPABASE_URL}/rest/v1/ai_memory` |
| Body | `[userMsg, assistantMsg]` (batch insert) |

---

## n8n Credentials Setup

| Credential name | Type | Value |
|----------------|------|-------|
| `Supabase API Key` | HTTP Header Auth | Header: `apikey`, Value: `SUPABASE_SERVICE_KEY` |
| `OpenAI API Key` | HTTP Header Auth | Header: `Authorization`, Value: `Bearer {OPENAI_API_KEY}` |
| `Grok API Key` | HTTP Header Auth | Header: `Authorization`, Value: `Bearer {GROK_API_KEY}` |

---

## Importing the Workflow

1. Open n8n → **Workflows** → **Import from file**
2. Select `n8n/codecraft-ai-teacher.workflow.json` from this repo
3. Set credentials for all three HTTP Header Auth entries
4. Set environment variables `SUPABASE_URL`, etc. in n8n settings
5. Activate the workflow
6. Test: `POST http://your-n8n-host/webhook/ai-teacher`

---

## Limitations vs Node.js Service

| Feature | n8n Workflow | Node.js Service |
|---------|-------------|-----------------|
| Redis caching | ❌ | ✅ |
| Retry logic | Manual (error node) | ✅ Built-in |
| Token optimization | ❌ | ✅ |
| Parallel fetch (progress + memory) | ✅ (parallel branches) | ✅ `Promise.all` |
| Production scale | Medium | High |
