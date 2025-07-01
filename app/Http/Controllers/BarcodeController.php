<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\JadwalShift;
use App\Models\Shift;
use App\Models\PermintaanIzin;
use App\Models\Absensi; // Tambahkan import model Absensi
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
        $shiftName = $shift->name;

        // Buat waktu shift untuk hari ini
        $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $shift->start_time);
        $shiftEnd = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $shift->end_time);

        // Handle shift yang melewati tengah malam
        if ($shiftEnd->lt($shiftStart)) {
            $shiftEnd = $shiftEnd->addDay();
        }

        // CEK STATUS ABSENSI HARI INI
        $absensiHariIni = Absensi::where('id_user', $user->id)
            ->where('id_jadwal', $jadwal->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Tentukan jenis absensi (masuk/keluar) dan pesan
        $jenisAbsensi = 'masuk'; // default
        $titleBarcode = 'Barcode Absensi Masuk';
        $pesanAbsensi = "Scan barcode untuk absensi masuk";

        if ($absensiHariIni) {
            if ($absensiHariIni->jam_masuk && !$absensiHariIni->jam_keluar) {
                // Sudah absen masuk, belum absen keluar
                $jenisAbsensi = 'keluar';
                $titleBarcode = 'Barcode Absensi Keluar';
                $pesanAbsensi = "Scan barcode untuk absensi keluar";
            } elseif ($absensiHariIni->jam_masuk && $absensiHariIni->jam_keluar) {
                // Sudah absen masuk dan keluar
                return view('barcode', [
                    'barcode' => null,
                    'shift' => $shift,
                    'shiftName' => $shiftName,
                    'shiftStart' => $shiftStart,
                    'shiftEnd' => $shiftEnd,
                    'titleBarcode' => 'Absensi Selesai',
                    'message' => "Anda sudah melakukan absensi masuk dan keluar untuk hari ini."
                ]);
            }
        }

        // TAMBAHAN: Validasi apakah waktu sekarang dalam rentang shift
        $isWithinShiftTime = $this->isWithinShiftTime($now, $shiftStart, $shiftEnd);
        
        if (!$isWithinShiftTime) {
            return view('barcode', [
                'barcode' => null,
                'shift' => $shift,
                'shiftName' => $shiftName,
                'shiftStart' => $shiftStart,
                'shiftEnd' => $shiftEnd,
                'titleBarcode' => $titleBarcode,
                'message' => "Barcode absensi hanya tersedia pada jam shift {$shiftName} ({$shiftStart->format('H:i')} - {$shiftEnd->format('H:i')}). Waktu sekarang: {$now->format('H:i')}"
            ]);
        }

        // Generate barcode hanya jika dalam rentang waktu shift
        $barcodeData = $user->id . '|' . $shift->id . '|' . $jenisAbsensi . '|' . $now->format('Y-m-d H:i:s');
        $barcode = QrCode::size(200)->generate($barcodeData);

        return view('barcode', [
            'barcode' => $barcode,
            'shift' => $shift,
            'shiftName' => $shiftName,
            'shiftStart' => $shiftStart,
            'shiftEnd' => $shiftEnd,
            'titleBarcode' => $titleBarcode,
            'jenisAbsensi' => $jenisAbsensi,
            'message' => $pesanAbsensi . " - {$shiftName} ({$shiftStart->format('H:i')} - {$shiftEnd->format('H:i')})"
        ]);
    }

    /**
     * Cek apakah waktu sekarang berada dalam rentang shift
     */
    private function isWithinShiftTime($currentTime, $shiftStart, $shiftEnd)
    {
        // Tambahkan toleransi waktu (misalnya 15 menit sebelum shift dimulai)
        $toleranceMinutes = 15;
        $shiftStartWithTolerance = $shiftStart->copy()->subMinutes($toleranceMinutes);
        
        // Jika shift tidak melewati tengah malam
        if ($shiftEnd->gt($shiftStart)) {
            return $currentTime->between($shiftStartWithTolerance, $shiftEnd);
        }
        
        // Jika shift melewati tengah malam (contoh: 22:00 - 06:00)
        return $currentTime->gte($shiftStartWithTolerance) || $currentTime->lte($shiftEnd);
    }
}