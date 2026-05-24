<?php

namespace App\Services;

use App\Models\Setting;

class AppSettingsService
{
    protected array $defaults = [
        'branding' => [
            'site_name' => 'CodeCraft',
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
        'avatars' => [
            'mode' => 'default',
            'available_modes' => ['default', 'superb'],
        ],
    ];

    protected function getStoredSettings(): array
    {
        $row = Setting::firstWhere('key', 'admin_settings');

        if (! $row) {
            return [];
        }

        return $row->value ?? [];
    }

    public function getSettings(): array
    {
        $stored = $this->getStoredSettings();

        return [
            'branding' => array_merge($this->defaults['branding'], $stored['branding'] ?? []),
            'features' => array_merge($this->defaults['features'], $stored['features'] ?? []),
            'avatars' => array_merge($this->defaults['avatars'], $stored['avatars'] ?? []),
        ];
    }

    public function getPublicSettings(): array
    {
        $settings = $this->getSettings();

        return [
            'features' => $settings['features'],
            'avatars' => [
                'mode' => $settings['avatars']['mode'],
                'available_modes' => $settings['avatars']['available_modes'],
            ],
        ];
    }

    public function saveSettings(array $settings): void
    {
        $stored = $this->getStoredSettings();
        $merged = array_replace_recursive($stored, $settings);

        Setting::updateOrCreate(
            ['key' => 'admin_settings'],
            ['value' => $merged]
        );
    }

    public function getFeatures(): array
    {
        return $this->getSettings()['features'];
    }

    public function isMaintenanceMode(): bool
    {
        // return $this->getFeatures()['maintenance_mode'] ?? false;
        return false; // Temporarily disable maintenance mode check to allow registration during maintenance
    }

    public function isRegistrationEnabled(): bool
    {
        return $this->getFeatures()['registration_enabled'] ?? true;
    }

    public function isLeaderboardEnabled(): bool
    {
        return $this->getFeatures()['leaderboard_enabled'] ?? true;
    }

    public function isAchievementsEnabled(): bool
    {
        return $this->getFeatures()['achievements_enabled'] ?? true;
    }
}
