<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Absensi\AbsensiController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\JadwalMengajarController;
use App\Http\Controllers\Admin\PeriodeSemesterController;

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
        // Dashboard - Semua jadwal per hari
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        
        // Grid view absensi (siswa x pertemuan)
        Route::get('/{jadwalId}/grid', [AbsensiController::class, 'grid'])->name('grid');
        
        // AJAX: Update single absensi
        Route::post('/update-single', [AbsensiController::class, 'updateSingle'])->name('updateSingle');
        
        // AJAX: Batch update semua siswa
        Route::post('/batch-update', [AbsensiController::class, 'batchUpdate'])->name('batchUpdate');
        
        // Form absensi (legacy - redirect to grid)
        Route::get('/{jadwalId}/create', [AbsensiController::class, 'create'])->name('create');
        
        // Simpan absensi batch (legacy)
        Route::post('/store', [AbsensiController::class, 'store'])->name('store');
        
        // Lihat detail absensi
        Route::get('/{jadwalId}/show', [AbsensiController::class, 'show'])->name('show');
        
        // Laporan rekap
        Route::get('/report', [AbsensiController::class, 'report'])->name('report');
    });

    // ===================================================================
    // ADMIN ROUTES - MATA PELAJARAN & JADWAL MENGAJAR & PERIODE SEMESTER
    // ===================================================================
    
    Route::prefix('admin')->name('admin.')->middleware('role:Operator Sekolah,Developer')->group(function () {
        
        // --- Periode Semester ---
        Route::prefix('periode-semester')->name('periode-semester.')->group(function () {
            Route::get('/', [PeriodeSemesterController::class, 'index'])->name('index');
            Route::get('/create', [PeriodeSemesterController::class, 'create'])->name('create');
            Route::post('/', [PeriodeSemesterController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [PeriodeSemesterController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PeriodeSemesterController::class, 'update'])->name('update');
            Route::delete('/{id}', [PeriodeSemesterController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/set-active', [PeriodeSemesterController::class, 'setActive'])->name('setActive');
            Route::post('/{id}/generate-pertemuan', [PeriodeSemesterController::class, 'generatePertemuan'])->name('generatePertemuan');
        });

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
