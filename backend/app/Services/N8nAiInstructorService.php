<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nAiInstructorService
{
    public function isConfigured(): bool
    {
        $url = (string) config('services.n8n.webhook_url');

        if ($url === '' || str_contains($url, '/webhook-test/')) {
            return false;
        }

        return true;
    }

    public function chat(string $message, int $userId, ?string $knowledgeContext = null): ?array
    {
        $webhookUrl = config('services.n8n.webhook_url');

        if (empty($webhookUrl)) {
            return null;
        }

        $payload = [
            'message' => $message,
            'user_id' => $userId,
        ];

        if ($knowledgeContext !== null && trim($knowledgeContext) !== '') {
            $maxChars = (int) config('services.n8n.max_knowledge_context_chars', 10000);
            $payload['knowledge_context'] = mb_substr($knowledgeContext, 0, $maxChars);
        }

        try {
            $response = Http::timeout((int) config('services.n8n.timeout', 90))
                ->acceptJson()
                ->post($webhookUrl, $payload);

            return $this->parseResponse($response, $webhookUrl);
        } catch (\Throwable $e) {
            Log::warning('n8n AI webhook exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    protected function parseResponse(Response $response, string $webhookUrl): ?array
    {
        if ($response->failed()) {
            Log::warning('n8n AI webhook failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $webhookUrl,
            ]);

            return null;
        }

        if (trim($response->body()) === '') {
            Log::warning('n8n AI webhook returned empty body', ['url' => $webhookUrl]);

            return null;
        }

        $payload = $response->json();
        $reply = $this->extractReply($payload);

        if (! is_string($reply) || trim($reply) === '') {
            Log::warning('n8n AI webhook invalid payload', ['payload' => $payload]);

            return null;
        }

        if ($this->isN8nGeminiFailureMessage($reply)) {
            Log::warning('n8n Gemini node failed (workflow returned placeholder reply)', [
                'url' => $webhookUrl,
                'reply_preview' => mb_substr($reply, 0, 200),
            ]);

            return null;
        }

        return [
            'success' => true,
            'reply' => $reply,
        ];
    }

    protected function extractReply(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach ([
            $payload['reply'] ?? null,
            $payload['data']['reply'] ?? null,
            $payload['response']['reply'] ?? null,
        ] as $value) {
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function isN8nGeminiFailureMessage(string $reply): bool
    {
        return str_contains($reply, 'Sorry, the AI could not generate a reply')
            || str_starts_with($reply, 'AI error:')
            || str_starts_with($reply, 'Gemini API:')
            || str_starts_with($reply, 'Missing "message"');
    }
}
