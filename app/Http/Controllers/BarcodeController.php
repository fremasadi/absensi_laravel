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

        // Cek izin
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

        // Ambil jadwal shift aktif
        $jadwal = JadwalShift::where('id_user', $user->id)
            ->where('status', 1)
            ->with('shift')
            ->latest('created_at')
            ->first();

        if (!$jadwal || !isset($jadwal->id_shift)) {
            return view('barcode', ['barcode' => null, 'message' => 'Tidak ada jadwal shift aktif atau id_shift tidak ditemukan.']);
        }

        $now = Carbon::now();

        // Buat waktu start_time hari ini
        $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $jadwal->shift->start_time);

        // Buat batas akhir 30 menit setelah start_time
        $shiftEnd = $shiftStart->copy()->addMinutes(30);

        // Cek kondisi lebih spesifik
        if ($now->lt($shiftStart)) {
            return view('barcode', ['barcode' => null, 'message' => 'Belum waktunya absen.']);
        }

        if ($now->gt($shiftEnd)) {
            return view('barcode', ['barcode' => null, 'message' => 'Waktu absensi telah lewat.']);
        }

        // Kalau pas di rentang waktu, generate barcode
        $barcodeData = $user->id . '|' . $jadwal->id_shift . '|' . $now->format('Y-m-d H:i:s');
        $barcode = QrCode::size(200)->generate($barcodeData);

        return view('barcode', [
            'barcode' => $barcode,
            'message' => null
        ]);
    }
}
