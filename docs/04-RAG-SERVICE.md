# Module 04 — RAG Service (Supabase)

## Purpose

Manages all Supabase interactions: vector similarity search for RAG, user progress retrieval, next-lesson resolution, and conversation memory read/write.

---

## File Location

```
src/services/ragService.js           (Node.js)
app/Services/RagService.php          (Laravel — extend as needed)
```

---

## Supabase Setup

### Required Extension

```sql
create extension if not exists vector;
```

### Index (run once after populating `lesson_chunks`)

```sql
create index on lesson_chunks
  using ivfflat (embedding vector_cosine_ops)
  with (lists = 100);
```

`lists = 100` is appropriate for up to ~1M rows. Increase to `200` for larger datasets.

---

## RPC Function: `match_lesson_chunks`

The core vector search function. Runs inside Postgres — no data leaves Supabase.

```sql
create or replace function match_lesson_chunks(
  query_embedding vector(1536),
  p_roadmap_id    uuid,
  match_threshold float default 0.7,
  match_count     int   default 5
)
returns table (
  id         uuid,
  lesson_id  uuid,
  content    text,
  metadata   jsonb,
  similarity float
)
language sql stable
as $$
  select
    lc.id,
    lc.lesson_id,
    lc.content,
    lc.metadata,
    1 - (lc.embedding <=> query_embedding) as similarity
  from lesson_chunks lc
  where
    lc.roadmap_id = p_roadmap_id
    and 1 - (lc.embedding <=> query_embedding) > match_threshold
  order by lc.embedding <=> query_embedding
  limit match_count;
$$;
```

### Parameters

| Parameter | Type | Default | Notes |
|-----------|------|---------|-------|
| `query_embedding` | vector(1536) | required | From OpenAI embedding service |
| `p_roadmap_id` | uuid | required | Filters chunks to user's active roadmap |
| `match_threshold` | float | `0.7` | Cosine similarity floor (0–1); lower = more results |
| `match_count` | int | `5` | Max chunks returned |

### Tuning `match_threshold`

| Value | Effect |
|-------|--------|
| `0.9+` | Very strict — only near-identical matches |
| `0.7–0.8` | Recommended — relevant, minimal noise |
| `0.5–0.6` | Loose — may include tangentially related content |
| `< 0.5` | Avoid — too much noise in context window |

---

## Node.js Implementation

```js
// src/services/ragService.js
const { createClient } = require('@supabase/supabase-js');

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_KEY
);

// ── Vector Search ──────────────────────────────────────────────────────────
async function retrieveRelevantChunks(embedding, roadmapId, options = {}) {
  const { threshold = 0.7, count = 5 } = options;

  const { data, error } = await supabase.rpc('match_lesson_chunks', {
    query_embedding: embedding,
    p_roadmap_id:    roadmapId,
    match_threshold: threshold,
    match_count:     count,
  });

  if (error) throw new Error(`RAG query failed: ${error.message}`);
  return data ?? [];
}

// ── User Progress ──────────────────────────────────────────────────────────
async function getUserProgress(userId) {
  const { data, error } = await supabase
    .from('user_progress')
    .select(`
      *,
      lessons (id, title, order_index, module_id,
        modules (id, title, roadmap_id,
          roadmaps (id, title)
        )
      )
    `)
    .eq('user_id', userId)
    .order('updated_at', { ascending: false });

  if (error) throw new Error(`Progress fetch failed: ${error.message}`);
  return data ?? [];
}

// ── Next Lesson ────────────────────────────────────────────────────────────
async function getNextLesson(userId, roadmapId) {
  const { data, error } = await supabase
    .from('lessons')
    .select('*, user_progress!left(status, user_id)')
    .eq('modules.roadmap_id', roadmapId)
    .or(`user_progress.user_id.is.null,user_progress.user_id.eq.${userId}`)
    .is('user_progress.status', null)
    .order('order_index')
    .limit(1)
    .single();

  if (error && error.code !== 'PGRST116') throw error;
  return data;
}

// ── Memory Read ────────────────────────────────────────────────────────────
async function getConversationMemory(userId, limit = 5) {
  const { data, error } = await supabase
    .from('ai_memory')
    .select('role, content')
    .eq('user_id', userId)
    .order('created_at', { ascending: false })
    .limit(limit);

  if (error) throw new Error(`Memory fetch failed: ${error.message}`);
  return (data ?? []).reverse(); // return in chronological order
}

// ── Memory Write ───────────────────────────────────────────────────────────
async function saveToMemory(userId, role, content, lessonId = null) {
  const { error } = await supabase.from('ai_memory').insert({
    user_id:   userId,
    role,
    content,
    lesson_id: lessonId,
  });

  if (error) throw new Error(`Memory save failed: ${error.message}`);
}

module.exports = {
  retrieveRelevantChunks,
  getUserProgress,
  getNextLesson,
  getConversationMemory,
  saveToMemory,
};
```

---

## Memory Cleanup (Supabase RPC)

Prevents unbounded growth in `ai_memory`:

```sql
create or replace function trim_user_memory(p_user_id uuid, p_keep int default 20)
returns void
language sql
as $$
  delete from ai_memory
  where user_id = p_user_id
    and id not in (
      select id from ai_memory
      where user_id = p_user_id
      order by created_at desc
      limit p_keep
    );
$$;
```

Call after every memory write:

```js
await supabase.rpc('trim_user_memory', { p_user_id: userId, p_keep: 20 });
```

---

## Data Flow: RAG Query

```
question (string)
    │
    ▼ embeddingService.generateEmbedding()
embedding (float[1536])
    │
    ▼ supabase.rpc('match_lesson_chunks', { embedding, roadmapId, threshold, count })
chunks[] = [
  { id, lesson_id, content, metadata, similarity: 0.84 },
  { id, lesson_id, content, metadata, similarity: 0.79 },
  ...
]
    │
    ▼ contextBuilder.buildSystemPrompt(progress, chunks)
system prompt with injected RAG context
```

---

## Multi-tenancy Guarantee

Every query is scoped to `roadmap_id` (derived from `user_id` → `user_progress` → active roadmap). Users **never** see content from roadmaps they are not enrolled in.

```sql
-- roadmap_id is always passed from the server — never from client input
where lc.roadmap_id = p_roadmap_id
```

---

## Error Scenarios

| Scenario | Behaviour |
|----------|-----------|
| Zero chunks returned | Returns `[]` — Context Builder uses fallback message |
| Supabase connection error | Throws `Error` — caught by route handler |
| Invalid `roadmap_id` | Returns `[]` — function is `stable`, no write side effects |
| Memory table write fails | Logged but does not fail the main response |
