<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminSettingsController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $settings = [
            'branding' => [
                'site_name' => config('app.name'),
                'tagline' => 'Learn to code with live playgrounds',
                'logo_url' => null,
                'favicon_url' => null,
            ],
            'features' => [
                'maintenance_mode' => false,
                'registration_enabled' => true,
                'sudden_tests_enabled' => true,
                'leaderboard_enabled' => true,
                'achievements_enabled' => true,
            ],
            'judge0' => [
                'base_url' => config('services.judge0.base_url', env('JUDGE0_BASE_URL', 'https://ce.judge0.com')),
                'api_key' => config('services.judge0.api_key') ? '***hidden***' : null,
            ],
        ];

        return $this->success($settings, 'Settings retrieved');
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branding' => ['nullable', 'array'],
            'branding.site_name' => ['nullable', 'string', 'max:100'],
            'branding.tagline' => ['nullable', 'string', 'max:200'],
            'branding.logo_url' => ['nullable', 'url', 'max:500'],
            'branding.favicon_url' => ['nullable', 'url', 'max:500'],
            'features' => ['nullable', 'array'],
            'features.maintenance_mode' => ['nullable', 'boolean'],
            'features.registration_enabled' => ['nullable', 'boolean'],
            'features.sudden_tests_enabled' => ['nullable', 'boolean'],
            'features.leaderboard_enabled' => ['nullable', 'boolean'],
            'features.achievements_enabled' => ['nullable', 'boolean'],
        ]);

        // In a real app, these would be stored in a settings table or config file
        // For now, we'll just return success
        Cache::put('admin_settings', $validated);

        return $this->success($validated, 'Settings updated');
    }

    public function updateJudge0(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'base_url' => ['required', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:255'],
        ]);

        // Store in cache for runtime configuration
        Cache::put('judge0_settings', $validated);

        return $this->success($validated, 'Judge0 settings updated');
    }
}