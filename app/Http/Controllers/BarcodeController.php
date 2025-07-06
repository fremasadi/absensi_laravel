<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\JadwalShift;
use App\Models\Shift;
use App\Models\PermintaanIzin;
use App\Models\Absensi;
use Carbon\Carbon;

class BarcodeController extends Controller
{
    public function showBarcodeMasuk()
    {
        $user = Auth::user();
        if (!$user) {
            return view('barcode-masuk', ['barcode' => null, 'message' => 'Pengguna tidak ditemukan.']);
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
            return view('barcode-masuk', [
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
            return view('barcode-masuk', [
                'barcode' => null,
                'message' => 'Tidak ada jadwal shift aktif untuk hari ini.'
            ]);
        }

        if (!$jadwal->shift) {
            return view('barcode-masuk', [
                'barcode' => null,
                'message' => 'Data shift tidak ditemukan.'
            ]);
        }

        // Cek apakah sudah absen masuk hari ini
        $absensiMasuk = Absensi::where('id_user', $user->id)
            ->where('id_jadwal', $jadwal->id)
            ->whereDate('tanggal_absen', $today)
            ->whereNotNull('waktu_masuk_time')
            ->first();

        if ($absensiMasuk) {
            return view('barcode-masuk', [
                'barcode' => null,
                'message' => 'Anda sudah melakukan absensi masuk untuk hari ini.'
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

        // TAMBAHAN: Validasi apakah waktu sekarang dalam rentang shift
        $isWithinShiftTime = $this->isWithinShiftTime($now, $shiftStart, $shiftEnd);
        
        if (!$isWithinShiftTime) {
            return view('barcode-masuk', [
                'barcode' => null,
                'shift' => $shift,
                'shiftName' => $shiftName,
                'shiftStart' => $shiftStart,
                'shiftEnd' => $shiftEnd,
                'message' => "Barcode absensi hanya tersedia pada jam shift {$shiftName} ({$shiftStart->format('H:i')} - {$shiftEnd->format('H:i')}). Waktu sekarang: {$now->format('H:i')}"
            ]);
        }

        // Generate barcode hanya jika dalam rentang waktu shift
        $barcodeData = $user->id . '|' . $shift->id . '|' . $now->format('Y-m-d H:i:s');
        $barcode = QrCode::size(200)->generate($barcodeData);

        return view('barcode-masuk', [
            'barcode' => $barcode,
            'shift' => $shift,
            'shiftName' => $shiftName,
            'shiftStart' => $shiftStart,
            'shiftEnd' => $shiftEnd,
            'message' => "Barcode absensi untuk {$shiftName} ({$shiftStart->format('H:i')} - {$shiftEnd->format('H:i')})"
        ]);
    }

    public function showBarcodekeluar()
    {
        $user = Auth::user();
        if (!$user) {
            return view('barcode-keluar', ['barcode' => null, 'message' => 'Pengguna tidak ditemukan.']);
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
            return view('barcode-keluar', [
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
            return view('barcode-keluar', [
                'barcode' => null,
                'message' => 'Tidak ada jadwal shift aktif untuk hari ini.'
            ]);
        }

        if (!$jadwal->shift) {
            return view('barcode-keluar', [
                'barcode' => null,
                'message' => 'Data shift tidak ditemukan.'
            ]);
        }

        // Cek apakah sudah absen masuk hari ini
        $absensiMasuk = Absensi::where('id_user', $user->id)
            ->where('id_jadwal', $jadwal->id)
            ->whereDate('tanggal_absen', $today)
            ->whereNotNull('waktu_masuk_time')
            ->first();

        if (!$absensiMasuk) {
            return view('barcode-keluar', [
                'barcode' => null,
                'message' => 'Anda harus melakukan absensi masuk terlebih dahulu sebelum dapat melakukan absensi keluar.'
            ]);
        }

        // Cek apakah sudah absen keluar hari ini
        if ($absensiMasuk->waktu_keluar_time) {
            return view('barcode-keluar', [
                'barcode' => null,
                'message' => 'Anda sudah melakukan absensi keluar untuk hari ini.'
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

        // PERUBAHAN: Validasi waktu absen keluar
        // Bisa keluar mulai dari waktu shift berakhir sampai 1 jam setelah shift berakhir
        $maximumExitTime = $shiftEnd->copy()->addHour();

        // Validasi apakah waktu sekarang sudah mencapai waktu shift berakhir
        if ($now->lt($shiftEnd)) {
            return view('barcode-keluar', [
                'barcode' => null,
                'shift' => $shift,
                'shiftName' => $shiftName,
                'shiftStart' => $shiftStart,
                'shiftEnd' => $shiftEnd,
                'maximumExitTime' => $maximumExitTime,
                'message' => "Barcode absensi keluar akan tersedia mulai pukul {$shiftEnd->format('H:i')} (saat shift {$shiftName} berakhir). Waktu sekarang: {$now->format('H:i')}"
            ]);
        }

        // Validasi apakah waktu sekarang sudah melewati batas maksimum untuk absen keluar
        if ($now->gt($maximumExitTime)) {
            return view('barcode-keluar', [
                'barcode' => null,
                'shift' => $shift,
                'shiftName' => $shiftName,
                'shiftStart' => $shiftStart,
                'shiftEnd' => $shiftEnd,
                'maximumExitTime' => $maximumExitTime,
                'message' => "Barcode absensi keluar sudah tidak tersedia. Waktu absensi keluar hanya tersedia dari pukul {$shiftEnd->format('H:i')} sampai {$maximumExitTime->format('H:i')}. Waktu sekarang: {$now->format('H:i')}"
            ]);
        }

        // Generate barcode untuk absen keluar
        $barcodeData = $user->id . '|' . $shift->id . '|' . $now->format('Y-m-d H:i:s') . '|keluar';
        $barcode = QrCode::size(200)->generate($barcodeData);

        return view('barcode-keluar', [
            'barcode' => $barcode,
            'shift' => $shift,
            'shiftName' => $shiftName,
            'shiftStart' => $shiftStart,
            'shiftEnd' => $shiftEnd,
            'maximumExitTime' => $maximumExitTime,
            'message' => "Barcode absensi keluar untuk {$shiftName}. Tersedia dari {$shiftEnd->format('H:i')} sampai {$maximumExitTime->format('H:i')}"
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