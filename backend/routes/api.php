<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CodeEvaluationController;
use App\Http\Controllers\Api\CodeExecutionController;
use App\Http\Controllers\Api\ExerciseController;
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

Route::get('lessons/{lesson:slug}/exercises', [ExerciseController::class, 'byLesson']);
Route::get('exercises/{exercise}', [ExerciseController::class, 'show']);

Route::post('code/run', [CodeExecutionController::class, 'run']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('lessons', [LessonController::class, 'store']);
    Route::post('progress', [ProgressController::class, 'update']);
    Route::get('progress', [ProgressController::class, 'index']);
    
    // Exercise submissions
    Route::post('exercises/{exercise}/submit', [CodeEvaluationController::class, 'submit']);
    Route::post('exercises/{exercise}/evaluate', [CodeEvaluationController::class, 'evaluate']);
    Route::get('exercises/{exercise}/hints', [CodeEvaluationController::class, 'hints']);
    Route::get('submissions/history', [CodeEvaluationController::class, 'history']);
    Route::post('exercises', [ExerciseController::class, 'store']);
});
