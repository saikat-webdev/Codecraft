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
            'module_id' => ['nullable', 'integer', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:lessons,slug'],
            'description' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'difficulty' => ['required', 'string', 'in:beginner,intermediate,advanced'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:180'],
            'order' => ['nullable', 'integer', 'min:0'],
            'language' => ['required', 'string', 'max:100'],
        ];
    }
}
