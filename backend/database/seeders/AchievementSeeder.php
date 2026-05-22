<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            ['slug' => 'first_lesson', 'name' => 'First Steps', 'description' => 'Complete your first lesson', 'icon' => '🎯', 'xp_reward' => 25, 'criteria_key' => 'lessons_completed', 'criteria_value' => 1],
            ['slug' => 'lesson_5', 'name' => 'Quick Learner', 'description' => 'Complete 5 lessons', 'icon' => '📚', 'xp_reward' => 50, 'criteria_key' => 'lessons_completed', 'criteria_value' => 5],
            ['slug' => 'streak_3', 'name' => 'On Fire', 'description' => 'Maintain a 3-day streak', 'icon' => '🔥', 'xp_reward' => 40, 'criteria_key' => 'streak_days', 'criteria_value' => 3],
            ['slug' => 'streak_7', 'name' => 'Unstoppable', 'description' => 'Maintain a 7-day streak', 'icon' => '⚡', 'xp_reward' => 100, 'criteria_key' => 'streak_days', 'criteria_value' => 7],
            ['slug' => 'xp_100', 'name' => 'XP Collector', 'description' => 'Earn 100 total XP', 'icon' => '✨', 'xp_reward' => 20, 'criteria_key' => 'total_xp', 'criteria_value' => 100],
            ['slug' => 'xp_500', 'name' => 'Code Champion', 'description' => 'Earn 500 total XP', 'icon' => '🏆', 'xp_reward' => 75, 'criteria_key' => 'total_xp', 'criteria_value' => 500],
            ['slug' => 'exercise_3', 'name' => 'Problem Solver', 'description' => 'Pass 3 coding exercises', 'icon' => '💻', 'xp_reward' => 35, 'criteria_key' => 'exercises_passed', 'criteria_value' => 3],
            ['slug' => 'sudden_1', 'name' => 'Quick Thinker', 'description' => 'Pass a sudden test', 'icon' => '⏱️', 'xp_reward' => 30, 'criteria_key' => 'sudden_tests_passed', 'criteria_value' => 1],
            ['slug' => 'sudden_5', 'name' => 'Pop Quiz Pro', 'description' => 'Pass 5 sudden tests', 'icon' => '🧠', 'xp_reward' => 80, 'criteria_key' => 'sudden_tests_passed', 'criteria_value' => 5],
            ['slug' => 'level_3', 'name' => 'Rising Star', 'description' => 'Reach level 3', 'icon' => '🌟', 'xp_reward' => 50, 'criteria_key' => 'level_reached', 'criteria_value' => 3],
        ];

        foreach ($achievements as $data) {
            Achievement::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
