# n8n: Test URL vs Production URL

## Why only the test webhook “works”

| URL type | Example | When it works |
|----------|---------|----------------|
| **Test** | `…/webhook-test/ai-teacher` | Only while the workflow is open in n8n **and** you clicked **Listen for test event** |
| **Production** | `…/webhook/ai-teacher` | While the workflow toggle is **Active** (ON) |

The CodeCraft app (Laravel) calls n8n from the server. It is **not** inside your n8n editor session, so **`/webhook-test/` will not work** for the website.

Seeing the POST in the Webhook node panel = test mode worked in the UI. The chat app needs the **production** URL.

## Fix production webhook (empty or no reply)

1. In n8n, open **CodeCraft AI Teacher** (simplified 4-node workflow from `n8n/codecraft-ai-teacher.workflow.json`).
2. **Gemini AI Teacher** node → set **Google Gemini (PaLM) API** credential.
3. Toggle workflow **Active** (top right).
4. Webhook node → copy **Production URL** (`/webhook/ai-teacher`).
5. In `backend/.env`:

```env
N8N_WEBHOOK_URL=https://saikatn8n.app.n8n.cloud/webhook/ai-teacher
```

6. Test **without** opening Listen in n8n:

```powershell
curl -X POST "https://saikatn8n.app.n8n.cloud/webhook/ai-teacher" `
  -H "Content-Type: application/json" `
  -d "{\"message\":\"How do I learn Python?\",\"user_id\":1}"
```

You must get JSON with a `reply` field. If the body is empty, check **Executions** for errors.

7. `php artisan config:clear` and restart `php artisan serve`.

## Until production works

Keep `GEMINI_API_KEY` in `.env`. Laravel will skip broken/empty n8n and call Gemini directly.
