# Module 10 — Error Handling & Performance Optimization

## Purpose

Defines fallback strategies for every failure mode, and implements caching, token reduction, and latency optimizations to keep the AI Teacher fast and resilient under load.

---

## Error Handling

### Failure Map

| Failure | Where | Strategy |
|---------|-------|----------|
| OpenAI embedding fails | `embeddingService` | Throw → route handler returns 500 |
| RAG returns 0 results | `ragService` | Return `[]` → Context Builder injects fallback message |
| Grok API error (4xx) | `grokService` | Throw immediately (not retried) |
| Grok API error (5xx / 429) | `grokService` | Retry ×3 with exponential backoff |
| Grok API timeout | `grokService` | Counted as retry attempt |
| All Grok retries exhausted | `grokService` | Throw → route handler returns 500 + fallback text |
| Memory save fails | `ragService` | Logged, does NOT fail the response |
| Supabase progress fetch fails | `ragService` | Throw → 500 (cannot build context without it) |
| Redis unavailable | `cache.js` | Bypass cache, call API directly |

---

### Retry Logic (Node.js)

```js
// Exponential backoff: 500ms → 1000ms → 2000ms
for (let attempt = 1; attempt <= retries; attempt++) {
  try {
    return await callApi();
  } catch (error) {
    const status = error.response?.status;
    const retryable = !status || status === 429 || status >= 500;
    if (!retryable || attempt === retries) throw error;
    await new Promise(r => setTimeout(r, retryDelay * Math.pow(2, attempt - 1)));
  }
}
```

### Retry Logic (Laravel)

```php
$attempt = 0;
while ($attempt < $this->maxRetries) {
    $response = Http::withToken($this->apiKey)->post(...);
    if ($response->successful()) return $this->parseResponse($response);
    if ($response->status() < 500 && $response->status() !== 429) throw new Exception(...);
    $attempt++;
    if ($attempt < $this->maxRetries) sleep((int) pow(2, $attempt - 1));
}
throw new Exception('API failed after retries');
```

---

### Fallback Responses

```js
// services/errorHandler.js

const FALLBACKS = {
  no_rag: () =>
    `I couldn't find specific course material for this question, ` +
    `but here's what I know based on general programming knowledge:\n\n` +
    `[Grok will still answer from training data — RAG context is just missing]`,

  api_failure: () =>
    `I'm temporarily unavailable. Please try again in a moment. ` +
    `If the issue continues, contact your instructor.`,
};

async function withFallback(fn, fallbackType) {
  try {
    return await fn();
  } catch (error) {
    console.error(`[${fallbackType}]`, error.message);
    return { content: FALLBACKS[fallbackType]?.() ?? FALLBACKS.api_failure() };
  }
}
```

**No-RAG fallback in Context Builder:**
```js
const ragContext = ragChunks.length > 0
  ? ragChunks.map((c, i) => `[Source ${i + 1}]\n${c.content}`).join('\n\n')
  : 'No specific lesson content matched. Answer from general programming knowledge.';
```
Grok will still produce a valid teaching response — it just won't cite course material.

---

## Performance Optimization

### 1. Redis Caching

Three distinct cache layers — each with a TTL matched to how often the data changes:

```js
// middleware/cache.js
const Redis  = require('ioredis');
const crypto = require('crypto');

const redis = new Redis(process.env.REDIS_URL);

// Embeddings: deterministic — cache indefinitely (1h is conservative)
async function getCachedEmbedding(text, generateFn) {
  const key    = `emb:${crypto.createHash('sha256').update(text).digest('hex')}`;
  const cached = await redis.get(key);
  if (cached) return JSON.parse(cached);

  const embedding = await generateFn(text);
  await redis.setex(key, 3600, JSON.stringify(embedding));
  return embedding;
}

// RAG results: cache per (query-hash, roadmapId)
async function getCachedRag(textHash, roadmapId, queryFn) {
  const key    = `rag:${textHash}:${roadmapId}`;
  const cached = await redis.get(key);
  if (cached) return JSON.parse(cached);

  const results = await queryFn();
  await redis.setex(key, 300, JSON.stringify(results)); // 5 min
  return results;
}

// Progress: short TTL — user can complete lessons between calls
async function getCachedProgress(userId, fetchFn) {
  const key    = `progress:${userId}`;
  const cached = await redis.get(key);
  if (cached) return JSON.parse(cached);

  const progress = await fetchFn();
  await redis.setex(key, 60, JSON.stringify(progress)); // 1 min
  return progress;
}

// Call this from lesson-completion webhook to invalidate immediately
async function invalidateProgressCache(userId) {
  await redis.del(`progress:${userId}`);
}

module.exports = { getCachedEmbedding, getCachedRag, getCachedProgress, invalidateProgressCache };
```

### Cache TTL Summary

| Cache key | TTL | Reason |
|-----------|-----|--------|
| `emb:{hash}` | 1 hour | Embeddings are deterministic |
| `rag:{hash}:{roadmapId}` | 5 minutes | Content rarely changes mid-session |
| `progress:{userId}` | 1 minute | User may complete lessons between questions |

**Redis unavailable fallback:**
```js
try {
  return await getCachedEmbedding(text, generateFn);
} catch (redisError) {
  console.warn('Redis unavailable, calling API directly');
  return generateFn(text);
}
```

---

### 2. Token Reduction

Keeping the prompt compact reduces cost and latency.

```js
// services/tokenOptimizer.js

// Trim RAG chunks to avoid bloating the context window
function truncateRagChunks(chunks, maxCharsPerChunk = 800) {
  return chunks.map(c => ({
    ...c,
    content: c.content.length > maxCharsPerChunk
      ? c.content.slice(0, maxCharsPerChunk) + '...'
      : c.content,
  }));
}

// Filter memory to only include messages that share keywords with the question
function filterRelevantMemory(memory, question, limit = 5) {
  const qWords = new Set(
    question.toLowerCase().split(/\s+/).filter(w => w.length > 3)
  );

  const scored = memory.map(msg => {
    const overlap = msg.content
      .toLowerCase()
      .split(/\s+/)
      .filter(w => qWords.has(w)).length;
    return { ...msg, score: overlap };
  });

  return scored
    .sort((a, b) => b.score - a.score)
    .slice(0, limit)
    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
}

module.exports = { truncateRagChunks, filterRelevantMemory };
```

### Token Budget Per Request

| Prompt section | Target tokens |
|----------------|--------------|
| System prompt + rules | ~200 |
| Progress summary | ~50 |
| RAG (5 chunks × 160 tokens) | ~800 |
| Memory (5 turns × 130 tokens) | ~650 |
| Current question | ~50 |
| **Total input** | **~1750** |
| Grok response (`max_tokens: 1500`) | ~1500 |
| **Total per call** | **~3250 tokens** |

---

### 3. Latency Improvements

#### Parallel Data Fetching

Embedding, progress, and memory fetches run simultaneously:

```js
const [embedding, progress, memory] = await Promise.all([
  generateEmbedding(question),
  getUserProgress(user_id),
  getConversationMemory(user_id, 5),
]);
```

Without parallelism: `~150ms + ~80ms + ~80ms = ~310ms`  
With `Promise.all`: `~150ms` (bounded by the slowest single call)

#### Non-blocking Memory Write

The response is returned before memory is persisted:

```js
// Fire-and-forget — client gets answer immediately
Promise.all([
  saveToMemory(user_id, 'user',      question,           lesson_id),
  saveToMemory(user_id, 'assistant', grokResult.content, lesson_id),
]).catch(err => console.error('Memory save failed:', err));

return res.json({ success: true, answer: grokResult.content, ... });
```

#### Request Timeout Configuration

```js
// axios call to Grok
timeout: 30000   // 30s — Grok is typically 1–5s for grok-3-mini

// OpenAI embedding
timeout: 15000   // 15s — embeddings are fast

// Supabase RPC
timeout: 10000   // 10s — vector search with index is fast
```

---

### 4. Database Optimization

#### pgvector IVFFlat Index

```sql
-- Run once, after initial data load
create index on lesson_chunks
  using ivfflat (embedding vector_cosine_ops)
  with (lists = 100);

-- Analyze after bulk inserts
analyze lesson_chunks;
```

Without index: full sequential scan → O(n) per query  
With IVFFlat: approximate nearest-neighbour → O(√n) per query

#### Denormalized `roadmap_id`

`lesson_chunks` stores `roadmap_id` directly, avoiding a JOIN on every vector search:
```sql
where lc.roadmap_id = p_roadmap_id  -- uses btree index on roadmap_id
  and 1 - (lc.embedding <=> query_embedding) > match_threshold
```

Add a supporting btree index:
```sql
create index on lesson_chunks (roadmap_id);
```

---

## Performance Targets

| Metric | Target | Notes |
|--------|--------|-------|
| Total API response time | < 4 seconds | p95 with cache warm |
| Embedding (cache miss) | < 500ms | OpenAI |
| Embedding (cache hit) | < 5ms | Redis |
| Supabase RAG query | < 200ms | With IVFFlat index |
| Grok API (grok-3-mini) | < 2s | Typical for 1500 token response |
| Memory write (non-blocking) | N/A | Does not affect response time |
