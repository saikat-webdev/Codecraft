<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AIChatRequest;
use App\Models\AiConversation;
use App\Services\GeminiInstructorService;
use App\Services\N8nAiInstructorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class AIController extends BaseApiController
{
    public function __construct(
        protected N8nAiInstructorService $n8n,
        protected GeminiInstructorService $gemini,
    ) {}

    public function chat(AIChatRequest $request): JsonResponse
    {
        $message = $request->validated('message');
        $userId = (int) ($this->resolveAuthenticatedUser($request)?->id ?? 1);

        $n8nUrl = (string) config('services.n8n.webhook_url');
        if (str_contains($n8nUrl, '/webhook-test/')) {
            Log::info('AI chat: ignoring N8N_WEBHOOK_URL test URL; use /webhook/ai-teacher with workflow Active.');
        }

        if ($this->n8n->isConfigured()) {
            $result = $this->n8n->chat($message, $userId);
            if ($result !== null) {
                $this->storeConversation($request, $message, $result['reply']);

                return response()->json($result);
            }
        }

        if ($this->gemini->isConfigured()) {
            $reply = $this->gemini->reply($message, $userId);
            if ($reply !== null) {
                $this->storeConversation($request, $message, $reply);

                return response()->json([
                    'success' => true,
                    'reply' => $reply,
                ]);
            }
        }

        if (! $this->n8n->isConfigured() && ! $this->gemini->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'AI Instructor is not configured. Set N8N_WEBHOOK_URL or GEMINI_API_KEY in backend/.env.',
            ], 503);
        }

        Log::error('AI Instructor: n8n and Gemini fallback both failed', [
            'n8n_url' => config('services.n8n.webhook_url'),
            'has_gemini_key' => $this->gemini->isConfigured(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $this->failureMessage(),
        ], 502);
    }

    protected function failureMessage(): string
    {
        if (str_contains((string) config('services.n8n.webhook_url'), '/webhook-test/')) {
            return 'n8n test URL cannot be used by the app. Switch N8N_WEBHOOK_URL to the production URL (/webhook/ai-teacher) and activate the workflow.';
        }

        if ($this->gemini->isConfigured()) {
            return 'AI Instructor is temporarily unavailable. n8n returned no data and the direct Gemini fallback failed — check your API key and n8n Executions.';
        }

        return 'n8n returned an empty response. Re-import the simplified workflow from n8n/codecraft-ai-teacher.workflow.json, attach Gemini credentials, activate it — or add GEMINI_API_KEY to backend/.env as a fallback.';
    }

    protected function storeConversation(AIChatRequest $request, string $message, string $reply): void
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return;
        }

        try {
            AiConversation::create([
                'user_id' => $user->id,
                'message' => $message,
                'response' => $reply,
            ]);
        } catch (\Throwable $e) {
            Log::debug('AI conversation log skipped', ['error' => $e->getMessage()]);
        }
    }

    protected function resolveAuthenticatedUser(AIChatRequest $request): ?\App\Models\User
    {
        if ($request->user()) {
            return $request->user();
        }

        $token = $request->bearerToken();
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            $tokenable = $accessToken?->tokenable;
            if ($tokenable instanceof \App\Models\User) {
                return $tokenable;
            }
        }

        return null;
    }
}
