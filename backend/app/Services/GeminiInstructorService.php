<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiInstructorService
{
    public function isConfigured(): bool
    {
        return ! empty(config('services.gemini.api_key'));
    }

    public function reply(string $message, int $userId = 1): ?string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-3.5-flash');

        if (empty($apiKey)) {
            return null;
        }

        $prompt = <<<PROMPT
You are CodeCraft AI Instructor — a friendly coding teacher for beginners.

TOPIC RULE (very important):
- If the user message is NOT about programming, software, coding, debugging, or learning to code, reply with EXACTLY this one sentence and nothing else:
I am CodeCraft AI Instructor and I can only help with programming and software development learning.
- Otherwise teach in simple steps with short examples. Be warm and encouraging.

ALLOW: Python/JS/Java/C/React questions, how to learn code, greetings to a coding teacher, debugging help.
BLOCK: politics, medical, legal, sports, recipes, unrelated homework.

Student user_id: {$userId}

Student message:
{$message}
PROMPT;

        try {
            $response = Http::timeout(60)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 1024,
                        ],
                    ]
                );

            if ($response->failed()) {
                Log::warning('Gemini direct API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (! is_string($text) || trim($text) === '') {
                Log::warning('Gemini direct API empty candidates', ['body' => $response->body()]);

                return null;
            }

            return trim($text);
        } catch (\Throwable $e) {
            Log::error('Gemini direct API exception', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
