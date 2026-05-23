<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Proxies code execution to the public Judge0 CE API through Laravel.
 * Requests never expose a third-party key to the browser; validation and
 * timeouts are enforced server-side before calling Judge0.
 */
class Judge0Service
{
    protected string $baseUrl;

    protected ?string $apiKey;

    protected int $timeout;

    public function __construct(
        protected LocalCodeRunnerService $localRunner,
    ) {
        // Public CE host (no API key). Override via JUDGE0_BASE_URL if needed.
        $this->baseUrl = rtrim(config('services.judge0.base_url', 'https://ce.judge0.com'), '/');
        $this->apiKey = config('services.judge0.api_key');
        $this->timeout = (int) config('services.judge0.timeout', 30);
    }

    public function runCode(string $code, string $language = 'python', int $cpuTimeLimit = 5): array
    {
        if (!config('services.judge0.enabled', true)) {
            return $this->runLocally($code, $language, $cpuTimeLimit);
        }

        $languageId = $this->getLanguageId($language);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->post($this->baseUrl . '/submissions?base64_encoded=false&wait=false', [
                    'source_code' => $code,
                    'language_id' => $languageId,
                    'cpu_time_limit' => $cpuTimeLimit,
                    'memory_limit' => 256000,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Judge0 submission failed', ['message' => $e->getMessage()]);

            return $this->runLocally($code, $language, $cpuTimeLimit)
                ?? [
                    'success' => false,
                    'error' => 'Could not reach the code execution service. Please try again.',
                ];
        }

        if ($response->failed()) {
            return $this->runLocally($code, $language, $cpuTimeLimit)
                ?? [
                    'success' => false,
                    'error' => 'Failed to submit code for execution.',
                ];
        }

        $token = $response->json('token');

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
        $maxAttempts = 20;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . "/submissions/{$token}?base64_encoded=false");
            } catch (\Throwable $e) {
                Log::warning('Judge0 poll failed', ['message' => $e->getMessage()]);

                return [
                    'success' => false,
                    'error' => 'Failed to retrieve execution result.',
                ];
            }

            if ($response->failed()) {
                return [
                    'success' => false,
                    'error' => 'Failed to get execution result.',
                ];
            }

            $result = $response->json();
            $statusId = (int) ($result['status']['id'] ?? 0);

            // 1 = In Queue, 2 = Processing — keep polling
            if ($statusId > 2) {
                return $this->formatResult($result);
            }

            usleep(500000);
            $attempts++;
        }

        return [
            'success' => false,
            'error' => 'Code execution timed out. Try simpler code or a shorter runtime.',
        ];
    }

    protected function formatResult(array $result): array
    {
        $statusId = (int) ($result['status']['id'] ?? 0);
        $statusLabel = $result['status']['description'] ?? 'Unknown';

        $stdout = trim((string) ($result['stdout'] ?? ''));
        $stderr = trim((string) ($result['stderr'] ?? ''));
        $compileOutput = trim((string) ($result['compile_output'] ?? ''));
        $message = trim((string) ($result['message'] ?? ''));

        if ($statusId === 3) {
            return [
                'success' => true,
                'output' => $stdout !== '' ? $stdout : 'Program finished with no output.',
                'error' => null,
                'status' => 'success',
                'status_label' => $statusLabel,
            ];
        }

        $errorParts = array_filter([$compileOutput, $stderr, $message, $statusLabel !== 'Unknown' ? $statusLabel : null]);

        return [
            'success' => false,
            'output' => $stdout,
            'error' => $errorParts !== [] ? implode("\n", $errorParts) : 'Execution failed.',
            'status' => 'error',
            'status_label' => $statusLabel,
        ];
    }

    protected function getLanguageId(string $language): int
    {
        $languages = [
            'python' => 71,
            'python3' => 71,
            'javascript' => 63,
            'js' => 63,
            'cpp' => 54,
            'c++' => 54,
            'c' => 50,
            'java' => 62,
            'react' => 63,
        ];

        $normalized = strtolower($language);
        if ($normalized === 'react') {
            $normalized = 'javascript';
        }

        return $languages[$normalized] ?? 71;
    }

    protected function getHeaders(): array
    {
        $headers = ['Content-Type' => 'application/json'];

        // Optional RapidAPI key — not required for public CE
        if ($this->apiKey) {
            $headers['X-RapidAPI-Key'] = $this->apiKey;
            $headers['X-RapidAPI-Host'] = 'judge0-ce.p.rapidapi.com';
        }

        return $headers;
    }

    protected function runLocally(string $code, string $language, int $cpuTimeLimit): ?array
    {
        if (!config('services.code_runner.fallback_enabled', true)) {
            return null;
        }

        if (!$this->localRunner->supports($language)) {
            return null;
        }

        $result = $this->localRunner->run($code, $language, $cpuTimeLimit);

        if (!$result['success'] && str_contains($result['error'] ?? '', 'not installed')) {
            return null;
        }

        return $result;
    }
}
