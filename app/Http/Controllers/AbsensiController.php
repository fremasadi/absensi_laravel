<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\JadwalShift;
use App\Models\Gaji;
use App\Models\Shift;
use App\Models\User;


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

        // Toleransi waktu (misal 30 menit sebelum dan sesudah shift)
        $toleranceMinutes = 30;
        $shiftStartWithTolerance = $shiftStart->copy()->subMinutes($toleranceMinutes);
        $shiftEndWithTolerance = $shiftEnd->copy()->addMinutes($toleranceMinutes);

        if (!$now->between($shiftStartWithTolerance, $shiftEndWithTolerance)) {
            return response()->json([
                'message' => 'Waktu scan di luar jadwal shift. Shift Anda: ' . 
                $shift->start_time . ' - ' . $shift->end_time
            ], 400);
        }

        // Proses menyimpan file selfie
        $selfieFileName = 'selfie_' . $userId . '_' . $now->format('Ymd_His') . '.' . $selfieImage->getClientOriginalExtension();
        $selfiePath = $selfieImage->storeAs('', $selfieFileName); // Simpan langsung di storage/app
        
        // Cari data absensi hari ini
        $absensi = Absensi::where('id_user', $userId)
            ->where('tanggal_absen', Carbon::today())
            ->first();

        if ($absensi) {
            // Jika sudah ada absensi hari ini
            if ($absensi->waktu_keluar_time) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absensi masuk dan keluar hari ini.'
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
                'selfiekeluar' => $selfieUrl,
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
                'selfie_keluar' => $selfieUrl
            ]);
        } else {
            // Buat absensi baru untuk waktu masuk
            $absensi = Absensi::create([
                'id_user' => $userId,
                'id_jadwal' => $jadwalShift->id,
                'tanggal_absen' => $now->toDateString(),
                'waktu_masuk_time' => $now->toTimeString(),
                'durasi_hadir' => 0, // Durasi awal 0 karena baru masuk
                'status_kehadiran' => 'hadir',
                'keterangan' => 'hadir',
                'selfiemasuk' => $selfieUrl,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            return response()->json([
                'message' => 'Absensi masuk berhasil dicatat.',
                'waktu_masuk' => $now->format('H:i:s'),
                'selfie_masuk' => $selfieUrl
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