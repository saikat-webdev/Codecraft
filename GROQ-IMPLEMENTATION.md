# GROQ Implementation

## What I changed

1. Replaced the existing n8n workflow in `n8n/codecraft-ai-teacher.workflow.json` with a new GROQ-based flow.
2. Updated the n8n setup guide text inside the workflow to use Header Auth with `Authorization` and a Bearer GROQ API key.
3. Changed the workflow `Call Gemini API` node into `Call Groq API`.
4. Updated the request URL to `https://api.groq.com/openai/v1/responses`.
5. Updated the request body to send:
   - `model: 'openai/gpt-oss-20b'`
   - `input: <prompt>`
   - `temperature: 0.7`
   - `max_output_tokens: 1024`
6. Updated the response parser to read `output_text` from GROQ results and return the reply.
7. Updated `backend/app/Services/N8nAiInstructorService.php` to remove Gemini-specific failure wording and handle generic provider failure messages instead.

## Files changed

- `n8n/codecraft-ai-teacher.workflow.json`
- `backend/app/Services/N8nAiInstructorService.php`
- `GROQ-IMPLEMENTATION.md`

## Notes

- The new n8n workflow now uses GROQ API endpoint `https://api.groq.com/openai/v1/responses`.
- The n8n HTTP Header Auth credential should be configured with header name `Authorization` and value `Bearer <GROQ_API_KEY>`.
- I did not change the Laravel direct Gemini fallback code or environment variable names outside the n8n workflow, since the task focused on replacing the n8n flow with GROQ.
- If you want, I can also update the project docs to replace remaining Gemini-specific instructions with GROQ.
