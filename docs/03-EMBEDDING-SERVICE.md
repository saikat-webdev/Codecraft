# Module 03 — OpenAI Embedding Service

## Purpose

Converts text (student questions, lesson content) into 1536-dimensional vectors using OpenAI's `text-embedding-3-small` model. These vectors power semantic similarity search in Supabase.

> **Rule:** Embeddings must always use **OpenAI**. Do not use any other embedding provider.

---

## File Location

```
src/services/embeddingService.js     (Node.js)
app/Services/EmbeddingService.php    (Laravel)
```

---

## API Reference

| Property | Value |
|----------|-------|
| Base URL | `https://api.openai.com/v1` |
| Endpoint | `POST /embeddings` |
| Auth header | `Authorization: Bearer {OPENAI_API_KEY}` |
| Model | `text-embedding-3-small` |
| Output dimensions | `1536` |
| Max input length | `8191 tokens` (~32k chars) |

---

## Why `text-embedding-3-small`?

| Model | Dimensions | Cost | Quality | Decision |
|-------|-----------|------|---------|----------|
| `text-embedding-3-small` | 1536 | Low | High | ✅ **Used** |
| `text-embedding-3-large` | 3072 | Higher | Slightly better | Overkill for lesson RAG |
| `text-embedding-ada-002` | 1536 | Medium | Lower | Older generation |

---

## Single Embedding Request

```json
POST https://api.openai.com/v1/embeddings
{
  "model": "text-embedding-3-small",
  "input": "What is a closure in JavaScript?",
  "encoding_format": "float"
}
```

### Response

```json
{
  "object": "list",
  "data": [
    {
      "object": "embedding",
      "index": 0,
      "embedding": [0.0023, -0.009, 0.012, ... ]  // 1536 floats
    }
  ],
  "model": "text-embedding-3-small",
  "usage": {
    "prompt_tokens": 8,
    "total_tokens": 8
  }
}
```

---

## Node.js Implementation

```js
// src/services/embeddingService.js
const OpenAI = require('openai');

const openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });

// Single text → vector
async function generateEmbedding(text) {
  const input = text.replace(/\n/g, ' ').trim().slice(0, 8000);

  const response = await openai.embeddings.create({
    model:           'text-embedding-3-small',
    input,
    encoding_format: 'float',
  });

  return response.data[0].embedding; // float[]
}

// Batch texts → vectors (more efficient for ingestion)
async function generateBatchEmbeddings(texts) {
  const inputs = texts.map(t => t.replace(/\n/g, ' ').trim().slice(0, 8000));

  const response = await openai.embeddings.create({
    model:           'text-embedding-3-small',
    input:           inputs,
    encoding_format: 'float',
  });

  return response.data.map(d => d.embedding);
}

module.exports = { generateEmbedding, generateBatchEmbeddings };
```

---

## Laravel Implementation

```php
// app/Services/EmbeddingService.php
public function embed(string $text): array
{
    $input = substr(str_replace("\n", ' ', trim($text)), 0, 8000);

    $response = Http::withToken($this->apiKey)
        ->timeout(15)
        ->post('https://api.openai.com/v1/embeddings', [
            'model'           => 'text-embedding-3-small',
            'input'           => $input,
            'encoding_format' => 'float',
        ]);

    if (!$response->successful()) {
        throw new \Exception("Embedding failed: {$response->body()}");
    }

    return $response->json('data.0.embedding'); // float[]
}
```

---

## Pre-processing Rules

Before sending text to the embedding API, always apply:

1. **Newline collapse:** `\n` → single space — prevents token fragmentation
2. **Trim whitespace:** removes leading/trailing spaces
3. **Length cap:** truncate at 8000 characters — stays safely under token limit

```js
const input = text.replace(/\n/g, ' ').trim().slice(0, 8000);
```

---

## Lesson Ingestion Flow

When adding new lesson content to Supabase, use batch embeddings:

```js
const { generateBatchEmbeddings } = require('./embeddingService');
const { createClient } = require('@supabase/supabase-js');

async function ingestLesson(lessonId, chunks) {
  const texts    = chunks.map(c => c.content);
  const vectors  = await generateBatchEmbeddings(texts);

  const rows = chunks.map((chunk, i) => ({
    lesson_id:   lessonId,
    chunk_index: i,
    content:     chunk.content,
    embedding:   vectors[i],
    metadata:    chunk.metadata ?? {},
  }));

  await supabase.from('lesson_chunks').insert(rows);
}
```

---

## Caching Strategy

Embeddings are **deterministic** — the same input always produces the same vector. Cache aggressively:

```js
const key = `emb:${sha256(text)}`;
const cached = await redis.get(key);
if (cached) return JSON.parse(cached);

const embedding = await generateEmbedding(text);
await redis.setex(key, 3600, JSON.stringify(embedding)); // 1 hour TTL
```

See [10-ERROR-PERFORMANCE.md](./10-ERROR-PERFORMANCE.md) for full caching implementation.

---

## Token Cost Estimation

| Input | Approx tokens | Approx cost |
|-------|--------------|-------------|
| Short question (10 words) | ~10 tokens | ~$0.000002 |
| Long question (100 words) | ~130 tokens | ~$0.000026 |
| Lesson chunk (500 words) | ~650 tokens | ~$0.00013 |

Cost at `text-embedding-3-small`: **$0.02 per 1M tokens** (as of model release).

---

## Error Scenarios

| Scenario | Behaviour |
|----------|-----------|
| Invalid API key | Throws 401 immediately |
| Input too long | Pre-truncated — never reaches API |
| Network timeout | Propagates as `Error` to calling service |
| Rate limit (429) | Not auto-retried here; caller should wrap with `withRetry` |
