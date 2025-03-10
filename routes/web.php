<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptBuilderController;
use App\Http\Controllers\PromptController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Маршруты аутентификации
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Маршруты регистрации
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Защищенные маршруты
Route::middleware(['auth'])->group(function () {
    Route::get('/', [PromptBuilderController::class, 'index'])->name('prompt.builder');
    /*Route::get('/', function () {
        return Inertia::render('PromptBuilder');
    })->name('prompt.builder');*/

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    Route::post('/prompts/{prompt}/increment-usage', [PromptController::class, 'incrementUsage'])
        ->name('prompts.increment-usage');

    // Добавляем новый маршрут для toggle-favorite
    Route::post('/prompts/{prompt}/toggle-favorite', [PromptController::class, 'toggleFavorite'])
        ->name('prompts.toggle-favorite');

    Route::post('/prompts/{prompt}/update-rating', [PromptController::class, 'updateRating'])
        ->name('prompts.update-rating');

    // Маршруты для промптов
    Route::post('/prompts', [PromptController::class, 'store'])->name('prompts.store');
    Route::post('/prompts/{prompt}/update-rating', [PromptController::class, 'updateRating'])->name('prompts.update-rating');
    Route::post('/prompts/{prompt}/toggle-favorite', [PromptController::class, 'toggleFavorite'])->name('prompts.toggle-favorite');
});

// Перенаправляем все остальные маршруты на Vue приложение
Route::get('{all}', function () {
    return Inertia::render('PromptBuilder');
})->where('all', '.*');
