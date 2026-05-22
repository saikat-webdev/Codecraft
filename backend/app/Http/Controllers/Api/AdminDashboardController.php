<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\ExerciseSubmission;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\CodingExercise;
use App\Models\SuddenTestAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends BaseApiController
{
    public function stats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('last_activity_date', '>=', now()->subDays(7))->count(),
            'active_percentage' => User::count() > 0 
                ? round((User::where('last_activity_date', '>=', now()->subDays(7))->count() / User::count()) * 100, 1) 
                : 0,
            'new_users_this_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'total_executions' => ExerciseSubmission::count(),
            'total_courses' => Module::count(),
            'published_courses' => Module::count(), // All modules are published for now
            'total_challenges' => CodingExercise::count(),
            'sudden_test_attempts' => SuddenTestAttempt::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return $this->success($stats, 'Dashboard stats retrieved');
    }

    public function activity(): JsonResponse
    {
        $activities = [];

        // Recent user registrations
        $recentUsers = User::orderByDesc('created_at')->limit(5)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user_register',
                'icon' => '👤',
                'message' => "New user registered: {$user->name}",
                'time_ago' => $user->created_at->diffForHumans(),
                'timestamp' => $user->created_at->toISOString(),
            ];
        }

        // Recent submissions
        $recentSubmissions = ExerciseSubmission::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        foreach ($recentSubmissions as $submission) {
            if ($submission->user) {
                $activities[] = [
                    'type' => 'code_execute',
                    'icon' => '💻',
                    'message' => "{$submission->user->name} submitted code",
                    'time_ago' => $submission->created_at->diffForHumans(),
                    'timestamp' => $submission->created_at->toISOString(),
                ];
            }
        }

        // Sort by timestamp
        usort($activities, fn($a, $b) => strtotime($b['timestamp']) - strtotime($a['timestamp']));

        return $this->success(array_slice($activities, 0, 10), 'Recent activity retrieved');
    }

    public function leaderboard(): JsonResponse
    {
        $leaderboard = User::orderByDesc('xp')
            ->orderByDesc('level')
            ->limit(10)
            ->get(['id', 'name', 'email', 'avatar_path', 'xp', 'level', 'streak_count']);

        return $this->success($leaderboard, 'Leaderboard retrieved');
    }
}