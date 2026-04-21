<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HealthDataController;

Route::get('/', [HealthDataController::class, 'index'])->name('health.index');
Route::post('/health-data', [HealthDataController::class, 'store'])->name('health.store');
Route::put('/health-data/{id}', [HealthDataController::class, 'update'])->name('health.update');
