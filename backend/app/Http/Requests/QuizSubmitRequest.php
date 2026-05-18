<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'question' => ['required', 'string'],
            'selected_answer' => ['required', 'string'],
        ];
    }
}
