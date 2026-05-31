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

        // Test URLs only work while "Listen for test event" is open in the n8n editor.
        // Laravel calls n8n in the background — production URL + active workflow is required.
        if ($url === '' || str_contains($url, '/webhook-test/')) {
            return false;
        }

        return true;
    }

    public function chat(string $message, int $userId): ?array
    {
        $webhookUrl = config('services.n8n.webhook_url');

        if (empty($webhookUrl)) {
            return null;
        }

        try {
            $response = Http::timeout((int) config('services.n8n.timeout', 90))
                ->acceptJson()
                ->post($webhookUrl, [
                    'message' => $message,
                    'user_id' => $userId,
                ]);

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
}
