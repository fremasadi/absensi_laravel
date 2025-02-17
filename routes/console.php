<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\JadwalShift;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// Fungsi untuk melakukan pengecekan absensi
function checkMissingAttendance($output = null) {
    $today = Carbon::today();
    
    $activeShifts = JadwalShift::where('status', 1)
        ->whereHas('shift', function($query) use ($today) {
            $query->whereTime('end_time', '<=', Carbon::now()->toTimeString());
        })
        ->with(['user', 'shift'])
        ->get();

    foreach ($activeShifts as $jadwal) {
        $absensi = Absensi::where('id_user', $jadwal->id_user)
            ->where('tanggal_absen', $today)
            ->first();

        if (!$absensi) {
            Absensi::create([
                'id_user' => $jadwal->id_user,
                'id_jadwal' => $jadwal->id,
                'tanggal_absen' => $today,
                'waktu_masuk_time' => null,
                'waktu_keluar_time' => null,
                'durasi_hadir' => 0,
                'status_kehadiran' => 'tidak hadir',
                'keterangan' => 'tanpa keterangan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $message = "Created missing attendance record for user {$jadwal->user->name} on shift {$jadwal->shift->name}";
            Log::info($message);
            if ($output) {
                $output->info($message);
            }
        }
        elseif ($absensi->waktu_masuk_time && !$absensi->waktu_keluar_time) {
            $shiftEnd = Carbon::parse($jadwal->shift->end_time);
            $waktuMasuk = Carbon::parse($absensi->waktu_masuk_time);
            $durasiHadir = $waktuMasuk->diffInMinutes($shiftEnd);

            $absensi->update([
                'waktu_keluar_time' => $shiftEnd->toTimeString(),
                'durasi_hadir' => $durasiHadir,
                'keterangan' => 'tidak absen keluar',
                'updated_at' => Carbon::now()
            ]);

            $message = "Updated missing exit time for user {$jadwal->user->name} on shift {$jadwal->shift->name}";
            Log::info($message);
            if ($output) {
                $output->info($message);
            }
        }
    }
}

// Mendaftarkan schedule untuk pengecekan otomatis setiap jam
Schedule::call(function() {
    checkMissingAttendance();
})->hourly();

// Mendaftarkan Artisan command untuk pengecekan manual
Artisan::command('attendance:check-missing', function () {
    $this->info('Checking for missing attendance records...');
    
    // Jalankan pengecekan dengan passing $this untuk output
    checkMissingAttendance($this);
    
    $this->info('Attendance check completed!');
})->purpose('Check and fill missing attendance records');