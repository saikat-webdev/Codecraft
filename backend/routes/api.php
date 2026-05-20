<?php

// use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('lessons', [LessonController::class, 'index']);
Route::get('lessons/{lesson:slug}', [LessonController::class, 'show']);
Route::get('lessons/{lesson:slug}/quizzes', [QuizController::class, 'byLesson']);
Route::post('quizzes/submit', [QuizController::class, 'submit']);

Route::get('modules', [ModuleController::class, 'index']);
Route::get('modules/{module:slug}', [ModuleController::class, 'show']);
Route::get('modules/{module:slug}/lessons', [ModuleController::class, 'lessons']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('lessons', [LessonController::class, 'store']);
    Route::post('progress', [ProgressController::class, 'update']);
    Route::get('progress', [ProgressController::class, 'index']);
    // AI feature is temporarily disabled
    // Route::post('ai/conversations', [AiChatController::class, 'store']);
    // Route::post('ai/prompt', [AiChatController::class, 'prompt']);
    // Route::get('ai/lessons/{lesson:slug}', [AiChatController::class, 'lessonHelp']);
});
