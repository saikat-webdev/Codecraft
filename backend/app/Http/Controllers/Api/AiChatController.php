<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AiConversationRequest;
use App\Models\AiInstructorSession;
use App\Models\Lesson;
use App\Services\GeminiInstructorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends BaseApiController
{
    public function store(AiConversationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $conversation = AiInstructorSession::create([
            'user_id' => $user->id,
            'lesson_id' => $data['lesson_id'] ?? null,
            'user_message' => $data['message'],
            'ai_response' => $data['response'] ?? '',
        ]);

        return $this->success(['conversation' => $conversation], 'Conversation saved', 201);
    }

    public function prompt(Request $request, GeminiInstructorService $instructor): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'lesson_slug' => 'nullable|string|exists:lessons,slug',
        ]);

        $user = $request->user();
        $message = trim($validated['message']);
        $lesson = null;

        if (!empty($validated['lesson_slug'])) {
            $lesson = Lesson::where('slug', $validated['lesson_slug'])->first();
        }

        $responseText = $instructor->generateReply($user, $message, $lesson);

        if (empty($responseText)) {
            return $this->error('Unable to generate an AI instructor response at this time.', 500);
        }

        $conversation = AiInstructorSession::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson?->id,
            'user_message' => $message,
            'ai_response' => $responseText,
        ]);

        return $this->success([
            'reply' => $responseText,
            'conversation_id' => $conversation->id,
        ], 'AI instructor reply generated');
    }

    public function lessonHelp(Lesson $lesson, Request $request, GeminiInstructorService $instructor): JsonResponse
    {
        $user = $request->user();
        $message = sprintf(
            'Please introduce the lesson "%s" in a kind, beginner-friendly way. Help the student understand the main idea and offer a small next step.',
            $lesson->title,
        );

        $responseText = $instructor->generateReply($user, $message, $lesson);

        return $this->success(['reply' => $responseText], 'Lesson help generated');
    }
}
