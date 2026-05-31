<?php

use App\Http\Controllers\Api\AIController;
use App\Http\Controllers\Api\AdminActivityController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\AdminSettingsController;
use App\Http\Controllers\Api\AdminSuddenTestController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CodeEvaluationController;
use App\Http\Controllers\Api\CodeExecutionController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SuddenTestController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Allow login route to remain accessible so admins can sign in during maintenance.
Route::post('login', [AuthController::class, 'login']);

// Public routes that should be influenced by feature flags (maintenance, registration, etc.)
Route::middleware('features')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('registration');

    Route::get('settings', [SettingsController::class, 'index']);
    Route::get('lessons', [LessonController::class, 'index']);
    Route::get('lessons/{lesson:slug}', [LessonController::class, 'show']);
    Route::get('lessons/{lesson:slug}/quizzes', [QuizController::class, 'byLesson']);
    Route::post('quizzes/submit', [QuizController::class, 'submit']);

    Route::get('modules', [ModuleController::class, 'index']);
    Route::get('modules/{module:slug}', [ModuleController::class, 'show']);
    Route::get('modules/{module:slug}/lessons', [ModuleController::class, 'lessons']);

    Route::get('exams', [ExamController::class, 'index']);
    Route::get('exams/{exam:slug}', [ExamController::class, 'show']);

    Route::get('lessons/{lesson:slug}/exercises', [ExerciseController::class, 'byLesson']);
    Route::get('exercises/{exercise}', [ExerciseController::class, 'show']);

    Route::post('code/run', [CodeExecutionController::class, 'run']);

    Route::get('sudden-tests/config', [SuddenTestController::class, 'config']);
});

// Authenticated routes: run auth first, then feature checks so admins are recognized.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('lessons', [LessonController::class, 'store']);
    Route::post('progress', [ProgressController::class, 'update']);
    Route::get('progress', [ProgressController::class, 'index']);

    // Apply features middleware after authentication to allow admins through.
    Route::middleware('features')->group(function () {
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::post('profile/avatar/style', [ProfileController::class, 'setAvatarStyle']);
        Route::get('profile/avatar/styles', [ProfileController::class, 'avatarStyles']);
        Route::get('profile/stats', [ProfileController::class, 'stats']);
        Route::get('profile/achievements', [ProfileController::class, 'achievements']);

        Route::get('sudden-tests/challenge', [SuddenTestController::class, 'challenge']);
        Route::post('sudden-tests/{question}/submit', [SuddenTestController::class, 'submit']);

        Route::get('exam-attempts', [ExamController::class, 'myAttempts']);
        Route::post('exams/{exam:slug}/submit', [ExamController::class, 'submit']);

        Route::post('exercises/{exercise}/submit', [CodeEvaluationController::class, 'submit']);
        Route::post('exercises/{exercise}/evaluate', [CodeEvaluationController::class, 'evaluate']);
        Route::get('exercises/{exercise}/hints', [CodeEvaluationController::class, 'hints']);
        Route::get('submissions/history', [CodeEvaluationController::class, 'history']);
        Route::post('exercises', [ExerciseController::class, 'store']);

        Route::get('ai/history', [AIController::class, 'history']);
        Route::post('ai/chat', [AIController::class, 'chat']);
        Route::delete('ai/history', [AIController::class, 'clearHistory']);

        Route::middleware('admin')->prefix('admin')->group(function () {
            // Dashboard
            Route::get('dashboard/stats', [AdminDashboardController::class, 'stats']);
            Route::get('dashboard/activity', [AdminDashboardController::class, 'activity']);
            Route::get('dashboard/leaderboard', [AdminDashboardController::class, 'leaderboard']);
            Route::get('activity-logs', [AdminActivityController::class, 'index']);

            // Users
            Route::get('users', [AdminUserController::class, 'index']);
            Route::get('users/{user}', [AdminUserController::class, 'show']);
            Route::put('users/{user}', [AdminUserController::class, 'update']);
            Route::post('users/{user}/suspend', [AdminUserController::class, 'suspend']);
            Route::post('users/{user}/activate', [AdminUserController::class, 'activate']);
            Route::post('users/{user}/role', [AdminUserController::class, 'assignRole']);

            // Settings
            Route::get('settings', [AdminSettingsController::class, 'index']);
            Route::put('settings', [AdminSettingsController::class, 'update']);
            Route::put('settings/judge0', [AdminSettingsController::class, 'updateJudge0']);

            // Sudden Tests (existing)
            Route::get('sudden-tests/settings', [AdminSuddenTestController::class, 'settings']);
            Route::put('sudden-tests/settings', [AdminSuddenTestController::class, 'updateSettings']);
            Route::get('sudden-tests/questions', [AdminSuddenTestController::class, 'questions']);
            Route::post('sudden-tests/questions', [AdminSuddenTestController::class, 'storeQuestion']);
            Route::put('sudden-tests/questions/{question}', [AdminSuddenTestController::class, 'updateQuestion']);
            Route::delete('sudden-tests/questions/{question}', [AdminSuddenTestController::class, 'destroyQuestion']);
        });
    });
});
