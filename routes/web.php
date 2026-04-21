<?php

use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('schedules.index');
});

Route::resource('schedules', ScheduleController::class);
