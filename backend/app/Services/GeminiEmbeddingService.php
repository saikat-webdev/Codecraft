<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiEmbeddingService
{
    public const TASK_RETRIEVAL_DOCUMENT = 'RETRIEVAL_DOCUMENT';

    public const TASK_RETRIEVAL_QUERY = 'RETRIEVAL_QUERY';

    public function isConfigured(): bool
    {
        return ! empty(config('services.gemini.api_key'));
    }

    /**
     * @return array<int, float>|null
     */
    public function embed(string $text, ?string $taskType = null): ?array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.embedding_model', 'gemini-embedding-001');
        $dimensions = (int) config('services.gemini.embedding_dimensions', 768);

        if (empty($apiKey) || trim($text) === '') {
            return null;
        }

        $payload = [
            'model' => "models/{$model}",
            'content' => [
                'parts' => [
                    ['text' => mb_substr($text, 0, 8000)],
                ],
            ],
        ];

        if ($dimensions > 0 && $dimensions < 3072) {
            $payload['outputDimensionality'] = $dimensions;
        }

        if ($taskType !== null && ! str_starts_with($model, 'gemini-embedding-2')) {
            $payload['taskType'] = $taskType;
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent",
                    $payload
                );

            if ($response->failed()) {
                Log::warning('Gemini embedContent failed', [
                    'status' => $response->status(),
                    'model' => $model,
                    'body' => $response->body(),
                ]);

                return null;
            }

            $values = $response->json('embedding.values')
                ?? $response->json('embeddings.0.values');

            if (! is_array($values) || $values === []) {
                return null;
            }

            $values = array_map('floatval', $values);

            if ($dimensions > 0 && $dimensions < 3072 && str_starts_with($model, 'gemini-embedding-001')) {
                $values = $this->normalizeVector($values);
            }

            return $values;
        } catch (\Throwable $e) {
            Log::error('Gemini embedContent exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  array<int, float>  $vector
     * @return array<int, float>
     */
    protected function normalizeVector(array $vector): array
    {
        $sumSquares = 0.0;
        foreach ($vector as $value) {
            $sumSquares += $value * $value;
        }

        $norm = sqrt($sumSquares);
        if ($norm <= 0.0) {
            return $vector;
        }

        return array_map(fn (float $value): float => $value / $norm, $vector);
    }

    public function toPgVector(array $embedding): string
    {
        return '['.implode(',', $embedding).']';
    }
}
