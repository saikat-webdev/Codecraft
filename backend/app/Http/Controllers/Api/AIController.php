<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AIChatRequest;
use App\Models\AiConversation;
use App\Models\User;
use App\Services\GeminiInstructorService;
use App\Services\KnowledgeRetrievalService;
use App\Services\N8nAiInstructorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIController extends BaseApiController
{
    private const HISTORY_LIMIT = 100;

    public function __construct(
        protected N8nAiInstructorService $n8n,
        protected GeminiInstructorService $gemini,
        protected KnowledgeRetrievalService $knowledge,
    ) {}

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $rows = AiConversation::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit(self::HISTORY_LIMIT)
            ->get(['id', 'message', 'response', 'created_at']);

        $messages = [];
        foreach ($rows as $row) {
            if (filled($row->message)) {
                $messages[] = [
                    'id' => "conv-{$row->id}-user",
                    'role' => 'user',
                    'content' => $row->message,
                    'created_at' => $row->created_at?->toIso8601String(),
                ];
            }
            if (filled($row->response)) {
                $messages[] = [
                    'id' => "conv-{$row->id}-assistant",
                    'role' => 'assistant',
                    'content' => $row->response,
                    'created_at' => $row->created_at?->toIso8601String(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    public function chat(AIChatRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $message = $request->validated('message');
        $userId = (int) $user->id;
        $knowledgeContext = $this->knowledge->buildContextForMessage($message);

        $reply = $this->resolveReply($message, $userId, $knowledgeContext);

        if ($reply === null) {
            Log::error('AI Instructor: all providers failed', [
                'prefer_n8n' => config('services.ai.prefer_n8n'),
                'has_gemini_key' => $this->gemini->isConfigured(),
                'has_n8n' => $this->n8n->isConfigured(),
                'gemini_model' => config('services.gemini.model'),
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->failureMessage(),
            ], 502);
        }

        $this->storeConversation($user, $message, $reply);

        return response()->json([
            'success' => true,
            'reply' => $reply,
        ]);
    }

    public function clearHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        AiConversation::query()->where('user_id', $user->id)->delete();

        return response()->json(['success' => true]);
    }

  /**
   * Gemini direct first (reliable). n8n only when AI_PREFER_N8N=true.
   */
    protected function resolveReply(string $message, int $userId, string $knowledgeContext): ?string
    {
        $preferN8n = (bool) config('services.ai.prefer_n8n', false);

        if ($preferN8n) {
            $fromN8n = $this->tryN8n($message, $userId, $knowledgeContext);
            if ($fromN8n !== null) {
                return $fromN8n;
            }

            return $this->tryGemini($message, $userId, $knowledgeContext);
        }

        $fromGemini = $this->tryGemini($message, $userId, $knowledgeContext);
        if ($fromGemini !== null) {
            return $fromGemini;
        }

        return $this->tryN8n($message, $userId, $knowledgeContext);
    }

    protected function tryGemini(string $message, int $userId, string $knowledgeContext): ?string
    {
        if (! $this->gemini->isConfigured()) {
            return null;
        }

        $reply = $this->gemini->reply($message, $userId, $knowledgeContext);

        return $this->acceptReply($reply);
    }

    protected function tryN8n(string $message, int $userId, string $knowledgeContext): ?string
    {
        if (! $this->n8n->isConfigured()) {
            return null;
        }

        $result = $this->n8n->chat($message, $userId, $knowledgeContext);
        if ($result === null) {
            return null;
        }

        return $this->acceptReply($result['reply'] ?? null);
    }

    protected function acceptReply(?string $reply): ?string
    {
        if (! is_string($reply) || trim($reply) === '') {
            return null;
        }

        $reply = trim($reply);

        if ($this->isBrokenN8nPlaceholder($reply)) {
            return null;
        }

        return $reply;
    }

    protected function isBrokenN8nPlaceholder(string $reply): bool
    {
        return str_contains($reply, 'Sorry, the AI could not generate a reply')
            || str_contains($reply, 'Open Gemini AI Teacher');
    }

    protected function failureMessage(): string
    {
        if (! $this->gemini->isConfigured()) {
            return 'AI Instructor is not configured. Add GEMINI_API_KEY to backend/.env (from Google AI Studio), then run: php artisan config:clear';
        }

        return 'AI Instructor could not reach Gemini. Check GEMINI_API_KEY and GEMINI_MODEL in backend/.env (try gemini-2.0-flash), then run: php artisan config:clear';
    }

    protected function storeConversation(User $user, string $message, string $reply): void
    {
        try {
            AiConversation::create([
                'user_id' => $user->id,
                'message' => $message,
                'response' => $reply,
            ]);
        } catch (\Throwable $e) {
            Log::warning('AI conversation save failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
