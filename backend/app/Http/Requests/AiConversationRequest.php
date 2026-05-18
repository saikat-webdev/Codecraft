<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:4000'],
            'response' => ['nullable', 'string'],
        ];
    }
}
