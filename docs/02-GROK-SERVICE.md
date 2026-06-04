# Module 02 — Grok API Service

## Purpose

Wraps all communication with the xAI Grok API. Handles request formatting, authentication, retry logic with exponential backoff, and response parsing.

> **Rule:** This service is the **only** LLM used in the system. Never substitute Grok with any other provider.

---

## File Location

```
src/services/grokService.js          (Node.js)
app/Services/GrokService.php         (Laravel)
```

---

## API Reference

| Property | Value |
|----------|-------|
| Base URL | `https://api.x.ai/v1` |
| Endpoint | `POST /chat/completions` |
| Auth header | `Authorization: Bearer {GROK_API_KEY}` |
| Default model | `grok-3-mini` |
| Timeout | 30 seconds |

### Supported Models

| Model | Use case |
|-------|----------|
| `grok-3-mini` | Default — fast, cheap, sufficient for teaching responses |
| `grok-3` | Complex explanations, long-form content generation |

---

## Request Format

```json
{
  "model": "grok-3-mini",
  "messages": [
    { "role": "system", "content": "You are an expert teacher..." },
    { "role": "user",   "content": "What is a closure in JavaScript?" }
  ],
  "temperature": 0.7,
  "max_tokens": 1500,
  "stream": false
}
```

### Parameters

| Parameter | Type | Default | Notes |
|-----------|------|---------|-------|
| `model` | string | `grok-3-mini` | See models table above |
| `temperature` | float | `0.7` | Lower = more deterministic, higher = more creative |
| `max_tokens` | int | `1500` | Controls response length; increase for test generation |
| `stream` | bool | `false` | Streaming not used in this implementation |

---

## Response Parsing

```json
{
  "id": "chatcmpl-...",
  "object": "chat.completion",
  "model": "grok-3-mini",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "A closure is..."
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 420,
    "completion_tokens": 310,
    "total_tokens": 730
  }
}
```

The service returns:

```js
{
  content: string,       // the actual answer text
  finish_reason: string, // "stop" | "length" | "content_filter"
  usage: object,         // token counts for monitoring
  model: string          // model that was used
}
```

---

## Retry Logic

```
Attempt 1 → fail (5xx or 429) → wait 500ms
Attempt 2 → fail              → wait 1000ms
Attempt 3 → fail              → throw Error
```

- **Retried:** HTTP 429 (rate limit), 5xx server errors, network timeouts
- **Not retried:** 4xx client errors (bad API key, malformed request)
- **Backoff formula:** `retryDelay * 2^(attempt - 1)` (exponential)

---

## Node.js Implementation

```js
// src/services/grokService.js
const axios = require('axios');

const GROK_BASE_URL = 'https://api.x.ai/v1';

async function callGrok(messages, options = {}) {
  const {
    model      = 'grok-3-mini',
    temperature = 0.7,
    max_tokens  = 1500,
    retries     = 3,
    retryDelay  = 500,
  } = options;

  let lastError;

  for (let attempt = 1; attempt <= retries; attempt++) {
    try {
      const response = await axios.post(
        `${GROK_BASE_URL}/chat/completions`,
        { model, messages, temperature, max_tokens, stream: false },
        {
          headers: {
            'Authorization': `Bearer ${process.env.GROK_API_KEY}`,
            'Content-Type': 'application/json',
          },
          timeout: 30000,
        }
      );

      const choice = response.data.choices[0];
      return {
        content:       choice.message.content,
        finish_reason: choice.finish_reason,
        usage:         response.data.usage,
        model:         response.data.model,
      };
    } catch (error) {
      lastError = error;
      const status = error.response?.status;
      if (status && status !== 429 && status < 500) throw error;
      if (attempt < retries) {
        await new Promise(r => setTimeout(r, retryDelay * Math.pow(2, attempt - 1)));
      }
    }
  }

  throw new Error(`Grok API failed after ${retries} attempts: ${lastError.message}`);
}

module.exports = { callGrok };
```

---

## Laravel Implementation

```php
// app/Services/GrokService.php — key method
public function chat(array $messages, array $options = []): array
{
    $payload = array_merge([
        'model'       => 'grok-3-mini',
        'messages'    => $messages,
        'temperature' => 0.7,
        'max_tokens'  => 1500,
    ], $options);

    $attempt = 0;
    while ($attempt < $this->maxRetries) {
        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if ($response->successful()) {
            return [
                'content'       => $response->json('choices.0.message.content'),
                'finish_reason' => $response->json('choices.0.finish_reason'),
                'usage'         => $response->json('usage'),
                'model'         => $response->json('model'),
            ];
        }

        if ($response->status() < 500 && $response->status() !== 429) {
            throw new Exception("Grok error {$response->status()}");
        }

        $attempt++;
        sleep((int) pow(2, $attempt - 1));
    }

    throw new Exception('Grok API failed after retries');
}
```

---

## Usage Example

```js
const { callGrok } = require('./services/grokService');

const result = await callGrok([
  { role: 'system', content: 'You are a teacher.' },
  { role: 'user',   content: 'Explain recursion.' },
], {
  model:       'grok-3-mini',
  temperature: 0.6,
  max_tokens:  800,
});

console.log(result.content);
console.log(result.usage); // { prompt_tokens, completion_tokens, total_tokens }
```

---

## Error Scenarios

| Scenario | Behaviour |
|----------|-----------|
| Invalid API key | Throws immediately (401, not retried) |
| Rate limited (429) | Retried with backoff |
| Grok server error (500) | Retried with backoff |
| Timeout (30s) | Counted as a retry attempt |
| All retries exhausted | Throws `Error` — caught by route handler and returns 500 |
