# Module 09 — Laravel API

## Purpose

Integrates the AI Teacher pipeline into a Laravel monolith using a clean service-layer architecture. Follows Laravel conventions: validation in controllers, business logic in services, models for data access.

---

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── AiTeacherController.php
├── Services/
│   ├── GrokService.php
│   ├── EmbeddingService.php
│   ├── RagService.php
│   └── AiTeacherService.php
├── Models/
│   ├── UserProgress.php
│   └── AiMemory.php
routes/
└── api.php
config/
└── services.php       (add grok + openai keys here)
```

---

## Configuration: `config/services.php`

```php
return [
    // ...existing entries...

    'grok' => [
        'api_key'  => env('GROK_API_KEY'),
        'base_url' => 'https://api.x.ai/v1',
        'model'    => env('GROK_MODEL', 'grok-3-mini'),
    ],

    'openai' => [
        'api_key'         => env('OPENAI_API_KEY'),
        'embedding_model' => 'text-embedding-3-small',
    ],
];
```

**`.env` additions:**
```ini
GROK_API_KEY=xai-xxxxxxxxxxxxxxxx
OPENAI_API_KEY=sk-xxxxxxxxxxxxxxxx
SUPABASE_URL=https://xxxx.supabase.co
SUPABASE_SERVICE_KEY=eyJ...
```

---

## Service: `GrokService.php`

Handles Grok API calls with retry logic.

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GrokService
{
    private string $baseUrl;
    private string $apiKey;
    private int    $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey  = config('services.grok.api_key');
        $this->baseUrl = config('services.grok.base_url');
    }

    public function chat(array $messages, array $options = []): array
    {
        $payload = array_merge([
            'model'       => config('services.grok.model'),
            'messages'    => $messages,
            'temperature' => 0.7,
            'max_tokens'  => 1500,
        ], $options);

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout(30)
                    ->post("{$this->baseUrl}/chat/completions", $payload);

                if ($response->successful()) {
                    return [
                        'content'       => $response->json('choices.0.message.content'),
                        'finish_reason' => $response->json('choices.0.finish_reason'),
                        'usage'         => $response->json('usage'),
                        'model'         => $response->json('model'),
                    ];
                }

                // Don't retry client errors except 429
                if ($response->status() < 500 && $response->status() !== 429) {
                    throw new Exception("Grok API error {$response->status()}: {$response->body()}");
                }

                $lastException = new Exception("Grok server error: {$response->status()}");
            } catch (Exception $e) {
                $lastException = $e;
            }

            $attempt++;
            if ($attempt < $this->maxRetries) {
                sleep((int) pow(2, $attempt - 1));
            }
        }

        throw $lastException ?? new Exception('Grok API failed after retries');
    }
}
```

---

## Service: `EmbeddingService.php`

Generates OpenAI embeddings.

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class EmbeddingService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function embed(string $text): array
    {
        $input = substr(str_replace("\n", ' ', trim($text)), 0, 8000);

        $response = Http::withToken($this->apiKey)
            ->timeout(15)
            ->post('https://api.openai.com/v1/embeddings', [
                'model'           => config('services.openai.embedding_model'),
                'input'           => $input,
                'encoding_format' => 'float',
            ]);

        if (!$response->successful()) {
            throw new Exception("OpenAI embedding failed: {$response->body()}");
        }

        return $response->json('data.0.embedding');
    }
}
```

---

## Service: `RagService.php`

Queries Supabase for vector search.

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RagService
{
    private string $supabaseUrl;
    private string $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url');
        $this->supabaseKey = config('services.supabase.service_key');
    }

    private function headers(): array
    {
        return [
            'apikey'        => $this->supabaseKey,
            'Authorization' => "Bearer {$this->supabaseKey}",
            'Content-Type'  => 'application/json',
        ];
    }

    public function retrieve(array $embedding, string $roadmapId, float $threshold = 0.7, int $count = 5): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->post("{$this->supabaseUrl}/rest/v1/rpc/match_lesson_chunks", [
                'query_embedding' => $embedding,
                'p_roadmap_id'    => $roadmapId,
                'match_threshold' => $threshold,
                'match_count'     => $count,
            ]);

        return $response->successful() ? $response->json() : [];
    }
}
```

---

## Service: `AiTeacherService.php`

Orchestrates all services. This is the main business logic class.

```php
<?php

namespace App\Services;

use App\Models\UserProgress;
use App\Models\AiMemory;
use Illuminate\Support\Collection;

class AiTeacherService
{
    public function __construct(
        private GrokService      $grok,
        private EmbeddingService $embedding,
        private RagService       $rag,
    ) {}

    public function ask(string $question, string $userId, ?string $lessonId = null): array
    {
        // 1. Generate embedding
        $embeddingVector = $this->embedding->embed($question);

        // 2. Fetch user context
        $progress = $this->getProgress($userId);
        $memory   = $this->getMemory($userId);

        // 3. RAG retrieval
        $roadmapId = $this->getActiveRoadmapId($progress);
        $ragChunks = $roadmapId ? $this->rag->retrieve($embeddingVector, $roadmapId) : [];

        // 4. Build prompt messages
        $messages = $this->buildMessages($question, $progress->toArray(), $ragChunks, $memory);

        // 5. Call Grok
        $result = $this->grok->chat($messages);

        // 6. Persist memory (dispatch to queue in production)
        AiMemory::insert([
            ['user_id' => $userId, 'role' => 'user',      'content' => $question,           'lesson_id' => $lessonId],
            ['user_id' => $userId, 'role' => 'assistant', 'content' => $result['content'],  'lesson_id' => $lessonId],
        ]);

        return [
            'answer'      => $result['content'],
            'model'       => $result['model'],
            'usage'       => $result['usage'],
            'rag_sources' => count($ragChunks),
        ];
    }

    private function getProgress(string $userId): Collection
    {
        return UserProgress::with(['lessons.modules.roadmaps'])
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();
    }

    private function getMemory(string $userId): Collection
    {
        return AiMemory::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->reverse()
            ->values();
    }

    private function getActiveRoadmapId(Collection $progress): ?string
    {
        return $progress->firstWhere('status', 'in_progress')
            ?->lessons?->modules?->roadmap_id;
    }

    private function buildMessages(string $question, array $progress, array $ragChunks, Collection $memory): array
    {
        $ragContext = count($ragChunks) > 0
            ? collect($ragChunks)->map(fn($c, $i) => "[Source " . ($i + 1) . "]\n{$c['content']}")->implode("\n\n")
            : 'No specific lesson content matched. Answer from general knowledge.';

        $completed     = collect($progress)->where('status', 'completed')->count();
        $active        = collect($progress)->firstWhere('status', 'in_progress');
        $currentLesson = $active['lessons']['title'] ?? 'None';

        $systemPrompt = <<<PROMPT
You are an expert, patient, and encouraging AI teacher for Codecraft.

Teaching rules:
1. Explain simply — assume a smart beginner
2. Use short code examples in ```blocks```
3. Numbered steps for complex topics
4. End every response with ## Next Step
5. Max 600 words

Student Progress:
Completed lessons: {$completed}
Current lesson: {$currentLesson}

Relevant Course Content:
{$ragContext}
PROMPT;

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($memory as $msg) {
            $messages[] = ['role' => $msg->role, 'content' => $msg->content];
        }

        $messages[] = ['role' => 'user', 'content' => $question];

        return $messages;
    }
}
```

---

## Controller: `AiTeacherController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\AiTeacherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiTeacherController extends Controller
{
    public function __construct(private AiTeacherService $service) {}

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question'  => 'required|string|min:2|max:2000',
            'user_id'   => 'required|uuid',
            'lesson_id' => 'nullable|uuid',
        ]);

        try {
            $result = $this->service->ask(
                $validated['question'],
                $validated['user_id'],
                $validated['lesson_id'] ?? null,
            );

            return response()->json(['success' => true, ...$result]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error'   => 'Failed to generate a response. Please try again.',
            ], 500);
        }
    }
}
```

---

## Routes: `routes/api.php`

```php
use App\Http\Controllers\AiTeacherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('/teacher/ask', [AiTeacherController::class, 'ask']);
});
```

---

## Service Provider Binding (optional explicit binding)

In `app/Providers/AppServiceProvider.php`:

```php
public function register(): void
{
    $this->app->singleton(GrokService::class);
    $this->app->singleton(EmbeddingService::class);
    $this->app->singleton(RagService::class);
    $this->app->singleton(AiTeacherService::class);
}
```

---

## Models

### `UserProgress.php`
```php
class UserProgress extends Model
{
    protected $fillable = ['user_id', 'lesson_id', 'module_id', 'roadmap_id', 'status', 'score'];

    public function lessons() { return $this->belongsTo(Lesson::class, 'lesson_id'); }
}
```

### `AiMemory.php`
```php
class AiMemory extends Model
{
    protected $fillable = ['user_id', 'role', 'content', 'lesson_id'];
    public $timestamps  = false;

    protected $casts = ['created_at' => 'datetime'];
}
```

---

## Testing with Artisan Tinker

```php
php artisan tinker

$service = app(App\Services\AiTeacherService::class);
$result  = $service->ask(
    'What is a closure in JavaScript?',
    '550e8400-e29b-41d4-a716-446655440000'
);
echo $result['answer'];
```
