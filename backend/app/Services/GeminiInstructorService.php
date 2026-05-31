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

    public function reply(string $message, int $userId = 1, ?string $knowledgeContext = null): ?string
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            return null;
        }

        $knowledgeContext = $knowledgeContext ?? $this->knowledge->buildContextForMessage($message);
        $knowledgeBlock = $knowledgeContext !== ''
            ? "\n\n---\n{$knowledgeContext}\n---\n"
            : '';

        $prompt = <<<PROMPT
You are CodeCraft AI Instructor — a friendly coding teacher for beginners.

TOPIC RULE (very important):
- If the user message is NOT about programming, software, coding, debugging, or learning to code, reply with EXACTLY this one sentence and nothing else:
I am CodeCraft AI Instructor and I can only help with programming and software development learning.
- Otherwise teach in simple steps with short examples. Be warm and encouraging.

ALLOW: Python/JS/Java/C/React questions, how to learn code, greetings to a coding teacher, debugging help, and questions about CodeCraft modules/lessons/tracks when context is provided below.
BLOCK: politics, medical, legal, sports, recipes, unrelated homework.
{$knowledgeBlock}
Student user_id: {$userId}

Student message:
{$message}
PROMPT;

        foreach ($this->modelsToTry() as $model) {
            $text = $this->callGenerateContent($apiKey, $model, $prompt);
            if ($text !== null) {
                return $text;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function modelsToTry(): array
    {
        $configured = trim((string) config('services.gemini.model', 'gemini-2.0-flash'));
        $fallbacks = array_map('trim', explode(',', (string) config('services.gemini.model_fallbacks', 'gemini-2.0-flash,gemini-1.5-flash')));

        return array_values(array_unique(array_filter([$configured, ...$fallbacks])));
    }

    protected function callGenerateContent(string $apiKey, string $model, string $prompt): ?string
    {
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
                Log::warning('Gemini generateContent failed', [
                    'model' => $model,
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 500),
                ]);

                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (! is_string($text) || trim($text) === '') {
                Log::warning('Gemini generateContent empty reply', [
                    'model' => $model,
                    'body' => mb_substr($response->body(), 0, 500),
                ]);

                return null;
            }

            return trim($text);
        } catch (\Throwable $e) {
            Log::error('Gemini generateContent exception', [
                'model' => $model,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function __construct(
        protected KnowledgeRetrievalService $knowledge,
    ) {}
}
