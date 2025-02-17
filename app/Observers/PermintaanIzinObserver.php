<?php

namespace App\Observers;

use App\Models\PermintaanIzin;
use App\Models\Absensi;
use App\Models\JadwalShift;
use App\Models\Shift;
use Carbon\Carbon;

class PermintaanIzinObserver
{
    /**
     * Handle the PermintaanIzin "updated" event.
     */
    public function updated(PermintaanIzin $izin)
    {
        // Cek jika status izin berubah menjadi disetujui
        if ($izin->isDirty('status') && $izin->status === true) {
            // Cari jadwal shift aktif sesuai user dan tanggal izin
            $tanggalIzin = Carbon::parse($izin->tanggal);
            $shiftAktif = JadwalShift::where('id_user', $izin->user_id)
                ->where('status', 1)
                ->first();

            if (!$shiftAktif) {
                \Log::warning("Jadwal shift tidak ditemukan untuk user_id: {$izin->user_id} pada tanggal {$tanggalIzin->toDateString()}.");
                return;
            }

            // Buat absensi otomatis dengan status izin
            Absensi::create([
                'id_user' => $izin->user_id,
                'id_jadwal' => $shiftAktif->id,
                'tanggal_absen' => $tanggalIzin->toDateString(),
                'waktu_masuk_time' => null,
                'waktu_keluar_time' => null,
                'durasi_hadir' => 0,
                'status_kehadiran' => $izin->jenis_izin,
                'keterangan' => $izin->alasan,
            ]);

            \Log::info("Absensi izin berhasil dibuat untuk user_id: {$izin->user_id} pada tanggal {$tanggalIzin->toDateString()} dengan jenis izin: {$izin->jenis_izin}.");
        }
    }
}