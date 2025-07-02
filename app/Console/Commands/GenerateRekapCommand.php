<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RekapAbsensiGaji;
use App\Models\Gaji;
use App\Models\User;
use Carbon\Carbon;

class GenerateRekapAbsensiGaji extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rekap:generate 
                            {--user= : ID User tertentu (opsional)}
                            {--gaji-id= : ID Gaji untuk mengambil periode yang sama}
                            {--periode-awal= : Tanggal periode awal (YYYY-MM-DD)}
                            {--periode-akhir= : Tanggal periode akhir (YYYY-MM-DD)}
                            {--force : Force regenerate jika sudah ada}';

    /**
     * The console command description.
     */
    protected $description = 'Generate rekap absensi dengan periode yang sama dengan data gaji';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Memulai proses generate rekap absensi...');

        // Get parameters
        $userId = $this->option('user');
        $gajiId = $this->option('gaji-id');
        $periodeAwalInput = $this->option('periode-awal');
        $periodeAkhirInput = $this->option('periode-akhir');
        $force = $this->option('force');

        // Tentukan periode
        if ($gajiId) {
            // Ambil periode dari data gaji yang sudah ada
            $gaji = Gaji::find($gajiId);
            if (!$gaji) {
                $this->error('❌ Data gaji dengan ID tersebut tidak ditemukan.');
                return 1;
            }
            $periodeAwal = $gaji->periode_awal->toDateString();
            $periodeAkhir = $gaji->periode_akhir->toDateString();
            $settingGajiId = $gaji->setting_gaji_id;
            $this->info("📋 Menggunakan periode dari gaji ID {$gajiId}: {$periodeAwal} s/d {$periodeAkhir}");
            
        } elseif ($periodeAwalInput && $periodeAkhirInput) {
            // Gunakan periode custom
            try {
                $periodeAwal = Carbon::createFromFormat('Y-m-d', $periodeAwalInput)->toDateString();
                $periodeAkhir = Carbon::createFromFormat('Y-m-d', $periodeAkhirInput)->toDateString();
                
                // Cari setting gaji yang aktif
                $gaji = Gaji::where('periode_awal', $periodeAwal)
                           ->where('periode_akhir', $periodeAkhir)
                           ->first();
                
                if ($gaji) {
                    $settingGajiId = $gaji->setting_gaji_id;
                } else {
                    $this->error('❌ Tidak ditemukan data gaji dengan periode tersebut.');
                    return 1;
                }
                
            } catch (\Exception $e) {
                $this->error('❌ Format tanggal tidak valid. Gunakan format YYYY-MM-DD');
                return 1;
            }
            
        } else {
            $this->error('❌ Harap tentukan periode dengan --gaji-id atau --periode-awal dan --periode-akhir');
            return 1;
        }

        // Get users
        if ($userId) {
            $users = User::where('id', $userId)->get();
        } elseif ($gajiId) {
            // Jika menggunakan gaji-id, ambil user dari gaji tersebut
            $users = User::where('id', $gaji->user_id)->get();
        } else {
            // Jika periode custom, ambil semua user yang ada gaji di periode tersebut
            $userIds = Gaji::where('periode_awal', $periodeAwal)
                          ->where('periode_akhir', $periodeAkhir)
                          ->pluck('user_id')
                          ->unique();
            $users = User::whereIn('id', $userIds)->get();
        }

        if ($users->isEmpty()) {
            $this->error('❌ User tidak ditemukan.');
            return 1;
        }

        $this->info("👥 Akan memproses {$users->count()} user(s)");

        $successCount = 0;
        $errorCount = 0;

        // Progress bar
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                // Cek apakah rekap sudah ada
                $existing = RekapAbsensiGaji::where('user_id', $user->id)
                    ->where('periode_awal', $periodeAwal)
                    ->where('periode_akhir', $periodeAkhir)
                    ->first();

                if ($existing && !$force) {
                    $this->newLine();
                    $this->warn("⚠️ Rekap untuk {$user->name} periode {$periodeAwal} s/d {$periodeAkhir} sudah ada. Gunakan --force untuk regenerate.");
                    $bar->advance();
                    continue;
                }

                // Generate rekap dengan periode yang sama dengan data gaji
                $rekap = RekapAbsensiGaji::generateRekap(
                    $user->id,
                    $periodeAwal,
                    $periodeAkhir,
                    $settingGajiId
                );

                $successCount++;
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
                $this->error("❌ Error untuk {$user->name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ Proses selesai!");
        $this->info("📊 Summary:");
        $this->info("   - Periode: {$periodeAwal} s/d {$periodeAkhir}");
        $this->info("   - Berhasil: {$successCount}");
        $this->info("   - Error: {$errorCount}");
        $this->info("   - Total: " . ($successCount + $errorCount));

        return 0;
    }
}