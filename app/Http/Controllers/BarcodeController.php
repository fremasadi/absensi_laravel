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

        // Buat shiftStart dan shiftEnd menggunakan tanggal hari ini + waktu shift
        $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $jadwal->shift->start_time);
        $shiftEnd = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $jadwal->shift->end_time);

        // Handle jika shift lewat tengah malam (contoh: 22:00 - 06:00)
        if ($shiftEnd->lessThan($shiftStart)) {
            $shiftEnd->addDay(); // Shift selesai keesokan harinya
        }

        if (!$now->between($shiftStart, $shiftEnd)) {
            return view('barcode', ['barcode' => null, 'message' => 'Di luar jam shift.']);
        }

        // Generate QR code
        $barcodeData = $user->id . '|' . $jadwal->id_shift . '|' . $now->format('Y-m-d H:i:s');
        $barcode = QrCode::size(200)->generate($barcodeData);

        return view('barcode', [
            'barcode' => $barcode,
            'message' => null
        ]);
    }
}
