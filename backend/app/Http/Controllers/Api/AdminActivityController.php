<?php

namespace App\Http\Controllers\Api;

use App\Models\ExerciseSubmission;
use App\Models\ExamAttempt;
use App\Models\SuddenTestAttempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminActivityController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type', 'all');
        $search = strtolower(trim((string) $request->query('search', '')));

        $logs = collect();

        if (in_array($type, ['all', 'user'], true)) {
            User::orderByDesc('created_at')->limit(15)->get()->each(function (User $user) use ($logs) {
                $logs->push([
                    'id' => 'user-' . $user->id,
                    'type' => 'user_register',
                    'description' => "New user registered: {$user->name}",
                    'time_ago' => $user->created_at->diffForHumans(),
                    'timestamp' => $user->created_at,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar_url' => $user->avatar_url,
                    ],
                    'ip_address' => null,
                    'details' => ['email' => $user->email],
                ]);
            });
        }

        if (in_array($type, ['all', 'code'], true)) {
            ExerciseSubmission::with('user')
                ->orderByDesc('created_at')
                ->limit(15)
                ->get()
                ->each(function (ExerciseSubmission $submission) use ($logs) {
                    if (!$submission->user) {
                        return;
                    }

                    $logs->push([
                        'id' => 'submission-' . $submission->id,
                        'type' => 'exercise_submit',
                        'description' => "{$submission->user->name} submitted an exercise",
                        'time_ago' => $submission->created_at->diffForHumans(),
                        'timestamp' => $submission->created_at,
                        'user' => [
                            'id' => $submission->user->id,
                            'name' => $submission->user->name,
                            'avatar_url' => $submission->user->avatar_url,
                        ],
                        'ip_address' => null,
                        'details' => [
                            'is_correct' => $submission->is_correct,
                            'exercise_id' => $submission->exercise_id,
                        ],
                    ]);
                });
        }

        if (in_array($type, ['all', 'system'], true)) {
            ExamAttempt::with(['user', 'exam'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->each(function (ExamAttempt $attempt) use ($logs) {
                    if (!$attempt->user) {
                        return;
                    }

                    $logs->push([
                        'id' => 'exam-' . $attempt->id,
                        'type' => $attempt->passed ? 'lesson_complete' : 'sudden_test',
                        'description' => "{$attempt->user->name} completed exam: {$attempt->exam?->title}",
                        'time_ago' => $attempt->created_at->diffForHumans(),
                        'timestamp' => $attempt->created_at,
                        'user' => [
                            'id' => $attempt->user->id,
                            'name' => $attempt->user->name,
                            'avatar_url' => $attempt->user->avatar_url,
                        ],
                        'ip_address' => null,
                        'details' => [
                            'score' => $attempt->percentage,
                            'passed' => $attempt->passed,
                        ],
                    ]);
                });

            SuddenTestAttempt::with('user')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->each(function (SuddenTestAttempt $attempt) use ($logs) {
                    if (!$attempt->user) {
                        return;
                    }

                    $logs->push([
                        'id' => 'sudden-' . $attempt->id,
                        'type' => 'sudden_test',
                        'description' => "{$attempt->user->name} answered a sudden test",
                        'time_ago' => $attempt->created_at->diffForHumans(),
                        'timestamp' => $attempt->created_at,
                        'user' => [
                            'id' => $attempt->user->id,
                            'name' => $attempt->user->name,
                            'avatar_url' => $attempt->user->avatar_url,
                        ],
                        'ip_address' => null,
                        'details' => [
                            'passed' => (bool) $attempt->passed,
                        ],
                    ]);
                });
        }

        if ($search !== '') {
            $logs = $logs->filter(function (array $log) use ($search) {
                return str_contains(strtolower($log['description']), $search)
                    || str_contains(strtolower($log['type']), $search)
                    || str_contains(strtolower($log['user']['name'] ?? ''), $search);
            });
        }

        $sorted = $logs->sortByDesc('timestamp')->values()->take(50)->map(function (array $log) {
            unset($log['timestamp']);

            return $log;
        });

        return $this->success($sorted, 'Activity logs retrieved');
    }
}
