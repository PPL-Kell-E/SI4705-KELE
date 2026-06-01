<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JiraController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\HealthDataController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\TargetKesehatanController;
use App\Http\Controllers\HasilPemeriksaanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PengingatController;

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
    // Admin area
    Route::middleware('is_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
    });

    // Dashboard (user biasa)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // PKE-1: Pengelolaan Profil (PKE-22: Lihat, PKE-23: Ubah)
    Route::get('/profile',  [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');

    // PKE-18: Pengingat & Notifikasi
    Route::prefix('pengingat')->name('pengingat.')->group(function () {
        Route::get('/',              [PengingatController::class, 'index'])->name('index');
        Route::post('/',             [PengingatController::class, 'store'])->name('store');
        Route::patch('/{pengingat}/toggle', [PengingatController::class, 'toggle'])->name('toggle');
        Route::put('/{pengingat}',   [PengingatController::class, 'update'])->name('update');
    });
    Route::get('/notifikasi/check',         [PengingatController::class, 'checkNotifikasi'])->name('notifikasi.check');
    Route::patch('/notifikasi/{id}/read',   [PengingatController::class, 'markRead'])->name('notifikasi.read');
    Route::patch('/notifikasi/read-all',    [PengingatController::class, 'markAllRead'])->name('notifikasi.read-all');
    // PKE: Rekomendasi CRUD
    Route::get('/rekomendasi',                             [RecommendationController::class, 'index'])->name('rekomendasi.index');
    Route::post('/rekomendasi',                            [RecommendationController::class, 'store'])->name('rekomendasi.store');
    Route::put('/rekomendasi/{rekomendasi}',               [RecommendationController::class, 'update'])->name('rekomendasi.update');
    Route::delete('/rekomendasi/{rekomendasi}',            [RecommendationController::class, 'destroy'])->name('rekomendasi.destroy');
    // PKE-2: Data Kesehatan Dasar
    Route::get('/data-kesehatan',         [HealthDataController::class, 'index'])->name('data-kesehatan.index');
    Route::post('/data-kesehatan',        [HealthDataController::class, 'store'])->name('data-kesehatan.store');
    Route::get('/data-kesehatan/export',  [HealthDataController::class, 'export'])->name('data-kesehatan.export');
    Route::get('/katalog',             [KatalogController::class, 'index'])->name('katalog.index');
    Route::get('/katalog/{slug}',      [KatalogController::class, 'show'])->name('katalog.show');
    // PKE-9: Admin CRUD Katalog
    Route::middleware('is_admin')->group(function () {
        Route::post('/katalog',             [KatalogController::class, 'store'])->name('katalog.store');
        Route::put('/katalog/{katalog}',    [KatalogController::class, 'update'])->name('katalog.update');
        Route::delete('/katalog/{katalog}', [KatalogController::class, 'destroy'])->name('katalog.destroy');
    });
    // PKE-5: Perencanaan Jadwal Pemeriksaan
    Route::resource('/jadwal', JadwalController::class)->except(['show']);
    Route::patch('/jadwal/{jadwal}/selesai', [JadwalController::class, 'tandaiSelesai'])->name('jadwal.selesai');
    // PKE-12: Riwayat Kesehatan
    Route::get('/riwayat',                              [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::patch('/riwayat/{hasilPemeriksaan}/hide',    [RiwayatController::class, 'hide'])->name('riwayat.hide');
    // PKE-15: Insight & Pencapaian
    Route::get('/insight',            [InsightController::class, 'index'])->name('insight.index');
    Route::get('/insight/progress',   [InsightController::class, 'progress'])->name('insight.progress');
    // PKE-11: Pencatatan Hasil Pemeriksaan
    Route::get('/hasil-pemeriksaan',                       [HasilPemeriksaanController::class, 'index'])->name('hasil-pemeriksaan.index');
    Route::post('/hasil-pemeriksaan',                      [HasilPemeriksaanController::class, 'store'])->name('hasil-pemeriksaan.store');
    Route::put('/hasil-pemeriksaan/{hasilPemeriksaan}',    [HasilPemeriksaanController::class, 'update'])->name('hasil-pemeriksaan.update');
    Route::delete('/hasil-pemeriksaan/{hasilPemeriksaan}', [HasilPemeriksaanController::class, 'destroy'])->name('hasil-pemeriksaan.destroy');
    // PKE-16: Target Kesehatan
    Route::resource('/target-kesehatan', TargetKesehatanController::class, [
        'parameters' => ['target-kesehatan' => 'target'],
    ])->except(['show']);

    // Jira Integration Routes
    Route::prefix('jira')->group(function () {
        Route::get('/test',        [JiraController::class, 'test'])->name('jira.test');
        Route::get('/backlog',     [JiraController::class, 'backlog'])->name('jira.backlog');
        Route::get('/issue/{key}', [JiraController::class, 'getIssue'])->name('jira.issue');
        Route::post('/issue',      [JiraController::class, 'createIssue'])->name('jira.create');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
