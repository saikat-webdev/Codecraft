<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Judge0Service
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.judge0.base_url', 'https://judge0-ce.p.rapidapi.com');
        $this->apiKey = config('services.judge0.api_key');
        $this->timeout = config('services.judge0.timeout', 30);
    }

    public function runCode(string $code, string $language = 'python', int $timeout = 5): array
    {
        $languageId = $this->getLanguageId($language);

        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->post($this->baseUrl . '/submissions', [
                'source_code' => $code,
                'language_id' => $languageId,
                'cpu_time_limit' => $timeout,
                'memory_limit' => 256000,
            ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => 'Failed to execute code. Please try again.',
            ];
        }

        $submission = $response->json();
        $token = $submission['token'] ?? null;

        if (!$token) {
            return [
                'success' => false,
                'error' => 'Invalid response from code execution service.',
            ];
        }

        return $this->pollForResult($token);
    }

    protected function pollForResult(string $token): array
    {
        $maxAttempts = 10;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($this->baseUrl . "/submissions/{$token}");

            if ($response->failed()) {
                return [
                    'success' => false,
                    'error' => 'Failed to get execution result.',
                ];
            }

            $result = $response->json();
            $statusId = $result['status']['id'] ?? 0;

            if ($statusId > 2) {
                return $this->formatResult($result);
            }

            sleep(1);
            $attempts++;
        }

        return [
            'success' => false,
            'error' => 'Code execution timed out. Please try simpler code.',
        ];
    }

    protected function formatResult(array $result): array
    {
        $statusId = $result['status']['id'] ?? 0;
        $status = $result['status']['description'] ?? 'Unknown';

        $output = trim(($result['stdout'] ?? '') . ($result['compile_output'] ?? ''));
        $error = trim(($result['stderr'] ?? '') . ($result['message'] ?? ''));

        if ($statusId === 3) {
            return [
                'success' => true,
                'output' => $output ?: 'Code ran successfully with no output.',
                'error' => null,
                'status' => 'success',
            ];
        }

        return [
            'success' => false,
            'output' => $output,
            'error' => $error ?: "Execution failed: {$status}",
            'status' => 'error',
        ];
    }

    protected function getLanguageId(string $language): int
    {
        $languages = [
            'python' => 71,
            'python3' => 71,
            'javascript' => 63,
            'java' => 62,
            'cpp' => 54,
            'c' => 50,
        ];

        return $languages[strtolower($language)] ?? 71;
    }

    protected function getHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['X-RapidAPI-Key'] = $this->apiKey;
            $headers['X-RapidAPI-Host'] = 'judge0-ce.p.rapidapi.com';
        }

        return $headers;
    }
}