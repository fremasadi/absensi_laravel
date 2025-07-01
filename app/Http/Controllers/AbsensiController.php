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

        $now = Carbon::now();
        $shiftStart = Carbon::parse($shift->start_time);
        $shiftEnd = Carbon::parse($shift->end_time);

        // Batas waktu absen masuk: 2 jam setelah shift dimulai
        $batasAbsenMasuk = $shiftStart->copy()->addHours(2);
        
        // Batas waktu absen keluar: 2 jam setelah shift berakhir
        $batasAbsenKeluar = $shiftEnd->copy()->addHours(2);

        // Cari data absensi hari ini
        $absensi = Absensi::where('id_user', $userId)
            ->where('tanggal_absen', Carbon::today())
            ->first();

        if ($absensi) {
            // Jika sudah ada absensi hari ini (untuk absen keluar)
            if ($absensi->waktu_keluar_time) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absensi masuk dan keluar hari ini.'
                ], 400);
            }

            // Validasi waktu untuk absen keluar
            if ($now->gt($batasAbsenKeluar)) {
                return response()->json([
                    'message' => 'Waktu absen keluar sudah berakhir. Batas waktu absen keluar: ' . 
                    $batasAbsenKeluar->format('H:i')
                ], 400);
            }

            // Validasi minimal waktu untuk absen keluar (tidak bisa keluar sebelum shift berakhir)
            if ($now->lt($shiftEnd)) {
                return response()->json([
                    'message' => 'Belum waktunya absen keluar. Shift berakhir pada: ' . 
                    $shiftEnd->format('H:i')
                ], 400);
            }

            // Proses absen keluar
            $selfieFileName = 'selfie_keluar_' . $userId . '_' . $now->format('Ymd_His') . '.' . $selfieImage->getClientOriginalExtension();
            $selfiePath = $selfieImage->storeAs('selfies', $selfieFileName);

            // Hitung durasi hadir
            $waktuMasuk = Carbon::parse($absensi->waktu_masuk_time);
            $waktuKeluar = Carbon::now();
            $durasiHadir = $waktuMasuk->diffInMinutes($waktuKeluar);

            // Update waktu keluar dan durasi
            $absensi->update([
                'waktu_keluar_time' => $waktuKeluar->toTimeString(),
                'durasi_hadir' => $durasiHadir,
                'selfiekeluar' => $selfiePath,
                'updated_at' => $now
            ]);

            try {
                // Ambil instance User berdasarkan ID
                $user = User::find($userId);
                if ($user) {
                    Gaji::generateSalary($user);
                } else {
                    \Log::error('User tidak ditemukan dengan ID: ' . $userId);
                }
            } catch (\Exception $e) {
                // Log error, tetapi tidak menghentikan proses absensi
                \Log::error('Gagal generate salary: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Absensi keluar berhasil dicatat.',
                'waktu_keluar' => $waktuKeluar->format('H:i:s'),
                'durasi_hadir' => $this->formatDurasi($durasiHadir),
                'selfie_path' => $selfiePath
            ]);
        } else {
            // Proses absen masuk
            // Validasi waktu untuk absen masuk
            if ($now->gt($batasAbsenMasuk)) {
                return response()->json([
                    'message' => 'Waktu absen masuk sudah berakhir. Batas waktu absen masuk: ' . 
                    $batasAbsenMasuk->format('H:i') . ' (2 jam setelah shift dimulai pada ' . 
                    $shiftStart->format('H:i') . ')'
                ], 400);
            }

            // Validasi tidak bisa absen terlalu awal (misal 30 menit sebelum shift)
            $toleransiAwal = 30; // menit
            $waktuMinimalAbsen = $shiftStart->copy()->subMinutes($toleransiAwal);
            
            if ($now->lt($waktuMinimalAbsen)) {
                return response()->json([
                    'message' => 'Belum waktunya absen masuk. Anda bisa absen mulai ' . 
                    $waktuMinimalAbsen->format('H:i') . ' (30 menit sebelum shift dimulai)'
                ], 400);
            }

            // Proses menyimpan file selfie
            $selfieFileName = 'selfie_masuk_' . $userId . '_' . $now->format('Ymd_His') . '.' . $selfieImage->getClientOriginalExtension();
            $selfiePath = $selfieImage->storeAs('selfies', $selfieFileName);

            // Tentukan status kehadiran berdasarkan waktu absen
            $statusKehadiran = 'hadir';
            $keterangan = 'hadir';
            
            if ($now->gt($shiftStart)) {
                $statusKehadiran = 'terlambat';
                $keterangan = 'terlambat';
            }

            // Buat absensi baru untuk waktu masuk
            $absensi = Absensi::create([
                'id_user' => $userId,
                'id_jadwal' => $jadwalShift->id,
                'tanggal_absen' => $now->toDateString(),
                'waktu_masuk_time' => $now->toTimeString(),
                'durasi_hadir' => 0,
                'status_kehadiran' => $statusKehadiran,
                'keterangan' => $keterangan,
                'selfiemasuk' => $selfiePath,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $responseMessage = 'Absensi masuk berhasil dicatat.';
            if ($statusKehadiran === 'terlambat') {
                $keterlambatan = $shiftStart->diffInMinutes($now);
                $responseMessage .= ' Anda terlambat ' . $keterlambatan . ' menit.';
            }

            return response()->json([
                'message' => $responseMessage,
                'waktu_masuk' => $now->format('H:i:s'),
                'status' => $statusKehadiran,
                'selfie_path' => $selfiePath,
                'batas_absen_keluar' => $batasAbsenKeluar->format('H:i')
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