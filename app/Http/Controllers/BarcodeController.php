<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\JadwalShift;
use App\Models\Shift;
use App\Models\PermintaanIzin;
use Carbon\Carbon;

class BarcodeController extends Controller
{
    public function showBarcode()
{
    $user = Auth::user();
    if (!$user) {
        return view('barcode', ['barcode' => null, 'message' => 'Pengguna tidak ditemukan.']);
    }

    $today = Carbon::today();
    $now = Carbon::now();

    // Cek izin yang disetujui
    $permintaanIzin = PermintaanIzin::where('user_id', $user->id)
        ->where('status', true)
        ->whereDate('tanggal_mulai', '<=', $today)
        ->whereDate('tanggal_selesai', '>=', $today)
        ->first();

    if ($permintaanIzin) {
        return view('barcode', [
            'barcode' => null,
            'message' => 'Anda memiliki izin yang disetujui untuk hari ini.'
        ]);
    }

    // Ambil jadwal shift aktif untuk user
    $jadwal = JadwalShift::where('id_user', $user->id)
        ->where('status', 1)
        ->with('shift')
        ->latest('created_at')
        ->first();

    if (!$jadwal) {
        return view('barcode', [
            'barcode' => null,
            'message' => 'Tidak ada jadwal shift aktif untuk hari ini.'
        ]);
    }

    if (!$jadwal->shift) {
        return view('barcode', [
            'barcode' => null,
            'message' => 'Data shift tidak ditemukan.'
        ]);
    }

    // Data shift
    $shift = $jadwal->shift;

    // Buat waktu shift untuk hari ini
    $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $shift->start_time);
    $shiftEnd = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $shift->end_time);

    // Handle shift yang melewati tengah malam
    if ($shiftEnd->lt($shiftStart)) {
        $shiftEnd = $shiftEnd->addDay();
    }

    // Generate barcode jika dalam rentang waktu shift
    $barcodeData = $user->id . '|' . $shift->id . '|' . $now->format('Y-m-d H:i:s');
    $barcode = QrCode::size(200)->generate($barcodeData);

    return view('barcode', [
        'barcode' => $barcode,
        'message' => "Barcode absensi untuk {$shift->name} ({$shiftStart->format('H:i')} - {$shiftEnd->format('H:i')})"
    ]);
}
}
