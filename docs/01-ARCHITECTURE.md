# Module 01 — System Architecture

## Purpose

Defines the end-to-end data flow, database schema, and infrastructure layout for the Codecraft AI Teacher system.

---

## Full System Flow

```
┌─────────────────────────────────────────────────────────────┐
│                      CLIENT REQUEST                         │
│         { question, user_id, lesson_id? }                   │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│                 API GATEWAY (Express / Laravel)              │
│            Auth → Rate Limit → Input Validation             │
└────┬──────────────────────┬──────────────────────┬──────────┘
     │                      │                      │
     ▼                      ▼                      ▼
┌─────────┐        ┌────────────────┐     ┌────────────────┐
│  Cache  │        │  User Context  │     │  Memory Store  │
│ (Redis) │        │  (Supabase DB) │     │ (last 5 msgs)  │
└─────────┘        └───────┬────────┘     └───────┬────────┘
                           │                      │
                           └──────────┬───────────┘
                                      │
                                      ▼
                          ┌────────────────────┐
                          │  OpenAI Embedding  │
                          │ text-embedding-3-  │
                          │      small         │
                          └─────────┬──────────┘
                                    │
                                    ▼
                          ┌────────────────────┐
                          │  Supabase RAG      │
                          │  pgvector search   │
                          │  (top-5 chunks)    │
                          └─────────┬──────────┘
                                    │
                                    ▼
                  ┌─────────────────────────────────┐
                  │         CONTEXT BUILDER         │
                  │  RAG docs + progress + memory   │
                  └────────────────┬────────────────┘
                                   │
                                   ▼
                  ┌─────────────────────────────────┐
                  │       GROK API  (xAI)           │
                  │   grok-3 / grok-3-mini          │
                  │   structured teaching prompt    │
                  └────────────────┬────────────────┘
                                   │
                                   ▼
                  ┌─────────────────────────────────┐
                  │       RESPONSE FORMATTER        │
                  │  explanation + example + next   │
                  └────────┬────────────────────────┘
                           │
              ┌────────────┴────────────┐
              ▼                         ▼
  ┌─────────────────┐       ┌─────────────────────┐
  │  Return JSON    │       │  Save to ai_memory  │
  │  to client      │       │  (non-blocking)     │
  └─────────────────┘       └─────────────────────┘
```

---

## Database Schema

### Table: `lesson_chunks` (RAG source)

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid | Primary key |
| `lesson_id` | uuid | FK → lessons |
| `module_id` | uuid | Denormalized for fast filtering |
| `roadmap_id` | uuid | Denormalized for fast filtering |
| `chunk_index` | int | Order within lesson |
| `content` | text | Raw lesson text chunk |
| `metadata` | jsonb | Tags, difficulty, etc. |
| `embedding` | vector(1536) | OpenAI embedding |
| `created_at` | timestamptz | |

### Table: `user_progress`

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid | Primary key |
| `user_id` | uuid | Multi-tenant key |
| `lesson_id` | uuid | FK → lessons |
| `module_id` | uuid | Denormalized |
| `roadmap_id` | uuid | Denormalized |
| `status` | text | `not_started` \| `in_progress` \| `completed` |
| `score` | int | Quiz/test score (nullable) |
| `completed_at` | timestamptz | When lesson was completed |

### Table: `ai_memory`

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid | Primary key |
| `user_id` | uuid | Multi-tenant key |
| `role` | text | `user` \| `assistant` |
| `content` | text | Message text |
| `lesson_id` | uuid | Context lesson (nullable) |
| `created_at` | timestamptz | For ordering and TTL cleanup |

---

## Infrastructure Layout

```
Codecraft Platform
│
├── Frontend (React/Vue)
│       └── POST /api/teacher/ask
│
├── API Layer
│   ├── Node.js (Express)  — microservice / standalone
│   └── Laravel            — integrated into monolith
│
├── External APIs
│   ├── api.x.ai           — Grok (LLM)
│   └── api.openai.com     — Embeddings only
│
├── Supabase
│   ├── PostgreSQL + pgvector
│   ├── lesson_chunks
│   ├── user_progress
│   └── ai_memory
│
├── Redis                  — embedding + RAG + progress cache
│
└── n8n                    — workflow automation / webhook trigger
```

---

## Key Design Decisions

| Decision | Reason |
|----------|--------|
| Denormalize `roadmap_id` on `lesson_chunks` | Avoids JOINs on every vector search; keeps RPC query simple |
| Non-blocking memory save | Response latency is not held up by a write; fire-and-forget |
| `match_threshold = 0.68` | Tuned to avoid irrelevant chunks while still matching paraphrased questions |
| Redis TTL: embeddings 1h, RAG 5min, progress 1min | Embeddings are deterministic; progress changes frequently |
| IVFFlat index with `lists = 100` | Good balance for datasets up to ~1M chunks |
