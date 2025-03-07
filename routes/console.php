<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use App\Models\JadwalShift;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// Fungsi untuk pengecekan absensi
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
            Absensi::updateOrCreate(
                ['id_user' => $jadwal->id_user, 'tanggal_absen' => $today],
                [
                    'id_jadwal' => $jadwal->id,
                    'waktu_masuk_time' => null,
                    'waktu_keluar_time' => null,
                    'durasi_hadir' => 0,
                    'status_kehadiran' => 'tidak hadir',
                    'keterangan' => 'tanpa keterangan',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            $message = "Created missing attendance record for user {$jadwal->user->name} on shift {$jadwal->shift->name}";
            Log::info($message);
            if ($output) {
                $output->info($message);
            }
        } 
        elseif ($absensi->waktu_masuk_time && !$absensi->waktu_keluar_time) {
            $shiftEnd = Carbon::parse($jadwal->shift->end_time);
            $waktuMasuk = Carbon::parse($absensi->waktu_masuk_time);
            $durasiHadir = max($waktuMasuk->diffInMinutes($shiftEnd), 0);

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

// **Mendaftarkan schedule untuk pengecekan otomatis setiap jam**
Schedule::call(function() {
    checkMissingAttendance();
})->hourly();

// **Mendaftarkan Artisan command untuk pengecekan manual**
Artisan::command('attendance:check-missing', function () {
    $this->info('Checking for missing attendance records...');
    
    checkMissingAttendance($this);
    
    $this->info('Attendance check completed!');
})->purpose('Check and fill missing attendance records');


// Command untuk generate gaji
Artisan::command('salary:generate {user_id?}', function ($userId = null) {
    try {
        // Tentukan fungsi helper di dalam scope command
        $generateSalary = function ($user) {
            $now = Carbon::now();

            // Ambil setting gaji default
            $settingGaji = \App\Models\SettingGaji::first();
            if (!$settingGaji) {
                $this->error('Data setting gaji tidak ditemukan.');
                \Log::error('Data setting gaji tidak ditemukan.');
                return;
            }

            // Tentukan periode gaji
            $periodeGaji = $settingGaji->periode_gaji;
            $periodeAwal = $now->copy()->subDays($periodeGaji);
            $periodeAkhir = $now;

            // Cek apakah ada data gaji yang periodenya belum berakhir untuk user ini
            $gajiAktif = \App\Models\Gaji::where('user_id', $user->id)
                ->where('periode_akhir', '>=', $periodeAwal->toDateString())
                ->orderBy('periode_akhir', 'desc')
                ->first();

            // Hitung total jam kerja berdasarkan absensi dalam periode ini
            $totalJamKerja = \App\Models\Absensi::where('id_user', $user->id)
                ->whereBetween('tanggal_absen', [$periodeAwal->toDateString(), $periodeAkhir->toDateString()])
                ->sum('durasi_hadir') / 60; // Konversi menit ke jam

            // Hitung total gaji
            $totalGaji = $totalJamKerja * $settingGaji->gaji_per_jam;

            // Jika ada data gaji yang masih aktif (periode belum berakhir)
            if ($gajiAktif) {
                // Update data gaji yang ada jika setting berubah atau ada perubahan lain
                if ($gajiAktif->setting_gaji_id != $settingGaji->id || 
                    $gajiAktif->total_jam_kerja != $totalJamKerja ||
                    $gajiAktif->total_gaji != $totalGaji) {
                    
                    $gajiAktif->update([
                        'setting_gaji_id' => $settingGaji->id,
                        'total_jam_kerja' => $totalJamKerja,
                        'total_gaji' => $totalGaji,
                        'updated_at' => $now
                    ]);
                    
                    $this->line("Gaji diperbarui untuk user {$user->id} dengan total gaji Rp {$totalGaji}");
                    \Log::info("Gaji diperbarui untuk user {$user->id} dengan total gaji Rp {$totalGaji}");
                }
            } else {
                // Jika tidak ada gaji aktif atau periode sebelumnya sudah berakhir, buat data gaji baru
                \App\Models\Gaji::create([
                    'user_id' => $user->id,
                    'setting_gaji_id' => $settingGaji->id,
                    'periode_awal' => $periodeAwal->toDateString(),
                    'periode_akhir' => $periodeAkhir->toDateString(),
                    'total_jam_kerja' => $totalJamKerja,
                    'total_gaji' => $totalGaji,
                    'status_pembayaran' => 'belum_dibayar',
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                
                $this->line("Gaji baru dibuat untuk user {$user->id} dengan total gaji Rp {$totalGaji}");
                \Log::info("Gaji baru dibuat untuk user {$user->id} dengan total gaji Rp {$totalGaji}");
            }
        };

        if ($userId) {
            // Generate untuk satu user
            $user = \App\Models\User::find($userId);
            if ($user) {
                $generateSalary($user);
                $this->info("Gaji berhasil dihitung untuk user {$user->name}");
            } else {
                $this->error("User tidak ditemukan");
            }
        } else {
            // Generate untuk semua user (tanpa filter status karena kolom tidak ada)
            $users = \App\Models\User::all();
            $count = 0;
            
            $this->info("Memulai perhitungan gaji untuk " . count($users) . " user...");
            
            foreach ($users as $user) {
                $generateSalary($user);
                $count++;
                
                // Tampilkan progress
                if ($count % 10 == 0) {
                    $this->info("Sudah dihitung: {$count} user");
                }
            }
            
            $this->info("Gaji berhasil dihitung untuk {$count} user");
        }
    } catch (\Exception $e) {
        $this->error("Error: " . $e->getMessage());
        \Log::error("Error saat generate gaji: " . $e->getMessage());
        \Log::error($e->getTraceAsString());
    }
})->purpose('Generate salary for users');