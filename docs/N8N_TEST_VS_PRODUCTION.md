# n8n: Test URL vs Production URL

## Why test works but production fails

| | **Test URL** (`/webhook-test/…`) | **Production URL** (`/webhook/…`) |
|---|----------------------------------|-----------------------------------|
| Used by | n8n editor (“Listen for test event”, manual runs) | Laravel, curl, your live app |
| Runs which version | **Draft** in the editor (what you see now) | **Published** snapshot |
| Needs editor open | Often yes (listen mode) | No |
| Credentials / model | Whatever you just set in the UI | Whatever was saved when you last **Published** |

So: test can succeed while production still uses an **old Published copy** (missing Gemini credential, wrong model like `gemini-3.5-flash`, or broken Extract Payload).

The message *“Sorry, the AI could not generate a reply…”* is **not from Laravel**. It is built in the **Format Response** node when the **Gemini AI Teacher** node returns no `text` / `output` (usually API or credential failure).

---

## Fix (do in order)

### 1. Check a failed **production** execution

1. n8n → **Executions**
2. Open a run triggered by **`/webhook/ai-teacher`** (not `webhook-test`)
3. Open **Gemini AI Teacher** — red error = root cause (quota, invalid model, no credential)

### 2. Fix Gemini node (in the editor)

1. **Gemini AI Teacher** → **Credential**: Google Gemini (PaLM) API with a valid [AI Studio](https://aistudio.google.com/apikey) key
2. **Model**: pick one that exists in the dropdown, e.g. **`gemini-2.0-flash`** (avoid models that 404 in your account)
3. **Save** workflow

### 3. Re-publish (critical)

1. Top right: **Published** → **OFF**
2. **Published** → **ON** again  

This pushes the editor version (credentials + model) to production.

### 4. Test production **without** “Listen for test event”

```powershell
curl -X POST "https://saikatn8n.app.n8n.cloud/webhook/ai-teacher" `
  -H "Content-Type: application/json" `
  -d "{\"message\":\"What is a variable in Python?\",\"user_id\":1}"
```

Expect: `{"success":true,"reply":"..."}` with a real answer (not the “Sorry…” fallback).

### 5. Laravel `.env`

```env
N8N_WEBHOOK_URL=https://saikatn8n.app.n8n.cloud/webhook/ai-teacher
```

**Not** `/webhook-test/`. Then:

```powershell
php artisan config:clear
```

---

## Until production is fixed

Keep `GEMINI_API_KEY` in `backend/.env`. Laravel uses **direct Gemini** when n8n returns empty or invalid JSON.

---

## Re-import workflow

If the graph is wrong, re-import `n8n/codecraft-ai-teacher.workflow.json`, re-assign credentials, pick model, then **Published OFF → ON**.
