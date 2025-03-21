<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureRoleIsUser;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PermintaanIzinController;
use App\Http\Controllers\RiwayatAbsensiController;

Route::get('/riwayat-absensi', [RiwayatAbsensiController::class, 'index'])->name('riwayat-absensi.index');
Route::post('/handle-scan', [AbsensiController::class, 'handleScan']);
Route::post('/upload-selfie', [AbsensiController::class, 'uploadSelfie']);
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', EnsureRoleIsUser::class, 'verified'])->name('dashboard');

// Grup rute dengan middleware auth dan role.user
Route::middleware(['auth', EnsureRoleIsUser::class])->group(function () {
    Route::resource('permintaan-izin', PermintaanIzinController::class);
    Route::patch('permintaan-izin/{permintaanIzin}/update-status', [PermintaanIzinController::class, 'updateStatus'])->name('permintaan-izin.update-status');
    Route::get('/barcode', [BarcodeController::class, 'showBarcode']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
