<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\JadwalShift;
use App\Models\Shift;
use App\Models\PermintaanIzin; // Tambahkan ini
use Carbon\Carbon;

class BarcodeController extends Controller
{
    public function showBarcode()
    {
        $user = Auth::user();
        if (!$user) {
            return view('barcode', ['barcode' => null, 'message' => 'Pengguna tidak ditemukan.']);
        }

        // Cek apakah ada permintaan izin yang disetujui (status = true) untuk hari ini
        $today = Carbon::today();
        $permintaanIzin = PermintaanIzin::where('user_id', $user->id)
            ->where('status', true) // Status true (disetujui)
            ->whereDate('tanggal_mulai', '<=', $today) // Tanggal mulai <= hari ini
            ->whereDate('tanggal_selesai', '>=', $today) // Tanggal selesai >= hari ini
            ->first();

        // Jika ada permintaan izin yang disetujui, jangan tampilkan barcode
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

        if (!$jadwal) {
            return view('barcode', ['barcode' => null, 'message' => 'Tidak ada jadwal shift aktif.']);
        }

        // Pastikan id_shift ada
        if (!isset($jadwal->id_shift)) {
            return view('barcode', ['barcode' => null, 'message' => 'id_shift tidak ditemukan.']);
        }

        // Validasi waktu saat ini sesuai shift
        $now = Carbon::now();
        $shiftStart = Carbon::parse($jadwal->shift->start_time);
        $shiftEnd = Carbon::parse($jadwal->shift->end_time);

        if (!$now->between($shiftStart, $shiftEnd)) {
            return view('barcode', ['barcode' => null, 'message' => 'Di luar jam shift.']);
        }

        // Generate QR code dengan id_shift
        $barcodeData = $user->id . '|' . $jadwal->id_shift . '|' . $now->format('Y-m-d H:i:s');
        $barcode = QrCode::size(200)->generate($barcodeData);

        return view('barcode', [
            'barcode' => $barcode,
            'message' => null
        ]);
    }
}