<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = $app->make(App\Services\GeminiInstructorService::class);
    $user = App\Models\User::first();
    echo 'USER: ' . ($user ? $user->id : 'none') . "\n";

    $config = config('services.gemini', []);
    $apiKey = $config['key'];
    $model = $config['model'] ?? env('GEMINI_MODEL', 'gemini-pro');

    $body = [
        'messages' => [
            ['author' => 'system', 'content' => [['type' => 'text', 'text' => 'Test message']]],
            ['author' => 'user', 'content' => [['type' => 'text', 'text' => 'What is Python?']]],
        ],
        'temperature' => 0.65,
        'maxOutputTokens' => 512,
    ];

    $alternateBody = [
        'instances' => [
            ['content' => 'What is Python?'],
        ],
        'parameters' => [
            'temperature' => 0.65,
            'maxOutputTokens' => 512,
        ],
    ];

    $otherModel = 'text-bison-001';
    $endpoints = [
        ["https://gemini.googleapis.com/v1/models/{$model}:generateMessage?key={$apiKey}", $body],
        ["https://gemini.googleapis.com/v1beta1/models/{$model}:generateMessage?key={$apiKey}", $body],
        ["https://gemini.googleapis.com/v1beta2/models/{$model}:generateMessage?key={$apiKey}", $body],
        ["https://generativelanguage.googleapis.com/v1beta2/models/{$model}:generateMessage?key={$apiKey}", [
            'prompt' => [
                'messages' => [
                    ['author' => 'system', 'content' => 'You are an expert beginner-friendly Python instructor.'],
                    ['author' => 'user', 'content' => 'What is Python?'],
                ],
            ],
            'temperature' => 0.65,
        ]],
        ["https://generativelanguage.googleapis.com/v1beta2/models/{$model}:generateMessage?key={$apiKey}", [
            'prompt' => [
                'messages' => [
                    ['author' => 'user', 'content' => 'What is Python?'],
                ],
            ],
            'temperature' => 0.65,
        ]],
        ["https://generativelanguage.googleapis.com/v1beta2/models/{$model}:generateMessage?key={$apiKey}", [
            'prompt' => [
                'messages' => [
                    ['author' => 'user', 'content' => 'What is Python?'],
                ],
            ],
        ]],
        ["https://generativelanguage.googleapis.com/v1beta2/models/{$model}:generateText?key={$apiKey}", ['prompt' => ['text' => 'What is Python?'], 'temperature' => 0.65, 'maxOutputTokens' => 512]],
        ["https://generativelanguage.googleapis.com/v1beta2/models/{$model}:generateText?key={$apiKey}", ['model' => $model, 'prompt' => ['text' => 'What is Python?'], 'temperature' => 0.65, 'maxOutputTokens' => 512]],
        ["https://generativelanguage.googleapis.com/v1beta2/models/{$otherModel}:generateText?key={$apiKey}", ['prompt' => ['text' => 'What is Python?'], 'temperature' => 0.65, 'maxOutputTokens' => 512]],
    ];

    foreach ($endpoints as $index => [$endpoint, $payload]) {
        echo "\nTEST ENDPOINT #" . ($index + 1) . ": {$endpoint}\n";
        echo "PAYLOAD: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
        $response = Illuminate\Support\Facades\Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        echo "STATUS: " . $response->status() . "\n";
        echo "FAILED: " . ($response->failed() ? 'yes' : 'no') . "\n";
        echo "BODY: " . $response->body() . "\n";
        echo "JSON: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
