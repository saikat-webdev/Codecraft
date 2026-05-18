<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'learning_goal' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'max:100'],
            'daily_learning_time' => ['nullable', 'integer', 'min:1'],
            'skill_level' => ['nullable', 'string', 'max:100'],
        ];
    }
}
