<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('quizzes.index')
        : view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::resource('quizzes', QuizController::class);
    Route::get('quizzes/{quiz}/stats', [QuizController::class, 'stats'])->name('quizzes.stats');
});

Route::get('quiz/{quiz}/attempt', [AttemptController::class, 'show'])->name('quiz.attempt');
Route::post('quiz/{quiz}/attempt', [AttemptController::class, 'submit'])->name('quiz.attempt.submit');

Route::get('attempts/{attempt}/result', [AttemptController::class, 'result'])->name('attempts.result');
