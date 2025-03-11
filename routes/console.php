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
            
            // Periode akhir = hari ini
            $periodeAkhir = $now->copy()->startOfDay();
            
            // Periode awal = hari ini - periode gaji
            $periodeAwal = $now->copy()->startOfDay()->subDays($periodeGaji - 1); // -1 karena hari ini termasuk

            $this->line("Menghitung gaji untuk periode: {$periodeAwal->format('Y-m-d')} sampai {$periodeAkhir->format('Y-m-d')}");

            // Cek apakah ada data gaji yang masih aktif untuk user ini
            $gajiAktif = \App\Models\Gaji::where('user_id', $user->id)
                ->orderBy('periode_akhir', 'desc')
                ->first();

            // Flag untuk menentukan perlu membuat record baru atau tidak
            $buatRecordBaru = true;

            // Jika ada data gaji sebelumnya
            if ($gajiAktif) {
                $gajiPeriodeAkhir = Carbon::parse($gajiAktif->periode_akhir)->startOfDay();
                
                // Jika periode terakhir belum berakhir (masih di hari yang sama atau lebih baru dari hari ini)
                if ($gajiPeriodeAkhir->greaterThanOrEqualTo($periodeAkhir)) {
                    $buatRecordBaru = false;
                    
                    // Periksa apakah setting gaji sudah berubah dari yang terakhir diaplikasikan
                    $gajiSetting = \App\Models\SettingGaji::find($gajiAktif->setting_gaji_id);
                    
                    // Apakah periode gaji berubah? Jika ya, perpanjang periode akhir
                    if ($gajiSetting && $gajiSetting->periode_gaji != $settingGaji->periode_gaji) {
                        // Hitung periode akhir baru berdasarkan periode awal yang sama
                        $periodeAwalAsli = Carbon::parse($gajiAktif->periode_awal)->startOfDay();
                        $periodeAkhirBaru = $periodeAwalAsli->copy()->addDays($settingGaji->periode_gaji - 1); // -1 karena hari awal termasuk
                        
                        // Update periode akhir gaji
                        $gajiAktif->periode_akhir = $periodeAkhirBaru->toDateString();
                        
                        $this->line("Periode akhir gaji untuk user {$user->id} diperpanjang hingga {$periodeAkhirBaru->format('Y-m-d')}");
                        \Log::info("Periode akhir gaji untuk user {$user->id} diperpanjang hingga {$periodeAkhirBaru->format('Y-m-d')}");
                    }
                    
                    // Hitung total jam kerja berdasarkan absensi dalam periode ini
                    $totalJamKerja = \App\Models\Absensi::where('id_user', $user->id)
                        ->whereBetween('tanggal_absen', [$gajiAktif->periode_awal, $gajiAktif->periode_akhir])
                        ->sum('durasi_hadir') / 60; // Konversi menit ke jam

                    // Hitung total gaji dengan tarif terbaru
                    $totalGaji = $totalJamKerja * $settingGaji->gaji_per_jam;

                    // Update data gaji yang ada
                    $gajiAktif->update([
                        'setting_gaji_id' => $settingGaji->id, // Update ke setting gaji terbaru
                        'total_jam_kerja' => $totalJamKerja,
                        'total_gaji' => $totalGaji,
                        'updated_at' => $now
                    ]);
                    
                    $this->line("Gaji diperbarui untuk user {$user->id} dengan total gaji Rp {$totalGaji} (tarif: Rp {$settingGaji->gaji_per_jam}/jam)");
                    \Log::info("Gaji diperbarui untuk user {$user->id} dengan total gaji Rp {$totalGaji} (tarif: Rp {$settingGaji->gaji_per_jam}/jam)");
                }
            }

            // Jika periode sebelumnya sudah berakhir atau tidak ada data gaji sebelumnya
            if ($buatRecordBaru) {
                // Buat data gaji baru dengan nilai awal 0
                $gajiId = \App\Models\Gaji::create([
                    'user_id' => $user->id,
                    'setting_gaji_id' => $settingGaji->id,
                    'periode_awal' => $periodeAwal->toDateString(),
                    'periode_akhir' => $periodeAkhir->addDays($periodeGaji - 1)->toDateString(), // Periode akhir = periode awal + periode gaji - 1
                    'total_jam_kerja' => 0, // Mulai dengan 0
                    'total_gaji' => 0, // Mulai dengan 0
                    'status_pembayaran' => 'belum_dibayar',
                    'created_at' => $now,
                    'updated_at' => $now
                ])->id;
                
                $this->line("Gaji baru dibuat untuk user {$user->id} dengan periode {$periodeAwal->toDateString()} sampai {$periodeAkhir->toDateString()}");
                \Log::info("Gaji baru dibuat untuk user {$user->id} dengan periode {$periodeAwal->toDateString()} sampai {$periodeAkhir->toDateString()}");
                
                // Setelah membuat record dengan nilai 0, baru update nilainya jika ada absensi
                $totalJamKerja = \App\Models\Absensi::where('id_user', $user->id)
                    ->whereBetween('tanggal_absen', [$periodeAwal->copy()->subDays($periodeGaji - 1)->toDateString(), $periodeAkhir->toDateString()])
                    ->sum('durasi_hadir') / 60; // Konversi menit ke jam
                
                if ($totalJamKerja > 0) {
                    // Hitung total gaji berdasarkan jam kerja
                    $totalGaji = $totalJamKerja * $settingGaji->gaji_per_jam;
                    
                    // Update record yang baru dibuat
                    \App\Models\Gaji::where('id', $gajiId)->update([
                        'total_jam_kerja' => $totalJamKerja,
                        'total_gaji' => $totalGaji,
                        'updated_at' => $now
                    ]);
                    
                    $this->line("Gaji user {$user->id} diupdate dengan data absensi: total jam: {$totalJamKerja}, total gaji: Rp {$totalGaji}");
                    \Log::info("Gaji user {$user->id} diupdate dengan data absensi: total jam: {$totalJamKerja}, total gaji: Rp {$totalGaji}");
                }
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
            // Generate untuk semua user
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