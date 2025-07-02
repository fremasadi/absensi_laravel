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
            $dataGajis = collect([$gaji]);
            $this->info("📋 Menggunakan periode dari gaji ID {$gajiId}");
            
        } elseif ($periodeAwalInput && $periodeAkhirInput) {
            // Gunakan periode custom
            try {
                $periodeAwal = Carbon::createFromFormat('Y-m-d', $periodeAwalInput)->toDateString();
                $periodeAkhir = Carbon::createFromFormat('Y-m-d', $periodeAkhirInput)->toDateString();
                
                $dataGajis = Gaji::where('periode_awal', $periodeAwal)
                               ->where('periode_akhir', $periodeAkhir)
                               ->get();
                
                if ($dataGajis->isEmpty()) {
                    $this->error('❌ Tidak ditemukan data gaji dengan periode tersebut.');
                    return 1;
                }
                
            } catch (\Exception $e) {
                $this->error('❌ Format tanggal tidak valid. Gunakan format YYYY-MM-DD');
                return 1;
            }
            
        } else {
            // Mode ALL - ambil semua data gaji
            $query = Gaji::query();
            
            // Jika ada filter user, terapkan
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $dataGajis = $query->orderBy('periode_awal', 'desc')
                              ->orderBy('user_id')
                              ->get();
                              
            if ($dataGajis->isEmpty()) {
                $this->error('❌ Tidak ditemukan data gaji.');
                return 1;
            }
            
            $this->info("📋 Mode ALL - Memproses semua data gaji ({$dataGajis->count()} data)");
        }

        $this->info("👥 Akan memproses {$dataGajis->count()} data gaji");

        $successCount = 0;
        $errorCount = 0;
        $skipCount = 0;

        // Progress bar
        $bar = $this->output->createProgressBar($dataGajis->count());
        $bar->start();

        foreach ($dataGajis as $gaji) {
            try {
                $periodeAwal = $gaji->periode_awal->toDateString();
                $periodeAkhir = $gaji->periode_akhir->toDateString();
                $user = $gaji->user;

                // Cek apakah rekap sudah ada
                $existing = RekapAbsensiGaji::where('user_id', $gaji->user_id)
                    ->where('periode_awal', $periodeAwal)
                    ->where('periode_akhir', $periodeAkhir)
                    ->first();

                if ($existing && !$force) {
                    $skipCount++;
                    $bar->advance();
                    continue;
                }

                // Generate rekap dengan periode yang sama dengan data gaji
                $rekap = RekapAbsensiGaji::generateRekap(
                    $gaji->user_id,
                    $periodeAwal,
                    $periodeAkhir,
                    $gaji->setting_gaji_id
                );

                $successCount++;
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ Proses selesai!");
        $this->info("📊 Summary:");
        $this->info("   - Total data gaji: {$dataGajis->count()}");
        $this->info("   - Berhasil generate: {$successCount}");
        $this->info("   - Dilewati (sudah ada): {$skipCount}");
        $this->info("   - Error: {$errorCount}");

        return 0;
    }
}