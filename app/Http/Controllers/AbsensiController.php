<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\JadwalShift;
use App\Models\Gaji;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\Storage; // Untuk menyimpan file selfie

class AbsensiController extends Controller
{
    public function handleScan(Request $request)
    {
        $barcodeData = $request->input('barcode');

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
                'durasi_hadir' => $this->formatDurasi($durasiHadir)
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
                'created_at' => $now,
                'updated_at' => $now
            ]);

            return response()->json([
                'message' => 'Absensi masuk berhasil dicatat.',
                'waktu_masuk' => $now->format('H:i:s'),
                'absensi_id' => $absensi->id // Kirim ID absensi untuk upload selfie
            ]);
        }
    }

    // Fungsi untuk menangani upload selfie
    public function uploadSelfie(Request $request)
{
    $request->validate([
        'absensi_id' => 'required|exists:absensi,id',
        'selfie' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Batasan file: 2MB, format JPEG/PNG/JPG
        'selfie_type' => 'required|in:masuk,keluar', // Pastikan jenis selfie valid
    ]);

    $absensi = Absensi::find($request->absensi_id);

    if (!$absensi) {
        return response()->json(['message' => 'Data absensi tidak ditemukan.'], 404);
    }

    // Simpan file selfie
    if ($request->hasFile('selfie')) {
        $file = $request->file('selfie');
        $fileName = 'selfie_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('selfies', $fileName, 'public'); // Simpan di folder 'selfies'

        // Tentukan kolom yang akan diupdate berdasarkan jenis selfie
        $column = $request->selfie_type === 'masuk' ? 'selfiemasuk' : 'selfiekeluar';

        // Update kolom yang sesuai
        $absensi->update([
            $column => $filePath
        ]);

        return response()->json([
            'message' => 'Selfie ' . $request->selfie_type . ' berhasil diupload.',
            'file_path' => $filePath
        ]);
    }

    return response()->json(['message' => 'Gagal mengupload selfie.'], 400);
}
    // Helper function untuk memformat durasi
    private function formatDurasi($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return sprintf('%02d jam %02d menit', $hours, $remainingMinutes);
    }
}