<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MahasiswaController;

// Welcome Page (Redirect to login)
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/admin/mahasiswa/update-uid', [MahasiswaController::class, 'updateUid'])->name('admin.mahasiswa.updateUid');
// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        
        Route::post('/device-status/toggle', [AdminController::class, 'toggleDeviceStatus'])->name('device.toggle');
        
        // Mahasiswa CRUD
        Route::get('/mahasiswa/rfid-edit-mode', [AdminController::class, 'rfidEditModeStatus'])->name('mahasiswa.rfidEditMode');
        Route::get('/mahasiswa', [AdminController::class, 'mahasiswaIndex'])->name('mahasiswa.index');
        Route::get('/mahasiswa/create', [AdminController::class, 'mahasiswaCreate'])->name('mahasiswa.create');
        Route::post('/mahasiswa', [AdminController::class, 'mahasiswaStore'])->name('mahasiswa.store');
        Route::get('/mahasiswa/{uid_ktm}/edit', [AdminController::class, 'mahasiswaEdit'])->name('mahasiswa.edit');
        Route::put('/mahasiswa/{uid_ktm}', [AdminController::class, 'mahasiswaUpdate'])->name('mahasiswa.update');
        Route::delete('/mahasiswa/{uid_ktm}', [AdminController::class, 'mahasiswaDestroy'])->name('mahasiswa.destroy');

        // Dosen CRUD
        Route::get('/dosen', [AdminController::class, 'dosenIndex'])->name('dosen.index');
        Route::get('/dosen/create', [AdminController::class, 'dosenCreate'])->name('dosen.create');
        Route::post('/dosen', [AdminController::class, 'dosenStore'])->name('dosen.store');
        Route::get('/dosen/{id}/edit', [AdminController::class, 'dosenEdit'])->name('dosen.edit');
        Route::put('/dosen/{id}', [AdminController::class, 'dosenUpdate'])->name('dosen.update');
        Route::delete('/dosen/{id}', [AdminController::class, 'dosenDestroy'])->name('dosen.destroy');
    });

    // Dosen Routes
    Route::middleware(['role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
        Route::get('/', [DosenController::class, 'index'])->name('dashboard');
        Route::get('/export/pdf', [DosenController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [DosenController::class, 'exportExcel'])->name('export.excel');
        
        // Live Monitor (Moved from Admin)
        Route::get('/live', [DosenController::class, 'liveMonitor'])->name('live');
        Route::get('/live-data', [DosenController::class, 'liveData'])->name('live-data');
    });
});

