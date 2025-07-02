<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Gaji extends Model
{
    protected $table = 'gajis';

    protected $fillable = [
        'user_id', 
        'setting_gaji_id', 
        'periode_awal', 
        'periode_akhir', 
        'total_jam_kerja', 
        'total_gaji', 

    ];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'total_jam_kerja' => 'integer',
        'total_gaji' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function settingGaji(): BelongsTo
    {
        return $this->belongsTo(SettingGaji::class, 'setting_gaji_id');
    }

    public function calculateTotalGaji(): float
    {
        // Ambil gaji_per_jam dari relasi SettingGaji
        return $this->total_jam_kerja * $this->settingGaji->gaji_per_jam;
    }

    // Method untuk generate gaji
public static function generateSalary($user)
{
    $now = Carbon::now();

    // Ambil setting gaji default
    $settingGaji = \App\Models\SettingGaji::first();
    if (!$settingGaji) {
        \Log::error('Data setting gaji tidak ditemukan.');
        return;
    }

    // Tentukan periode gaji
    $periodeGaji = $settingGaji->periode_gaji;
    $periodeAwal = $now->copy()->subDays($periodeGaji);
    $periodeAkhir = $now;

    // Cek apakah ada data gaji yang periodenya belum berakhir untuk user ini
    $gajiAktif = Gaji::where('user_id', $user->id)
        ->where('periode_akhir', '>=', $periodeAwal->toDateString())
        ->orderBy('periode_akhir', 'desc')
        ->first();

    // Hitung total jam kerja berdasarkan absensi dalam periode ini
    $totalJamKerja = Absensi::where('id_user', $user->id)
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
            
            \Log::info("Gaji diperbarui untuk user {$user->id} dengan total gaji Rp {$totalGaji}");
        }
    } else {
        // Jika tidak ada gaji aktif atau periode sebelumnya sudah berakhir, buat data gaji baru
        Gaji::create([
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
        
        \Log::info("Gaji baru dibuat untuk user {$user->id} dengan total gaji Rp {$totalGaji}");
    }
}

    
}
