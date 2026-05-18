<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgressUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'completed' => ['required', 'boolean'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
