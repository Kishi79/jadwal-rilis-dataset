<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicJadwalController;
use App\Http\Controllers\Admin\AdminJadwalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Homepage redirect to jadwal
Route::get('/', function () {
    return redirect()->route('public.jadwal');
});

// Public jadwal rilis routes
Route::prefix('jadwal')->name('public.')->group(function () {
    Route::get('/', [PublicJadwalController::class, 'index'])->name('jadwal');
    Route::get('/data', [PublicJadwalController::class, 'getData'])->name('jadwal.data');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard redirect to admin jadwal
    Route::get('/dashboard', function () {
        return redirect()->route('admin.jadwal.index');
    })->name('dashboard');

    // Admin jadwal management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('jadwal', AdminJadwalController::class);
        Route::get('jadwal-dataset-details', [AdminJadwalController::class, 'getDatasetDetails'])
            ->name('jadwal.dataset.details');
    });

    // Profile management (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Include Breeze authentication routes
require __DIR__.'/auth.php';
