<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\ReservasiController;
use App\Http\Controllers\WaktuAbsenController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/scan', function() {
        return view('features/scan');
    })->name('scan');

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/api/dashboard-data', 'getDashboardData');
        Route::get('/', 'index')->name('/');
    });
    Route::controller(WaktuAbsenController::class)->group(function () {
        Route::post('/api/pengaturan-waktu', 'simpan');
        Route::get('/api/pengaturan-waktu', 'getSettings');
    });
    
    Route::prefix('reservasi')->group(function () {
        Route::controller(ReservasiController::class)->group(function () {
            Route::get('/', 'index')->name('reservasi');
            Route::post('/', 'store')->name('reservasi.store');
            Route::get('/more-siswa', 'loadMore')->name('siswa.more');
            Route::delete('/{reservasi}', 'destroy')->name('reservasi.destroy');
        });
    });

    Route::prefix('absensi')->group(function () {
        Route::controller(AbsensiController::class)->group(function () {
            Route::get('/', 'index')->name('absensi');
            Route::post('/rekap', 'rekap');
        });
    });

    Route::middleware(['auth', 'check.admin'])->group(function () {
        Route::prefix('penilaian')->group(function () {
            Route::controller(PenilaianController::class)->group(function () {
                Route::get('/', 'index')->name('penilaian');
                Route::put('/{id}', 'update')->name('penilaian.update');
            });
        });

        Route::prefix('kelas')->group(function () {
            Route::controller(KelasController::class)->group(function () {
                Route::get('/', 'index')->name('kelas');
                Route::post('/save-changes', 'saveBatchChanges')->name('kelas.save-changes');
            });
        });

        Route::prefix('siswa')->group(function () {
            Route::controller(SiswaController::class)->group(function () {
                Route::get('/tambah', 'tambah')->name('siswa.create'); // Show Store Form
                Route::get('/{id}', 'edit')->name('siswa.edit'); // Show Edit Form
                Route::post('/', 'store')->name('siswa.store'); // Create
                Route::put('/{id}', 'update')->name('siswa.update'); // Update
            });
        });
    });

    Route::prefix('siswa')->group(function () {
        Route::controller(SiswaController::class)->group(function () {
            Route::get('/', 'index')->name('siswa'); // Read (List)
            Route::delete('/{id}', 'delete')->name('siswa.delete'); // Delete
        });
    });

    Route::controller(AuthController::class)->group(function () {
        Route::post('/refresh', 'refresh');
        Route::post('logout', 'logout')->name('logout');
    });
});
