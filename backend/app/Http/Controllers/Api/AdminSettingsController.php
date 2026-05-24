<?php

namespace App\Http\Controllers\Api;

use App\Services\AvatarStyleService;
use App\Services\AppSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AdminSettingsController extends BaseApiController
{
    public function index(AppSettingsService $settingsService): JsonResponse
    {
        $settings = $settingsService->getSettings();
        $settings['judge0'] = array_merge([
            'base_url' => config('services.judge0.base_url', env('JUDGE0_BASE_URL', 'https://ce.judge0.com')),
            'api_key' => config('services.judge0.api_key') ? '***hidden***' : null,
        ], Cache::get('judge0_settings', []));

        return $this->success($settings, 'Settings retrieved');
    }

    public function update(Request $request): JsonResponse
    {
        $avatarService = app(AvatarStyleService::class);
        $settingsService = app(AppSettingsService::class);

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
            'avatars' => ['nullable', 'array'],
            'avatars.mode' => ['nullable', 'string', Rule::in($avatarService->getModeOptions())],
        ]);

        if (isset($validated['avatars']['mode'])) {
            $avatarService->setMode($validated['avatars']['mode']);
        }

        $settingsService->saveSettings($validated);

        return $this->success($settingsService->getSettings(), 'Settings updated');
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