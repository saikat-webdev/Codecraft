# Module 08 — Node.js API (Express)

## Purpose

Production-ready Express microservice that exposes the AI Teacher pipeline as a REST API. Fully modular — each concern lives in its own service file.

---

## Project Structure

```
ai-teacher/
├── src/
│   ├── services/
│   │   ├── grokService.js          LLM calls
│   │   ├── embeddingService.js     OpenAI vector generation
│   │   ├── ragService.js           Supabase queries
│   │   ├── contextBuilder.js       Prompt assembly
│   │   └── tokenOptimizer.js       Chunk trimming, memory filtering
│   ├── middleware/
│   │   ├── cache.js                Redis embedding/RAG/progress cache
│   │   └── rateLimit.js            express-rate-limit config
│   ├── routes/
│   │   └── teacher.js              Route handlers
│   └── app.js                      Express entry point
├── .env
└── package.json
```

---

## Dependencies

```json
{
  "dependencies": {
    "express":        "^4.19.0",
    "axios":          "^1.7.0",
    "openai":         "^4.52.0",
    "@supabase/supabase-js": "^2.44.0",
    "ioredis":        "^5.4.0",
    "helmet":         "^7.1.0",
    "express-rate-limit": "^7.3.0",
    "dotenv":         "^16.4.0"
  }
}
```

Install:
```bash
npm install
```

---

## Environment Variables

```ini
# .env
GROK_API_KEY=xai-xxxxxxxxxxxxxxxx
OPENAI_API_KEY=sk-xxxxxxxxxxxxxxxx
SUPABASE_URL=https://xxxx.supabase.co
SUPABASE_SERVICE_KEY=eyJ...
REDIS_URL=redis://localhost:6379
PORT=3000
NODE_ENV=production
```

---

## Entry Point: `src/app.js`

```js
require('dotenv').config();
const express    = require('express');
const helmet     = require('helmet');
const rateLimit  = require('express-rate-limit');
const teacherRouter = require('./routes/teacher');

const app = express();

app.use(helmet());
app.use(express.json({ limit: '10kb' }));

// 30 requests/minute per IP on all /api/teacher routes
app.use('/api/teacher', rateLimit({
  windowMs:       60 * 1000,
  max:            30,
  standardHeaders: true,
  legacyHeaders:   false,
}));

app.use('/api/teacher', teacherRouter);

// Global error handler
app.use((err, req, res, next) => {
  console.error(err);
  res.status(500).json({ error: 'Internal server error' });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`AI Teacher running on :${PORT}`));
```

---

## Routes: `src/routes/teacher.js`

### `POST /api/teacher/ask`

Main endpoint. Runs the full pipeline: embed → RAG → context → Grok → respond.

**Request:**
```json
{
  "question":  "What is a closure?",
  "user_id":   "550e8400-e29b-41d4-a716-446655440000",
  "lesson_id": "optional-uuid"
}
```

**Response:**
```json
{
  "success":     true,
  "answer":      "## Explanation\nA closure is...",
  "model":       "grok-3-mini",
  "usage":       { "prompt_tokens": 420, "completion_tokens": 310 },
  "rag_sources": 3
}
```

**Error response:**
```json
{
  "success": false,
  "error":   "Failed to generate response"
}
```

---

### `GET /api/teacher/next-lesson/:user_id`

Returns the next incomplete lesson for the user's active roadmap.

**Response:**
```json
{
  "next_lesson": {
    "id":          "uuid",
    "title":       "Arrow Functions",
    "order_index": 8
  }
}
```

---

## Full Route Handler

```js
// src/routes/teacher.js
const express         = require('express');
const router          = express.Router();
const { generateEmbedding }        = require('../services/embeddingService');
const { retrieveRelevantChunks,
        getUserProgress,
        getNextLesson,
        getConversationMemory,
        saveToMemory }             = require('../services/ragService');
const { buildSystemPrompt,
        buildMessages }            = require('../services/contextBuilder');
const { callGrok }                 = require('../services/grokService');

router.post('/ask', async (req, res) => {
  const { question, user_id, lesson_id } = req.body;

  if (!question?.trim() || !user_id) {
    return res.status(400).json({ error: 'question and user_id are required' });
  }

  try {
    // Parallel fetch: embedding + progress + memory
    const [embedding, progress, memory] = await Promise.all([
      generateEmbedding(question),
      getUserProgress(user_id),
      getConversationMemory(user_id, 5),
    ]);

    const activeRoadmapId = progress
      .find(p => p.status === 'in_progress')
      ?.lessons?.modules?.roadmap_id;

    const ragChunks = activeRoadmapId
      ? await retrieveRelevantChunks(embedding, activeRoadmapId, { threshold: 0.68, count: 5 })
      : [];

    const systemPrompt = buildSystemPrompt(progress, ragChunks);
    const messages     = buildMessages(systemPrompt, memory, question);

    const grokResult = await callGrok(messages, {
      model:       'grok-3-mini',
      temperature: 0.7,
      max_tokens:  1500,
    });

    // Non-blocking memory save
    Promise.all([
      saveToMemory(user_id, 'user',      question,             lesson_id),
      saveToMemory(user_id, 'assistant', grokResult.content,   lesson_id),
    ]).catch(err => console.error('Memory save error:', err));

    return res.json({
      success:     true,
      answer:      grokResult.content,
      model:       grokResult.model,
      usage:       grokResult.usage,
      rag_sources: ragChunks.length,
    });
  } catch (error) {
    console.error('AI Teacher error:', error);
    return res.status(500).json({
      success: false,
      error:   'Failed to generate response',
      details: process.env.NODE_ENV === 'development' ? error.message : undefined,
    });
  }
});

router.get('/next-lesson/:user_id', async (req, res) => {
  try {
    const { user_id } = req.params;
    const progress    = await getUserProgress(user_id);
    const active      = progress.find(p => p.status === 'in_progress');
    const roadmapId   = active?.lessons?.modules?.roadmap_id;

    if (!roadmapId) {
      return res.json({ next_lesson: null, message: 'No active roadmap' });
    }

    const next = await getNextLesson(user_id, roadmapId);
    return res.json({ next_lesson: next });
  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
});

module.exports = router;
```

---

## Running the Service

```bash
# Development (with auto-reload)
npm install -g nodemon
nodemon src/app.js

# Production
node src/app.js

# With PM2
pm2 start src/app.js --name ai-teacher
```

---

## Testing with cURL

```bash
# Ask a question
curl -X POST http://localhost:3000/api/teacher/ask \
  -H "Content-Type: application/json" \
  -d '{
    "question": "What is a closure in JavaScript?",
    "user_id": "550e8400-e29b-41d4-a716-446655440000"
  }'

# Get next lesson
curl http://localhost:3000/api/teacher/next-lesson/550e8400-e29b-41d4-a716-446655440000
```

---

## Security Notes

| Measure | Implementation |
|---------|---------------|
| Helmet | Sets secure HTTP headers |
| Rate limiting | 30 req/min per IP |
| Body size limit | `10kb` — prevents oversized payloads |
| `user_id` scoping | All DB queries filter by `user_id` |
| `SUPABASE_SERVICE_KEY` | Server-side only — never exposed to client |
| Error details | Only shown in `NODE_ENV=development` |
