<?php



// Tambahkan observer untuk SettingGaji
// File: app/Observers/SettingGajiObserver.php
namespace App\Observers;

use App\Models\SettingGaji;
use App\Models\Gaji;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SettingGajiObserver
{
    /**
     * Handle the SettingGaji "updated" event.
     */
    public function updated(SettingGaji $settingGaji)
    {
        $now = Carbon::now();
        
        // Cek apakah gaji_per_jam atau periode_gaji berubah
        if ($settingGaji->wasChanged('gaji_per_jam') || $settingGaji->wasChanged('periode_gaji')) {
            Log::info("Setting gaji diperbarui. Memperbarui data gaji aktif.");
            
            // Ambil semua gaji yang masih aktif (belum berakhir)
            $gajiAktif = Gaji::where('setting_gaji_id', $settingGaji->id)
                ->where('periode_akhir', '>=', $now->toDateString())
                ->get();
                
            foreach ($gajiAktif as $gaji) {
                // Simpan nilai lama untuk log
                $totalGajiLama = $gaji->total_gaji;
                $periodeAkhirLama = $gaji->periode_akhir;
                
                // Hitung ulang total gaji dengan tarif baru
                if ($settingGaji->wasChanged('gaji_per_jam')) {
                    $gaji->total_gaji = $gaji->total_jam_kerja * $settingGaji->gaji_per_jam;
                }
                
                // Perbarui periode akhir jika periode gaji berubah
                if ($settingGaji->wasChanged('periode_gaji')) {
                    $periodeAwal = Carbon::parse($gaji->periode_awal)->startOfDay();
                    $periodeAkhirBaru = $periodeAwal->copy()->addDays($settingGaji->periode_gaji - 1);
                    $gaji->periode_akhir = $periodeAkhirBaru->toDateString();
                }
                
                $gaji->updated_at = $now;
                $gaji->save();
                
                Log::info("Gaji ID {$gaji->id} untuk user ID {$gaji->user_id} diperbarui: " . 
                    ($settingGaji->wasChanged('gaji_per_jam') ? "Total gaji: {$totalGajiLama} -> {$gaji->total_gaji}. " : "") .
                    ($settingGaji->wasChanged('periode_gaji') ? "Periode akhir: {$periodeAkhirLama} -> {$gaji->periode_akhir}." : "")
                );
            }
        }
    }
}