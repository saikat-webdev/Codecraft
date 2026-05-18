<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:lessons,slug'],
            'description' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'difficulty' => ['required', 'string', 'in:beginner,intermediate,advanced'],
            'language' => ['required', 'string', 'max:100'],
        ];
    }
}
