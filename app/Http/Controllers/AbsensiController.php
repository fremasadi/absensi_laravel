<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\JadwalShift;
use App\Models\Gaji;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\Storage;


class AbsensiController extends Controller
{
    public function handleScan(Request $request)
    {
        $barcodeData = $request->input('barcode');
        $selfieImage = $request->file('selfie');

        // Validasi selfie
        if (!$selfieImage) {
            return response()->json(['message' => 'Selfie diperlukan untuk absensi.'], 400);
        }

        // Parse data barcode
        [$userId, $shiftId, $scanTime] = explode('|', $barcodeData);

        // Validasi data barcode
        if (!is_numeric($userId) || !is_numeric($shiftId) || !strtotime($scanTime)) {
            return response()->json(['message' => 'Data barcode tidak valid.'], 400);
        }

        $jadwalShift = JadwalShift::where('id_user', $userId)
            ->where('id_shift', $shiftId)
            ->where('status', 1)
            ->first();

        if (!$jadwalShift) {
            return response()->json(['message' => 'Jadwal shift tidak ditemukan.'], 400);
        }

        $shift = Shift::find($shiftId);
        if (!$shift) {
            return response()->json(['message' => 'Data shift tidak valid.'], 400);
        }

        // Set timezone untuk Indonesia
        $now = Carbon::now('Asia/Jakarta');
        $today = Carbon::today('Asia/Jakarta');
        
        // Parsing waktu shift dengan tanggal hari ini
        $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $shift->start_time, 'Asia/Jakarta');
        $shiftEnd = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $shift->end_time, 'Asia/Jakarta');

        // Jika shift end lebih kecil dari start (shift malam), tambahkan 1 hari
        if ($shiftEnd->lt($shiftStart)) {
            $shiftEnd->addDay();
        }

        // Batas waktu untuk absen masuk (2 jam setelah shift dimulai)
        $lateThreshold = $shiftStart->copy()->addHours(2);
        
        // Batas waktu untuk absen keluar (1 jam setelah shift selesai)
        $checkoutDeadline = $shiftEnd->copy()->addHour();

        // Cari data absensi hari ini
        $absensi = Absensi::where('id_user', $userId)
            ->where('tanggal_absen', $today->format('Y-m-d'))
            ->first();

        if ($absensi) {
            // Jika sudah ada absensi hari ini (untuk absen keluar)
            if ($absensi->waktu_keluar_time) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absensi masuk dan keluar hari ini.'
                ], 400);
            }

            // Validasi waktu absen keluar
            if ($now->lt($shiftEnd)) {
                return response()->json([
                    'message' => 'Belum waktunya absen keluar. Shift berakhir pada: ' . $shiftEnd->format('H:i')
                ], 400);
            }

            if ($now->gt($checkoutDeadline)) {
                return response()->json([
                    'message' => 'Waktu absen keluar sudah terlewat. Batas maksimal: ' . 
                    $checkoutDeadline->format('H:i')
                ], 400);
            }

            // Proses menyimpan file selfie keluar
            $selfieFileName = 'selfie_keluar_' . $userId . '_' . $now->format('Ymd_His') . '.' . $selfieImage->getClientOriginalExtension();
            $selfiePath = $selfieImage->storeAs('selfies', $selfieFileName);

            // Hitung durasi hadir
            $waktuMasukTime = Carbon::createFromFormat('H:i:s', $absensi->waktu_masuk_time, 'Asia/Jakarta');
            $waktuMasukFull = Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $absensi->waktu_masuk_time, 'Asia/Jakarta');
            $durasiHadir = $waktuMasukFull->diffInMinutes($now);

            // Update waktu keluar dan durasi
            $absensi->update([
                'waktu_keluar_time' => $now->format('H:i:s'),
                'durasi_hadir' => $durasiHadir,
                'selfiekeluar' => $selfiePath,
                'updated_at' => $now
            ]);

            try {
                $user = User::find($userId);
                if ($user) {
                    Gaji::generateSalary($user);
                } else {
                    \Log::error('User tidak ditemukan dengan ID: ' . $userId);
                }
            } catch (\Exception $e) {
                \Log::error('Gagal generate salary: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Absensi keluar berhasil dicatat.',
                'waktu_keluar' => $now->format('H:i:s'),
                'durasi_hadir' => $this->formatDurasi($durasiHadir),
                'selfie_path' => $selfiePath
            ]);

        } else {
            // Proses absen masuk
            
            // Validasi waktu absen masuk (maksimal 2 jam setelah shift dimulai)
            if ($now->gt($lateThreshold)) {
                return response()->json([
                    'message' => 'Waktu absen masuk sudah terlewat. Batas maksimal: ' . 
                    $lateThreshold->format('H:i')
                ], 400);
            }

            // Tentukan status kehadiran berdasarkan waktu
            $statusKehadiran = 'hadir';
            $keterangan = 'hadir';
            
            // Jika absen setelah waktu shift dimulai, maka terlambat
            if ($now->gt($shiftStart)) {
                $minutesLate = $shiftStart->diffInMinutes($now);
                if ($minutesLate > 0) { // Toleransi 0 menit
                    $statusKehadiran = 'terlambat';
                    $keterangan = 'terlambat ' . $minutesLate . ' menit';
                }
            }

            // Proses menyimpan file selfie masuk
            $selfieFileName = 'selfie_masuk_' . $userId . '_' . $now->format('Ymd_His') . '.' . $selfieImage->getClientOriginalExtension();
            $selfiePath = $selfieImage->storeAs('selfies', $selfieFileName);

            // Buat absensi baru untuk waktu masuk
            $absensi = Absensi::create([
                'id_user' => $userId,
                'id_jadwal' => $jadwalShift->id,
                'tanggal_absen' => $today->format('Y-m-d'),
                'waktu_masuk_time' => $now->format('H:i:s'),
                'durasi_hadir' => 0,
                'status_kehadiran' => $statusKehadiran,
                'keterangan' => $keterangan,
                'selfiemasuk' => $selfiePath,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $responseMessage = $statusKehadiran === 'terlambat' 
                ? 'Absensi masuk berhasil dicatat (TERLAMBAT).' 
                : 'Absensi masuk berhasil dicatat.';

            return response()->json([
                'message' => $responseMessage,
                'waktu_masuk' => $now->format('H:i:s'),
                'waktu_shift' => $shiftStart->format('H:i:s'),
                'status' => $statusKehadiran,
                'keterangan' => $keterangan,
                'selfie_path' => $selfiePath,
                'debug' => [
                    'now' => $now->format('Y-m-d H:i:s'),
                    'shift_start' => $shiftStart->format('Y-m-d H:i:s'),
                    'is_late' => $now->gt($shiftStart),
                    'minutes_diff' => $now->gt($shiftStart) ? $shiftStart->diffInMinutes($now) : 0
                ]
            ]);
        }
    }

    // Helper function untuk memformat durasi
    private function formatDurasi($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d jam %02d menit', $hours, $remainingMinutes);
    }
}