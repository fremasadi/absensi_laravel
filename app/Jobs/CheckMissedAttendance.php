<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\JadwalShift;
use App\Models\Absensi;
use App\Models\Shift;

class CheckMissedAttendance implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = Carbon::today();
        $jadwalShifts = JadwalShift::where('status', 1)->get();

        foreach ($jadwalShifts as $jadwalShift) {
            $shift = Shift::find($jadwalShift->id_shift);
            if (!$shift) {
                continue;
            }

            // Cek apakah shift sudah selesai
            $shiftEndTime = Carbon::parse($shift->end_time);
            if (Carbon::now()->lt($shiftEndTime)) {
                continue; // Shift belum selesai, skip
            }

            // Cek apakah pengguna sudah absen hari ini
            $absensi = Absensi::where('id_user', $jadwalShift->id_user)
                ->where('tanggal_absen', $today)
                ->first();

            if (!$absensi) {
                // Jika tidak ada absensi, tandai sebagai tidak absen
                Absensi::create([
                    'id_user' => $jadwalShift->id_user,
                    'id_jadwal' => $jadwalShift->id,
                    'tanggal_absen' => $today,
                    'status_kehadiran' => 'tidak absen',
                    'keterangan' => 'tidak melakukan absen sampai shift selesai',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}