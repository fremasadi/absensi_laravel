<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RekapAbsensiGaji;
use App\Models\Gaji;
use App\Models\User;
use App\Models\SettingGaji;
use Carbon\Carbon;

class GenerateRekapAbsensiGaji extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rekap:generate 
                            {--user= : ID User tertentu (opsional)}
                            {--month= : Bulan dalam format YYYY-MM (default: bulan lalu)}
                            {--setting-gaji= : ID Setting Gaji (default: ambil yang aktif)}
                            {--force : Force regenerate jika sudah ada}';

    /**
     * The console command description.
     */
    protected $description = 'Generate rekap absensi dan gaji untuk karyawan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Memulai proses generate rekap absensi gaji...');

        // Get parameters
        $userId = $this->option('user');
        $month = $this->option('month') ?: Carbon::now()->subMonth()->format('Y-m');
        $settingGajiId = $this->option('setting-gaji');
        $force = $this->option('force');

        // Validasi format bulan
        try {
            $carbonMonth = Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception $e) {
            $this->error('❌ Format bulan tidak valid. Gunakan format YYYY-MM (contoh: 2024-12)');
            return 1;
        }

        // Tentukan periode
        $periodeAwal = $carbonMonth->startOfMonth()->toDateString();
        $periodeAkhir = $carbonMonth->endOfMonth()->toDateString();

        $this->info("📅 Periode: {$periodeAwal} s/d {$periodeAkhir}");

        // Get setting gaji
        if (!$settingGajiId) {
            $settingGaji = SettingGaji::first();
            if (!$settingGaji) {
                $this->error('❌ Setting gaji tidak ditemukan. Harap buat setting gaji terlebih dahulu.');
                return 1;
            }
            $settingGajiId = $settingGaji->id;
        }

        // Get users
        $users = $userId ? User::where('id', $userId)->get() : User::all();

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
                    $this->warn("⚠️ Rekap untuk {$user->name} periode {$month} sudah ada. Gunakan --force untuk regenerate.");
                    $bar->advance();
                    continue;
                }

                // Generate rekap
                $rekap = RekapAbsensiGaji::generateRekap(
                    $user->id,
                    $periodeAwal,
                    $periodeAkhir,
                    $settingGajiId
                );

                // Cek apakah data gaji sudah ada
                $existingGaji = Gaji::where('user_id', $user->id)
                    ->where('periode_awal', $periodeAwal)
                    ->where('periode_akhir', $periodeAkhir)
                    ->first();

                if ($existingGaji && !$force) {
                    $this->newLine();
                    $this->warn("⚠️ Data gaji untuk {$user->name} periode {$month} sudah ada. Gunakan --force untuk regenerate.");
                } else {
                    // Jika force atau belum ada, hapus yang lama dan buat baru
                    if ($existingGaji && $force) {
                        $existingGaji->delete();
                    }

                    // Buat data gaji baru dengan periode yang sama dengan rekap
                    $gaji = Gaji::create([
                        'user_id' => $user->id,
                        'setting_gaji_id' => $settingGajiId,
                        'periode_awal' => $periodeAwal,
                        'periode_akhir' => $periodeAkhir,
                        'total_jam_kerja' => $rekap->total_jam_kerja ?? 0,
                        'total_gaji' => 0 // Akan dihitung setelah ini
                    ]);

                    // Hitung total gaji
                    $gaji->total_gaji = $gaji->calculateTotalGaji();
                    $gaji->save();

                    $this->newLine();
                    $this->info("✅ Data gaji untuk {$user->name} berhasil dibuat/diperbarui");
                }

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
        $this->info("   - Berhasil: {$successCount}");
        $this->info("   - Error: {$errorCount}");
        $this->info("   - Total: " . ($successCount + $errorCount));

        return 0;
    }
}