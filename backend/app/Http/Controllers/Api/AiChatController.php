<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AiConversationRequest;
use App\Http\Resources\AiConversationResource;
use App\Models\AiConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends BaseApiController
{
    public function store(AiConversationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $conv = AiConversation::create([
            'user_id' => $user->id,
            'message' => $data['message'],
            'response' => $data['response'] ?? null,
        ]);

        return $this->success(new AiConversationResource($conv), 'Conversation saved', 201);
    }

    public function prompt(Request $request): JsonResponse
    {
        // Placeholder to forward prompts to an AI service in later phases.
        $request->validate(['message' => 'required|string']);

        return $this->success(['reply' => 'This is a placeholder response.'], 'AI prompt received');
    }
}
