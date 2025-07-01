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

        // Proses menyimpan file selfie langsung ke storage/app
        $selfieFileName = 'selfie_' . $userId . '_' . $now->format('Ymd_His') . '.' . $selfieImage->getClientOriginalExtension();

        // Simpan file ke storage/app/selfies (bukan di public)
        $selfiePath = $selfieImage->storeAs('selfies', $selfieFileName);

        // Cari data absensi hari ini
        $absensi = Absensi::where('id_user', $userId)
            ->where('tanggal_absen', Carbon::today())
            ->first();

        if ($absensi) {
            // Jika sudah ada absensi hari ini (proses absen keluar)
            if ($absensi->waktu_keluar_time) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absensi masuk dan keluar hari ini.'
                ], 400);
            }

            // Validasi waktu absen keluar
            // Bisa absen keluar dari jam end shift sampai 1 jam setelahnya
            $shiftEndWithBuffer = $shiftEnd->copy()->addHour();
            
            if ($now->lt($shiftEnd)) {
                return response()->json([
                    'message' => 'Belum waktunya absen keluar. Shift berakhir pada: ' . $shift->end_time
                ], 400);
            }

            if ($now->gt($shiftEndWithBuffer)) {
                return response()->json([
                    'message' => 'Waktu absen keluar sudah terlewat. Batas absen keluar sampai: ' . 
                    $shiftEndWithBuffer->format('H:i:s')
                ], 400);
            }

            // Hitung durasi hadir
            $waktuMasuk = Carbon::parse($absensi->waktu_masuk_time);
            $waktuKeluar = Carbon::now();
            $durasiHadir = $waktuMasuk->diffInMinutes($waktuKeluar);

            // Update waktu keluar dan durasi
            $absensi->update([
                'waktu_keluar_time' => $waktuKeluar->toTimeString(),
                'durasi_hadir' => $durasiHadir,
                'selfiekeluar' => $selfiePath, // Path relatif ke storage/app
                'updated_at' => $now
            ]);

            try {
                // Ambil instance User berdasarkan ID
                $user = User::find($userId);
                if ($user) {
                    Gaji::generateSalary($user); // Kirim instance User, bukan ID
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
            // Validasi waktu absen masuk
            // Batas terlambat: 2 jam setelah shift mulai
            $batasTerlambat = $shiftStart->copy()->addHours(2);
            
            // Batas minimum: 30 menit sebelum shift
            $batasMinimum = $shiftStart->copy()->subMinutes(30);

            if ($now->lt($batasMinimum)) {
                return response()->json([
                    'message' => 'Belum waktunya absen masuk. Shift dimulai pada: ' . $shift->start_time
                ], 400);
            }

            if ($now->gt($batasTerlambat)) {
                return response()->json([
                    'message' => 'Waktu absen masuk sudah terlewat. Batas absen masuk sampai: ' . 
                    $batasTerlambat->format('H:i:s')
                ], 400);
            }

            // Tentukan status kehadiran dan keterangan
            $statusKehadiran = 'hadir';
            $keterangan = 'hadir';
            
            if ($now->gt($shiftStart)) {
                $statusKehadiran = 'terlambat';
                $keterangan = 'terlambat';
            }

            try {
                // Buat absensi baru untuk waktu masuk
                $absensi = Absensi::create([
                    'id_user' => $userId,
                    'id_jadwal' => $jadwalShift->id,
                    'tanggal_absen' => $now->toDateString(),
                    'waktu_masuk_time' => $now->toTimeString(),
                    'durasi_hadir' => 0, // Durasi awal 0 karena baru masuk
                    'status_kehadiran' => $statusKehadiran,
                    'keterangan' => $keterangan,
                    'selfiemasuk' => $selfiePath, // Path relatif ke storage/app
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                // Log untuk debugging
                \Log::info('Absensi masuk berhasil dibuat', [
                    'absensi_id' => $absensi->id,
                    'user_id' => $userId,
                    'jadwal_id' => $jadwalShift->id,
                    'tanggal' => $now->toDateString(),
                    'waktu_masuk' => $now->toTimeString(),
                    'status' => $statusKehadiran
                ]);

                $message = 'Absensi masuk berhasil dicatat.';
                if ($statusKehadiran === 'terlambat') {
                    $selisihMenit = $shiftStart->diffInMinutes($now);
                    $message .= ' (Terlambat ' . $this->formatDurasi($selisihMenit) . ')';
                }

                return response()->json([
                    'message' => $message,
                    'waktu_masuk' => $now->format('H:i:s'),
                    'status' => $statusKehadiran,
                    'selfie_path' => $selfiePath,
                    'absensi_id' => $absensi->id // Tambahkan ID untuk konfirmasi
                ]);

            } catch (\Exception $e) {
                // Log error untuk debugging
                \Log::error('Gagal membuat absensi masuk', [
                    'error' => $e->getMessage(),
                    'user_id' => $userId,
                    'jadwal_id' => $jadwalShift->id,
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'message' => 'Gagal menyimpan data absensi. Silakan coba lagi.',
                    'error' => $e->getMessage()
                ], 500);
            }
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