<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\RecommendationController;

Route::get('/', [ExaminationController::class, 'index'])->name('examinations.index');
Route::get('/examinations/{id}', [ExaminationController::class, 'show'])->name('examinations.show');

// Admin Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [ExaminationController::class, 'dashboard'])->name('examinations.dashboard');

    // Examination Routes
    Route::resource('examinations', ExaminationController::class)->except(['index', 'show']);

    // Schedule Routes
    Route::resource('schedules', ScheduleController::class);

    // Recommendation Routes
    Route::resource('recommendations', RecommendationController::class);
});
