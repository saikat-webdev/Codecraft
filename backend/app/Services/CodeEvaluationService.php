<?php

namespace App\Services;

use App\Models\CodingExercise;
use Illuminate\Support\Facades\Http;

class CodeEvaluationService
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;
    protected string $provider;

    public function __construct()
    {
        $config = config('services.gemini', []);
        $this->provider = strtolower($config['provider'] ?? 'google');
        $this->apiKey = $config['key'] ?? null;
        $this->model = $config['model'] ?? 'gemini-pro';
        $this->endpoint = $config['endpoint'] ?? null;
    }

    public function evaluateCode(string $code, CodingExercise $exercise, ?string $output = null, ?string $error = null): string
    {
        if (!$this->apiKey) {
            return $this->getLocalFeedback($code, $exercise, $output, $error);
        }

        $prompt = $this->buildEvaluationPrompt($code, $exercise, $output, $error);
        $response = $this->sendRequest($prompt);

        return $response ?: $this->getLocalFeedback($code, $exercise, $output, $error);
    }

    public function getHints(CodingExercise $exercise): array
    {
        if (!$this->apiKey) {
            return $this->getLocalHints($exercise);
        }

        $prompt = "Generate 3 progressive hints for this Python coding exercise.
Exercise: {$exercise->title}
Description: {$exercise->description}
Expected output: {$exercise->expected_output}

Provide hints in this format:
HINT 1: [First gentle hint - just point them in the right direction]
HINT 2: [More specific guidance with concepts]
HINT 3: [Detailed explanation with example]

Be encouraging and use emojis. Don't give away the full solution in hint 1 or 2.";

        $response = $this->sendRequest($prompt);

        if ($response) {
            return $this->parseHints($response);
        }

        return $this->getLocalHints($exercise);
    }

    protected function buildEvaluationPrompt(string $code, CodingExercise $exercise, ?string $output, ?string $error): string
    {
        $prompt = "You are a friendly Python instructor helping a beginner. Analyze their code and provide encouraging feedback.

Exercise: {$exercise->title}
Problem: {$exercise->description}
Expected output: {$exercise->expected_output}

Student's code:
```python
{$code}
```";

        if ($error) {
            $prompt .= "\nError output:\n{$error}";
        } elseif ($output) {
            $prompt .= "\nActual output:\n{$output}";
        }

        $prompt .= "\n\nProvide feedback that:
1. Starts with encouragement
2. Points out what they did well
3. Gently explains errors in simple terms
4. Gives a helpful hint to fix issues
5. Uses emojis and friendly tone

Keep it concise (2-3 short paragraphs max). NEVER be harsh or say 'wrong'. Always encourage them to keep trying!";

        return $prompt;
    }

    protected function sendRequest(string $prompt): ?string
    {
        if ($this->provider === 'google') {
            return $this->sendGoogleRequest($prompt);
        }

        return $this->sendOpenAIRequest($prompt);
    }

    protected function sendGoogleRequest(string $prompt): ?string
    {
        $endpoint = $this->endpoint ?? "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
        $url = $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . 'key=' . urlencode($this->apiKey);

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        'role' => 'user',
                        'parts' => [['text' => $prompt]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 512,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    protected function sendOpenAIRequest(string $prompt): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post($this->endpoint ?? 'https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a friendly Python instructor helping beginners.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 512,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    protected function parseHints(string $response): array
    {
        $hints = [];
        preg_match_all('/HINT \d+:\s*(.+?)(?=HINT \d+:|$)/s', $response, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $hint) {
                $hints[] = trim($hint);
            }
        }

        while (count($hints) < 3) {
            $hints[] = 'Try breaking the problem into smaller steps.';
        }

        return array_slice($hints, 0, 3);
    }

    protected function getLocalFeedback(string $code, CodingExercise $exercise, ?string $output, ?string $error): string
    {
        if ($error) {
            if (str_contains($error, 'SyntaxError')) {
                return "Check your syntax! Common Python syntax issues:\n• Missing colon `:` after if/for/while/def statements\n• Mismatched quotes or parentheses\n• Incorrect indentation\n\nYou're almost there! 😊";
            }

            if (str_contains($error, 'IndentationError')) {
                return "Indentation is crucial in Python! Make sure:\n• Use consistent spaces (4 recommended) or tabs\n• Lines with the same indentation are at the same level\n• No mixing of spaces and tabs\n\nYou can do it! 💪";
            }

            return "There's an error in your code. Don't worry - debugging is part of learning! Try:\n• Reading the error message carefully\n• Checking for typos\n• Making sure brackets and quotes match";
        }

        if ($output && $exercise->expected_output) {
            $normalizedOutput = trim($output);
            $normalizedExpected = trim($exercise->expected_output);

            if (str_contains($normalizedOutput, $normalizedExpected) || $normalizedOutput === $normalizedExpected) {
                return "Great job! Your code works correctly. Keep practicing! 🎉";
            }

            return "Your code runs, but the output doesn't match. Try again - you're making progress! 💪\n\nExpected to see: {$exercise->expected_output}";
        }

        return "Code executed successfully! Test it with different inputs to make sure it works correctly. 🔍";
    }

    protected function getLocalHints(CodingExercise $exercise): array
    {
        return [
            'Think about what concepts this exercise is testing. Break the problem into smaller steps!',
            'Consider the pattern or logic needed. Use pseudocode if it helps organize your thoughts.',
            "Try this approach: \n1. Understand the input/output requirements\n2. Plan your steps\n3. Write the code\n4. Test with example values\n\nExample starter code might look like:\n```python\n# Your solution here\n```",
        ];
    }
}