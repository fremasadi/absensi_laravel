<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RekapAbsensiGaji;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateRekapCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rekap:generate 
                            {--user-id= : ID user tertentu (optional)}
                            {--periode-awal= : Tanggal awal periode (Y-m-d)}
                            {--periode-akhir= : Tanggal akhir periode (Y-m-d)}
                            {--bulan= : Generate untuk bulan tertentu (Y-m format)}
                            {--bulan-lalu : Generate untuk bulan lalu}
                            {--force : Force regenerate jika data sudah ada}';

    /**
     * The console command description.
     */
    protected $description = 'Generate rekap absensi dan gaji untuk periode tertentu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Memulai proses generate rekap...');

        try {
            // Tentukan periode
            $periode = $this->determinePeriode();
            
            if (!$periode) {
                $this->error('❌ Periode tidak valid. Gunakan --periode-awal dan --periode-akhir, atau --bulan, atau --bulan-lalu');
                return 1;
            }

            $periodeAwal = $periode['awal'];
            $periodeAkhir = $periode['akhir'];

            $this->info("📅 Periode: {$periodeAwal->format('d-m-Y')} s/d {$periodeAkhir->format('d-m-Y')}");

            // Tentukan user
            $users = $this->determineUsers();
            
            if ($users->isEmpty()) {
                $this->error('❌ Tidak ada user yang ditemukan');
                return 1;
            }

            $this->info("👥 Akan generate rekap untuk {$users->count()} user");

            // Konfirmasi jika tidak force
            if (!$this->option('force')) {
                if (!$this->confirm('Lanjutkan generate rekap?')) {
                    $this->info('⚠️ Proses dibatalkan');
                    return 0;
                }
            }

            // Progress bar
            $progressBar = $this->output->createProgressBar($users->count());
            $progressBar->start();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($users as $user) {
                try {
                    // Cek apakah rekap sudah ada
                    $existingRekap = RekapAbsensiGaji::where('user_id', $user->id)
                        ->where('periode_awal', $periodeAwal)
                        ->where('periode_akhir', $periodeAkhir)
                        ->first();

                    if ($existingRekap && !$this->option('force')) {
                        $this->newLine();
                        $this->warn("⚠️ Rekap untuk {$user->name} sudah ada. Gunakan --force untuk regenerate");
                        $progressBar->advance();
                        continue;
                    }

                    // Generate rekap
                    $rekap = RekapAbsensiGaji::generateRekap(
                        $user->id,
                        $periodeAwal,
                        $periodeAkhir
                    );

                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "User {$user->name}: " . $e->getMessage();
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            DB::commit();

            // Summary
            $this->info("✅ Rekap berhasil di-generate!");
            $this->table(['Statistik', 'Jumlah'], [
                ['Berhasil', $successCount],
                ['Error', $errorCount],
                ['Total User', $users->count()]
            ]);

            // Tampilkan error jika ada
            if (!empty($errors)) {
                $this->error('❌ Error yang terjadi:');
                foreach ($errors as $error) {
                    $this->line("   - $error");
                }
            }

            // Tampilkan summary rekap
            $this->showRekapSummary($periodeAwal, $periodeAkhir);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Tentukan periode berdasarkan input
     */
    private function determinePeriode()
    {
        // Jika ada periode manual
        if ($this->option('periode-awal') && $this->option('periode-akhir')) {
            return [
                'awal' => Carbon::parse($this->option('periode-awal')),
                'akhir' => Carbon::parse($this->option('periode-akhir'))
            ];
        }

        // Jika bulan lalu
        if ($this->option('bulan-lalu')) {
            $bulanLalu = Carbon::now()->subMonth();
            return [
                'awal' => $bulanLalu->startOfMonth(),
                'akhir' => $bulanLalu->endOfMonth()
            ];
        }

        // Jika bulan tertentu
        if ($this->option('bulan')) {
            $bulan = Carbon::createFromFormat('Y-m', $this->option('bulan'));
            return [
                'awal' => $bulan->startOfMonth(),
                'akhir' => $bulan->endOfMonth()
            ];
        }

        // Default: bulan ini
        $bulanIni = Carbon::now();
        return [
            'awal' => $bulanIni->copy()->startOfMonth(),
            'akhir' => $bulanIni->copy()->endOfMonth()
        ];
    }

    /**
     * Tentukan user yang akan di-generate
     */
    private function determineUsers()
    {
        if ($this->option('user-id')) {
            return User::where('id', $this->option('user-id'))->get();
        }

        return User::all();
    }

    /**
     * Tampilkan summary rekap
     */
    private function showRekapSummary($periodeAwal, $periodeAkhir)
    {
        $rekaps = RekapAbsensiGaji::with('user')
            ->where('periode_awal', $periodeAwal)
            ->where('periode_akhir', $periodeAkhir)
            ->get();

        if ($rekaps->isEmpty()) {
            return;
        }

        $this->info("\n📊 Summary Rekap:");
        
        $totalGaji = $rekaps->sum('total_gaji_bersih');
        $totalJamKerja = $rekaps->sum('total_jam_kerja');
        $totalKeterlambatan = $rekaps->sum('total_keterlambatan_menit');
        $avgKehadiran = $rekaps->avg('persentase_kehadiran');

        $this->table(['Metrik', 'Nilai'], [
            ['Total Gaji Bersih', 'Rp ' . number_format($totalGaji, 0, ',', '.')],
            ['Total Jam Kerja', number_format($totalJamKerja, 1) . ' jam'],
            ['Total Keterlambatan', number_format($totalKeterlambatan) . ' menit'],
            ['Rata-rata Kehadiran', number_format($avgKehadiran, 1) . '%']
        ]);

        // Top 3 karyawan dengan jam kerja terbanyak
        $topKaryawan = $rekaps->sortByDesc('total_jam_kerja')->take(3);
        
        if ($topKaryawan->count() > 0) {
            $this->info("\n🏆 Top 3 Karyawan (Jam Kerja):");
            $tableData = [];
            foreach ($topKaryawan as $index => $rekap) {
                $tableData[] = [
                    '#' . ($index + 1),
                    $rekap->user->name,
                    number_format($rekap->total_jam_kerja, 1) . ' jam',
                    'Rp ' . number_format($rekap->total_gaji_bersih, 0, ',', '.')
                ];
            }
            $this->table(['Ranking', 'Nama', 'Jam Kerja', 'Gaji Bersih'], $tableData);
        }
    }
}