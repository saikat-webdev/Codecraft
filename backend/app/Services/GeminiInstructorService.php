<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class GeminiInstructorService
{
    public function generateReply(User $user, string $message, ?Lesson $lesson = null): string
    {
        $config = config('services.gemini', []);
        $provider = strtolower($config['provider'] ?? env('GEMINI_API_PROVIDER', 'google'));
        $model = $config['model'] ?? env('GEMINI_MODEL', 'gemini-pro');
        $endpoint = $config['endpoint'] ?? null;
        $apiKey = $config['key'] ?? null;

        if ($provider === 'openai') {
            $apiKey = $apiKey ?: env('OPENAI_API_KEY');
        }

        if (empty($apiKey)) {
            return 'AI instructor integration is not configured. Please set GEMINI_API_KEY for Google or OPENAI_API_KEY for OpenAI.';
        }

        if (empty($endpoint)) {
            if ($provider === 'google') {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta2/models/{$model}:generateMessage";
            } else {
                $endpoint = 'https://api.openai.com/v1/responses';
            }
        }

        $systemPrompt = $this->buildSystemPrompt($lesson);
        $payload = $this->buildPayload($provider, $endpoint, $apiKey, $model, $systemPrompt, $message);

        try {
            $response = Http::timeout(30)->withHeaders($payload['headers'])->post($payload['endpoint'], $payload['body']);

            if ($response->failed()) {
                $apiMessage = $response->json('error.message') ?: $response->json('error.errors.0.message');
                $status = $response->status();

                if ($provider === 'google') {
                    return sprintf(
                        'Google AI key error (%s): %s. Verify GEMINI_API_KEY, GEMINI_MODEL, and that the Generative AI API is enabled for your project.',
                        $status,
                        $apiMessage ?? 'Unexpected response from the provider.'
                    );
                }

                return $apiMessage ?? 'The AI instructor service returned an unexpected error.';
            }

            return $this->parseResponse($response->json());
        } catch (\Throwable $exception) {
            return 'The AI instructor is unavailable right now. Please try again in a moment.';
        }
    }

    protected function buildSystemPrompt(?Lesson $lesson): string
    {
        $base = [
            'You are an expert beginner-friendly Python instructor.',
            'Teach slowly and clearly.',
            'Always encourage the student.',
            'Explain concepts with simple examples.',
            'Break concepts into tiny steps.',
            'Ask interactive questions.',
            'Never overwhelm beginners.',
            'If the student is confused, help them debug beginner mistakes gently.',
            'Do not provide huge code dumps. Keep replies short, friendly, and practical.',
        ];

        if ($lesson) {
            $base[] = 'The current lesson is: ' . $lesson->title . '.';
            $base[] = 'Use the lesson description and beginner context to provide lesson-specific guidance.';
        }

        return implode(' ', $base);
    }

    protected function buildPayload(string $provider, string $endpoint, string $apiKey, string $model, string $systemPrompt, string $message): array
    {
        if ($provider === 'google') {
            return [
                'endpoint' => $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . 'key=' . urlencode($apiKey),
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'prompt' => [
                        'messages' => [
                            ['author' => 'system', 'content' => [['type' => 'text', 'text' => $systemPrompt]]],
                            ['author' => 'user', 'content' => [['type' => 'text', 'text' => $message]]],
                        ],
                    ],
                    'temperature' => 0.65,
                    'maxOutputTokens' => 512,
                ],
            ];
        }

        return [
            'endpoint' => $endpoint,
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'body' => [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.65,
                'max_tokens' => 512,
            ],
        ];
    }

    protected function parseResponse(array $response): string
    {
        if (!empty($response['output_text'])) {
            return trim($response['output_text']);
        }

        if (!empty($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }

        if (!empty($response['choices'][0]['text'])) {
            return trim($response['choices'][0]['text']);
        }

        if (!empty($response['candidates'][0]['content'][0]['text'])) {
            return trim($response['candidates'][0]['content'][0]['text']);
        }

        if (!empty($response['message'])) {
            return trim($response['message']);
        }

        if (!empty($response['text'])) {
            return trim($response['text']);
        }

        return trim(json_encode($response));
    }
}
