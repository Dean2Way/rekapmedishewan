<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RekamMedisController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\ParamedisController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\JenisHewanController;
use App\Http\Controllers\DiagnosaController;
use App\Http\Controllers\AnamnesaController;
use App\Http\Controllers\SurveilansController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\PenandatanganController;

Route::redirect('/', '/login');
// Route untuk Halaman Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Route untuk Halaman yang Membutuhkan Login (Auth Umum)
Route::middleware('auth','nocache')->group(function () {
    
    // DIAKSES ADMIN & OPERATOR
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/surveilans', [SurveilansController::class, 'index'])->name('surveilans.index');

    // Modul transaksi hanya untuk operator dan admin
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/rekam-medis/search', [RekamMedisController::class, 'search'])->name('rekam-medis.search');
        Route::resource('rekam-medis', RekamMedisController::class);
    });

    // Akses khusus admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('user', UserController::class)
            ->parameters(['user' => 'id_user'])
            ->except(['show', 'create', 'edit']);

        Route::resource('dokter', DokterController::class)
            ->except(['show', 'create', 'edit']);

        Route::resource('paramedis', ParamedisController::class)
            ->except(['show', 'create', 'edit']);

        Route::resource('pelayanan', PelayananController::class)
            ->parameters(['pelayanan' => 'id_pelayanan'])
            ->except(['show', 'create', 'edit']);

        Route::resource('jenis-hewan', JenisHewanController::class)
            ->parameters(['jenis-hewan' => 'id_jenis'])
            ->names('jenis_hewan')
            ->except(['show', 'create', 'edit']);

        Route::resource('diagnosa', DiagnosaController::class)
            ->except(['show', 'create', 'edit']);

        Route::resource('anamnesa', AnamnesaController::class)
            ->except(['show', 'create', 'edit']);

        Route::resource('obat', ObatController::class)
            ->except(['show', 'create', 'edit']);

        Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('admin.api-tokens.index');
        Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store');
        Route::delete('/api-tokens/{id}', [ApiTokenController::class, 'destroy'])->name('admin.api-tokens.destroy');
    });

    // Fitur yang dapat diakses admin dan kepala dinas
    Route::middleware('role:admin,kepala_dinas')->group(function () {
        Route::resource('penandatangan', PenandatanganController::class)
            ->except(['show', 'create', 'edit']);

        Route::get('/rekap-laporan', [RekamMedisController::class, 'rekapLaporan'])->name('rekap-laporan.index');
        Route::get('/rekap-laporan/rekapitulasi', [RekamMedisController::class, 'rekapitulasi'])->name('rekap-laporan.rekapitulasi');
        Route::get('/rekap-laporan/export', [RekamMedisController::class, 'exportRekapLaporan'])->name('rekap-laporan.export');
        Route::get('/rekap-laporan/export-view', [RekamMedisController::class, 'exportRekapLaporanView'])->name('rekap-laporan.export-view');
        Route::get('/rekap-laporan/pdf', [RekamMedisController::class, 'cetakRekapLaporan'])->name('rekap-laporan.pdf');
        Route::get('/rekap-laporan/pdf2', [RekamMedisController::class, 'cetakRekapLaporan2'])->name('rekap-laporan.pdf2');
    });
});