<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'admin_settings'],
            ['value' => [
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
            ]]
        );
    }
}
