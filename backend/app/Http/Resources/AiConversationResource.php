<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AiConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'response' => $this->response,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
