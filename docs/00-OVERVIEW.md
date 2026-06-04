# AI Teacher System — Architecture Overview

**Project:** Codecraft AI Teacher Module  
**Stack:** Grok API (xAI) · OpenAI Embeddings · Supabase (pgvector) · n8n · Node.js · Laravel  
**Purpose:** Personalized, RAG-powered AI teacher that answers student questions, tracks progress, and suggests next lessons.

---

## Module Index

| # | Module | File | Responsibility |
|---|--------|------|----------------|
| 1 | System Architecture | [01-ARCHITECTURE.md](./01-ARCHITECTURE.md) | End-to-end flow, data models, infra |
| 2 | Grok API Service | [02-GROK-SERVICE.md](./02-GROK-SERVICE.md) | LLM calls, retry logic, model config |
| 3 | OpenAI Embedding Service | [03-EMBEDDING-SERVICE.md](./03-EMBEDDING-SERVICE.md) | Vector generation for RAG |
| 4 | RAG Service (Supabase) | [04-RAG-SERVICE.md](./04-RAG-SERVICE.md) | Vector search, progress fetch, memory I/O |
| 5 | Context Builder | [05-CONTEXT-BUILDER.md](./05-CONTEXT-BUILDER.md) | Assembles system prompt from all inputs |
| 6 | Prompt Template | [06-PROMPT-TEMPLATE.md](./06-PROMPT-TEMPLATE.md) | Grok prompt design, variables, rules |
| 7 | n8n Workflow | [07-N8N-WORKFLOW.md](./07-N8N-WORKFLOW.md) | Node-by-node workflow documentation |
| 8 | Node.js API | [08-NODEJS-API.md](./08-NODEJS-API.md) | Express service, routes, project structure |
| 9 | Laravel API | [09-LARAVEL-API.md](./09-LARAVEL-API.md) | Controller, Service classes, routes |
| 10 | Error Handling & Performance | [10-ERROR-PERFORMANCE.md](./10-ERROR-PERFORMANCE.md) | Fallbacks, retry, caching, token optimization |

---

## High-Level Flow (30 seconds)

```
User Question
    │
    ├─► OpenAI → generate embedding
    ├─► Supabase → fetch user progress
    └─► Supabase → fetch last 5 memory messages
              │
              ▼
    Supabase pgvector → match top-5 lesson chunks
              │
              ▼
    Context Builder → system prompt + RAG + progress + memory
              │
              ▼
    Grok API → teaching-style response
              │
              ├─► Save to ai_memory (non-blocking)
              └─► Return JSON to client
```

---

## Core Constraints

| Constraint | Rule |
|------------|------|
| LLM | **Grok API only** — never Gemini, Claude, or GPT for generation |
| Embeddings | **OpenAI only** — `text-embedding-3-small` |
| Vector DB | **Supabase** with `pgvector` extension |
| Automation | **n8n** for workflow orchestration |
| Multi-tenant | All queries filtered by `user_id` and `roadmap_id` |

---

## Environment Variables (all modules)

```ini
GROK_API_KEY=xai-xxxxxxxxxxxxxxxx
OPENAI_API_KEY=sk-xxxxxxxxxxxxxxxx
SUPABASE_URL=https://xxxx.supabase.co
SUPABASE_SERVICE_KEY=eyJ...
REDIS_URL=redis://localhost:6379
NODE_ENV=production
PORT=3000
```
