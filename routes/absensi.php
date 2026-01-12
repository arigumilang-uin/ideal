<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Absensi\AbsensiController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\JadwalMengajarController;

/*
|--------------------------------------------------------------------------
| Absensi Routes
|--------------------------------------------------------------------------
|
| Routes for attendance (absensi), mata pelajaran, and jadwal mengajar.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {

    // ===================================================================
    // ABSENSI ROUTES (For Guru/Wali Kelas to take attendance)
    // ===================================================================
    
    Route::prefix('absensi')->name('absensi.')->group(function () {
        // Dashboard - Jadwal hari ini
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        
        // Form absensi untuk jadwal tertentu
        Route::get('/{jadwalId}/create', [AbsensiController::class, 'create'])->name('create');
        
        // Simpan absensi batch
        Route::post('/store', [AbsensiController::class, 'store'])->name('store');
        
        // Lihat detail absensi
        Route::get('/{jadwalId}/show', [AbsensiController::class, 'show'])->name('show');
        
        // Laporan rekap
        Route::get('/report', [AbsensiController::class, 'report'])->name('report');
    });

    // ===================================================================
    // ADMIN ROUTES - MATA PELAJARAN & JADWAL MENGAJAR
    // ===================================================================
    
    Route::prefix('admin')->name('admin.')->middleware('role:Operator Sekolah,Developer')->group(function () {
        
        // --- Mata Pelajaran ---
        Route::prefix('mata-pelajaran')->name('mata-pelajaran.')->group(function () {
            Route::get('/', [MataPelajaranController::class, 'index'])->name('index');
            Route::get('/create', [MataPelajaranController::class, 'create'])->name('create');
            Route::post('/', [MataPelajaranController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [MataPelajaranController::class, 'edit'])->name('edit');
            Route::put('/{id}', [MataPelajaranController::class, 'update'])->name('update');
            Route::delete('/{id}', [MataPelajaranController::class, 'destroy'])->name('destroy');
        });

        // --- Jadwal Mengajar ---
        Route::prefix('jadwal-mengajar')->name('jadwal-mengajar.')->group(function () {
            Route::get('/', [JadwalMengajarController::class, 'index'])->name('index');
            Route::get('/create', [JadwalMengajarController::class, 'create'])->name('create');
            Route::post('/', [JadwalMengajarController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [JadwalMengajarController::class, 'edit'])->name('edit');
            Route::put('/{id}', [JadwalMengajarController::class, 'update'])->name('update');
            Route::delete('/{id}', [JadwalMengajarController::class, 'destroy'])->name('destroy');
        });
    });
});
