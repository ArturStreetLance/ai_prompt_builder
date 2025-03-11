<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PromptTemplateController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\PromptSubmissionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Публичные маршруты
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Защищенные маршруты
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('prompt-templates', PromptTemplateController::class);
    Route::post('prompt-templates/{promptTemplate}/rate', [RatingController::class, 'rate']);
    Route::post('prompt-templates/compile', [PromptTemplateController::class, 'compile']);
    Route::get('prompt-templates/saved', [PromptTemplateController::class, 'saved']);
    Route::post('/prompts/submit', [PromptSubmissionController::class, 'submit']);
    Route::get('/prompts/popular', [PromptSubmissionController::class, 'getPopularPrompts']);
    Route::get('/prompts/frequent', [PromptSubmissionController::class, 'getUserFrequentPrompts']);
});
