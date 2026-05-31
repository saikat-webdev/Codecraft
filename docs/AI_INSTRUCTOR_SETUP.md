# CodeCraft AI Instructor — Setup Guide

## Overview

| Layer | Role |
|-------|------|
| **React** (`/ai`) | Chat UI → `POST /api/ai/chat` |
| **Laravel** | Validates input, forwards to n8n, returns `{ success, reply }` |
| **n8n** | Classifies topic → Gemini teacher or block message |

---

## 1. n8n workflow

1. Open your n8n instance (cloud or self-hosted).
2. **Workflows → Import from file** → choose `n8n/codecraft-ai-teacher.workflow.json`.
3. Create **Google Gemini API** credentials in n8n (Google AI Studio API key).
4. Open both **Gemini Classifier** and **Gemini AI Teacher** nodes → assign your credential (replace `REPLACE_WITH_YOUR_CREDENTIAL_ID` if import did not map it).
5. Open the **Webhook** node and copy the **Production URL** (not Test URL), e.g.  
   `https://your-n8n.app/webhook/ai-teacher`  
   ⚠️ Do **not** put `/webhook-test/` in `backend/.env`. The test URL is only for manual tests in the n8n UI while **Listen for test event** is on. Laravel calls n8n in the background — it needs the **Production** URL.
6. On both **Gemini Classifier** and **Gemini AI Teacher** HTTP nodes, assign **Google Gemini(PaLM) API** credentials (same API key as [Google AI Studio](https://aistudio.google.com/apikey)).
7. **Activate** the workflow (toggle ON). Until it is active, production webhooks return **404**.

### Why the app errors but n8n shows the webhook data

Seeing the POST in the Webhook node panel only means step 1 worked. The app needs the **last** node (**Respond to Webhook**) to return JSON `{"success":true,"reply":"..."}`. If Gemini nodes fail (missing API key, wrong node type), the HTTP response to Laravel is empty or an error — and the chat UI shows a failure message.

Check **Executions** in n8n (left sidebar) for red failed runs.

### All messages show the block reply

The **IF** node sends traffic to **Block Response** when the classifier returns `blocked` (or when `classification` is empty and the old workflow required the word `allowed`). Open **Parse Classifier** in a successful execution and check the `classification` field. Re-import `n8n/codecraft-ai-teacher.workflow.json` for the fixed `should_allow` logic (block only when the model explicitly says `blocked`).

Test with curl:

```bash
curl -X POST "https://YOUR-N8N/webhook/ai-teacher" \
  -H "Content-Type: application/json" \
  -d "{\"message\":\"What is a for loop in Python?\",\"user_id\":1}"
```

Expected:

```json
{"success":true,"reply":"..."}
```

---

## 2. Laravel backend

Add to `backend/.env`:

```env
N8N_WEBHOOK_URL=https://your-n8n.app/webhook/ai-teacher
N8N_WEBHOOK_TIMEOUT=90
GEMINI_API_KEY=your_google_ai_studio_key
GEMINI_MODEL=gemini-3.5-flash
```

### Model selection in n8n (dropdown)

The workflow uses the **Google Gemini** node (`Gemini AI Teacher`):

1. Open the **Gemini AI Teacher** node.
2. Assign your **Google Gemini (PaLM) API** credential.
3. Use the **Model** dropdown → pick e.g. `gemini-3.5-flash` (loaded from your Google account).
4. Save and **Activate** the workflow.

Re-import `n8n/codecraft-ai-teacher.workflow.json` if you still see an old **HTTP Request** node without a model dropdown.

**Laravel fallback** (when n8n fails) uses `GEMINI_MODEL` in `backend/.env` — separate from the n8n dropdown.

Official model list: https://ai.google.dev/gemini-api/docs/models

### Quota errors

If you see `Quota exceeded` for `gemini-2.0-flash`, your key hit the **free tier limit for that model**. Use `gemini-3.5-flash` (updated in this project) or check usage: https://ai.dev/rate-limit

Clear config cache:

```bash
cd backend
php artisan config:clear
```

Ensure migrations are up to date (optional chat logging uses `ai_conversations`):

```bash
php artisan migrate
```

---

## 3. Frontend

No extra env vars required if `VITE_API_URL` already points at your API (default `http://localhost:8000/api`).

```bash
cd frontend
npm run dev
```

Open **http://localhost:5173/ai**

---

## 4. Files touched

### Created

- `backend/app/Http/Controllers/Api/AIController.php`
- `backend/app/Http/Requests/AIChatRequest.php`
- `backend/app/Models/AiConversation.php`
- `frontend/src/pages/AIChat.jsx`
- `frontend/src/services/ai.js`
- `n8n/codecraft-ai-teacher.workflow.json`
- `docs/AI_INSTRUCTOR_SETUP.md`

### Modified

- `backend/routes/api.php` — `POST ai/chat`
- `backend/config/services.php` — `n8n` config
- `backend/env.docker.example` — `N8N_WEBHOOK_URL`
- `frontend/src/App.jsx` — route + nav
- `frontend/src/components/MobileNav.jsx` — nav link
- `frontend/src/index.css` — chat styles

---

## 5. API contract

**Request** `POST /api/ai/chat`

```json
{ "message": "How do variables work in JavaScript?" }
```

Headers: `Authorization: Bearer <token>` optional (logged-in user id sent to n8n; guests use `user_id: 1`).

**Success** `200`

```json
{ "success": true, "reply": "..." }
```

**Error** `502` / `503`

```json
{ "success": false, "message": "..." }
```

---

## 6. Troubleshooting

| Issue | Fix |
|-------|-----|
| 503 AI not configured | Set `N8N_WEBHOOK_URL` in `.env` |
| 502 timeout | Increase `N8N_WEBHOOK_TIMEOUT`; check n8n is active |
| Empty reply | Confirm n8n **Respond to Webhook** returns `reply` field |
| CORS errors | Set `FRONTEND_URL=http://localhost:5173` in backend `.env` |
| Gemini node missing after import | Install/update n8n; use LangChain Gemini nodes or swap to HTTP Request → Gemini REST API |

---

## 7. Future improvements

- **Chat history** — API to load `ai_conversations` per user; hydrate UI on mount
- **Streaming** — SSE from n8n or Laravel streamed response for token-by-token UX
- **Personalization** — pass track/level from user profile into n8n prompt
- **Rate limiting** — throttle `ai/chat` per user/IP
- **Lesson context** — optional `lesson_id` in payload for scoped explanations
