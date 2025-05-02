<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureRoleIsUser;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PermintaanIzinController;
use App\Http\Controllers\RiwayatAbsensiController;
use App\Http\Controllers\GajiController;
use App\Http\Controllers\DashboardController;

Route::get('/gaji/{id}/download', [GajiController::class, 'downloadSlipGaji'])->name('gaji.downloadSlipGaji');
Route::get('/riwayat-absensi', [RiwayatAbsensiController::class, 'index'])->name('riwayat-absensi.index');
Route::post('/handle-scan', [AbsensiController::class, 'handleScan']);
Route::post('/upload-selfie', [AbsensiController::class, 'uploadSelfie']);
Route::get('/', function () {
    return redirect('/login');  // Mengarahkan langsung ke halaman login
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

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});

require __DIR__.'/auth.php';
