<?php

namespace App\Observers;

use App\Models\PermintaanIzin;
use App\Models\Absensi;
use App\Models\JadwalShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PermintaanIzinObserver
{
    public function updated(PermintaanIzin $izin)
    {
        if ($izin->isDirty('status') && $izin->status === true) {
            $this->createAttendanceForApprovedLeave($izin);
        }
    }

    public function createAttendanceForApprovedLeave(PermintaanIzin $izin)
    {
        // Menangani kedua format tanggal (tanggal tunggal atau rentang tanggal)
        if (isset($izin->tanggal_mulai) && isset($izin->tanggal_selesai)) {
            // Format rentang tanggal (dari console.php)
            $startDate = Carbon::parse($izin->tanggal_mulai);
            $endDate = Carbon::parse($izin->tanggal_selesai);
            $today = Carbon::today();
            
            // Jika tanggal hari ini berada di antara periode izin
            if ($today->between($startDate, $endDate)) {
                $this->createAttendanceRecord($izin, $today);
            }
        } else {
            // Format tanggal tunggal (dari kode asli)
            $tanggalIzin = Carbon::parse($izin->tanggal);
            $this->createAttendanceRecord($izin, $tanggalIzin);
        }
    }

    private function createAttendanceRecord($izin, $tanggal)
    {
        $shiftAktif = JadwalShift::where('id_user', $izin->user_id)
            ->where('status', 1)
            ->whereDate('created_at', '<=', $tanggal)
            ->whereDate('expired_at', '>=', $tanggal)
            ->first();

        if (!$shiftAktif) {
            Log::warning("No active shift found for user_id: {$izin->user_id} on {$tanggal->toDateString()}");
            return;
        }

        Absensi::updateOrCreate(
            [
                'id_user' => $izin->user_id,
                'tanggal_absen' => $tanggal->toDateString()
            ],
            [
                'id_jadwal' => $shiftAktif->id,
                'waktu_masuk_time' => null,
                'waktu_keluar_time' => null,
                'durasi_hadir' => 0,
                'status_kehadiran' => $izin->jenis_izin ?? 'izin',
                'keterangan' => $izin->alasan ?? 'Izin disetujui',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );

        Log::info("Created leave attendance for user_id: {$izin->user_id} on {$tanggal->toDateString()}");
    }
}