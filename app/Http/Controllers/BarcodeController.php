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
            return view('barcode', [
                'barcode' => null,
                'message' => 'Pengguna tidak ditemukan.',
                'debug' => null
            ]);
        }

        $today = Carbon::today();
        $now = Carbon::now();

        // Debug info
        $debugInfo = [
            'user_id' => $user->id,
            'current_time' => $now->format('Y-m-d H:i:s'),
            'today' => $today->format('Y-m-d')
        ];

        // Cek izin yang disetujui
        $permintaanIzin = PermintaanIzin::where('user_id', $user->id)
            ->where('status', true)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->first();

        if ($permintaanIzin) {
            return view('barcode', [
                'barcode' => null,
                'message' => 'Anda memiliki izin yang disetujui untuk hari ini.',
                'debug' => $debugInfo
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
                'message' => 'Tidak ada jadwal shift aktif untuk hari ini.',
                'debug' => array_merge($debugInfo, ['jadwal_found' => false])
            ]);
        }

        if (!$jadwal->shift) {
            return view('barcode', [
                'barcode' => null,
                'message' => 'Data shift tidak ditemukan.',
                'debug' => array_merge($debugInfo, [
                    'jadwal_id' => $jadwal->id,
                    'shift_id' => $jadwal->id_shift,
                    'shift_data' => null
                ])
            ]);
        }

        // Data shift
        $shift = $jadwal->shift;
        $debugInfo = array_merge($debugInfo, [
            'jadwal_id' => $jadwal->id,
            'shift_id' => $shift->id,
            'shift_name' => $shift->name,
            'shift_start' => $shift->start_time,
            'shift_end' => $shift->end_time
        ]);

        // Buat waktu shift untuk hari ini
        $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $shift->start_time);
        $shiftEnd = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $shift->end_time);

        // Handle shift yang melewati tengah malam (misal: 18:00 - 23:00, atau 22:00 - 06:00)
        if ($shiftEnd->lt($shiftStart)) {
            // Shift melewati tengah malam, shift_end adalah hari berikutnya
            $shiftEnd = $shiftEnd->addDay();
        }

        $debugInfo = array_merge($debugInfo, [
            'calculated_shift_start' => $shiftStart->format('Y-m-d H:i:s'),
            'calculated_shift_end' => $shiftEnd->format('Y-m-d H:i:s')
        ]);

        // Tentukan window absensi yang fleksibel
        // Opsi 1: 30 menit sebelum shift sampai 2 jam setelah shift dimulai
        $absensiStart = $shiftStart->copy()->subMinutes(30);
        $absensiEnd = $shiftStart->copy()->addHours(2);

        // Opsi 2: Untuk shift malam yang panjang, bisa absen sepanjang shift
        // Uncomment baris di bawah jika ingin menggunakan opsi ini
        // $absensiEnd = $shiftEnd->copy()->subMinutes(30); // 30 menit sebelum shift berakhir

        $debugInfo = array_merge($debugInfo, [
            'absensi_start' => $absensiStart->format('Y-m-d H:i:s'),
            'absensi_end' => $absensiEnd->format('Y-m-d H:i:s'),
            'can_absen_now' => $now->between($absensiStart, $absensiEnd)
        ]);

        // Cek apakah dalam rentang waktu absensi
        if ($now->lt($absensiStart)) {
            $timeLeft = $now->diffInMinutes($absensiStart);
            return view('barcode', [
                'barcode' => null,
                'message' => "Belum waktunya absen. Absensi dimulai {$timeLeft} menit lagi pada {$absensiStart->format('H:i')}.",
                'debug' => $debugInfo
            ]);
        }

        if ($now->gt($absensiEnd)) {
            return view('barcode', [
                'barcode' => null,
                'message' => "Waktu absensi telah lewat. Absensi berakhir pada {$absensiEnd->format('H:i')}.",
                'debug' => $debugInfo
            ]);
        }

        // Generate barcode jika dalam rentang waktu yang diizinkan
        $barcodeData = $user->id . '|' . $shift->id . '|' . $now->format('Y-m-d H:i:s');
        $barcode = QrCode::size(200)->generate($barcodeData);

        return view('barcode', [
            'barcode' => $barcode,
            'message' => "Barcode absensi untuk {$shift->name} ({$shiftStart->format('H:i')} - {$shiftEnd->format('H:i')})",
            'debug' => array_merge($debugInfo, [
                'barcode_data' => $barcodeData,
                'generated_at' => $now->format('Y-m-d H:i:s')
            ])
        ]);
    }
}
