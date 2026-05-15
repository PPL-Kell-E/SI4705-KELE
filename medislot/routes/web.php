<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JiraController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\HealthDataController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\InsightController;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // PKE-1: Pengelolaan Profil (PKE-22: Lihat, PKE-23: Ubah)
    Route::get('/profile',  [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');

    // Stub routes for sidebar (to be implemented per sprint)
    Route::get('/pengingat',          fn() => view('coming-soon', ['title' => 'Pengingat']))->name('pengingat.index');
    Route::get('/rekomendasi',        [RecommendationController::class, 'index'])->name('rekomendasi.index');
    // PKE-2: Data Kesehatan Dasar
    Route::get('/data-kesehatan',         [HealthDataController::class, 'index'])->name('data-kesehatan.index');
    Route::post('/data-kesehatan',        [HealthDataController::class, 'store'])->name('data-kesehatan.store');
    Route::get('/data-kesehatan/export',  [HealthDataController::class, 'export'])->name('data-kesehatan.export');
    Route::get('/katalog',             [KatalogController::class, 'index'])->name('katalog.index');
    Route::get('/katalog/{key}',       [KatalogController::class, 'show'])->name('katalog.show');
    // PKE-5: Perencanaan Jadwal Pemeriksaan
    Route::resource('/jadwal', JadwalController::class)->except(['show']);
    Route::get('/riwayat',            fn() => view('coming-soon', ['title' => 'Riwayat']))->name('riwayat.index');
    // PKE-15: Insight & Pencapaian
    Route::get('/insight',            [InsightController::class, 'index'])->name('insight.index');
    Route::get('/insight/progress',   fn() => view('coming-soon', ['title' => 'Progress']))->name('insight.progress');
    Route::get('/hasil-pemeriksaan',  fn() => view('coming-soon', ['title' => 'Hasil Pemeriksaan']))->name('hasil-pemeriksaan.index');

    // Jira Integration Routes
    Route::prefix('jira')->group(function () {
        Route::get('/test',        [JiraController::class, 'test'])->name('jira.test');
        Route::get('/backlog',     [JiraController::class, 'backlog'])->name('jira.backlog');
        Route::get('/issue/{key}', [JiraController::class, 'getIssue'])->name('jira.issue');
        Route::post('/issue',      [JiraController::class, 'createIssue'])->name('jira.create');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
