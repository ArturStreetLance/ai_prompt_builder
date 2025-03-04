<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', function () {
    return Inertia::render('PromptBuilder');
})->name('prompt.builder');

// Перенаправляем все остальные маршруты на Vue приложение
Route::get('{all}', function () {
    return Inertia::render('PromptBuilder');
})->where('all', '.*');
