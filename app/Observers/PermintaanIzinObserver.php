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
        $tanggalIzin = Carbon::parse($izin->tanggal);
        
        $shiftAktif = JadwalShift::where('id_user', $izin->user_id)
            ->where('status', 1)
            ->whereDate('created_at', '<=', $tanggalIzin)
            ->whereDate('expired_at', '>=', $tanggalIzin)
            ->first();

        if (!$shiftAktif) {
            Log::warning("No active shift found for user_id: {$izin->user_id} on {$tanggalIzin->toDateString()}");
            return;
        }

        Absensi::updateOrCreate(
            [
                'id_user' => $izin->user_id,
                'tanggal_absen' => $tanggalIzin->toDateString()
            ],
            [
                'id_jadwal' => $shiftAktif->id,
                'waktu_masuk_time' => null,
                'waktu_keluar_time' => null,
                'durasi_hadir' => 0,
                'status_kehadiran' => $izin->jenis_izin,
                'keterangan' => $izin->alasan,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );

        Log::info("Created leave attendance for user_id: {$izin->user_id} on {$tanggalIzin->toDateString()}");
    }
}