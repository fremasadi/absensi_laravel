<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Gaji extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gajis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'setting_gaji_id', 
        'periode_awal', 
        'periode_akhir', 
        'total_jam_kerja', 
        'gaji_per_jam', 
        'total_gaji', 
        'status_pembayaran', 
        'catatan'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'total_jam_kerja' => 'integer',
        'gaji_per_jam' => 'decimal:2',
        'total_gaji' => 'decimal:2'
    ];

    /**
     * Relationship with User model
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with SettingGaji model
     *
     * @return BelongsTo
     */
    public function settingGaji(): BelongsTo
    {
        return $this->belongsTo(SettingGaji::class, 'setting_gaji_id');
    }

    /**
     * Scope a query to filter by payment status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_pembayaran', $status);
    }

    /**
     * Calculate total salary based on working hours
     *
     * @return float
     */
    public function calculateTotalGaji(): float
    {
        return $this->total_jam_kerja * $this->gaji_per_jam;
    }

    // Method untuk generate gaji
    public static function generateSalary(User $user)
{
    // Buat setting gaji jika belum ada
    $settingGaji = SettingGaji::where('user_id', $user->id)->first() 
        ?? SettingGaji::initialCreate($user);

    // Hitung periode awal dan akhir
    $periodeAwal = Carbon::now()->startOfDay();
    $periodeAkhir = $periodeAwal->copy()->addDays($settingGaji->periode_gaji);

    // Debug: Log info untuk pemeriksaan
    \Log::info('User ID: ' . $user->id);
    \Log::info('Periode Awal: ' . $periodeAwal);
    \Log::info('Periode Akhir: ' . $periodeAkhir);

    // Hitung total durasi hadir dalam menit, lalu konversi ke jam
    $totalDurasiHadir = Absensi::where('id_user', $user->id)
        ->whereBetween('tanggal_absen', [$periodeAwal->toDateString(), $periodeAkhir->toDateString()]) // Hitung untuk seluruh periode
        ->sum('durasi_hadir');
    $totalJamKerja = $totalDurasiHadir / 60; // Konversi menit ke jam

    \Log::info('Total Durasi Hadir: ' . $totalDurasiHadir);
    \Log::info('Total Jam Kerja: ' . $totalJamKerja);

    // Cek apakah ada data gaji yang periode penggajiannya masih aktif
    $gajiExisting = self::where('user_id', $user->id)
        ->where('periode_awal', '<=', $periodeAwal->toDateString()) // Periode awal <= tanggal sekarang
        ->where('periode_akhir', '>=', $periodeAwal->toDateString()) // Periode akhir >= tanggal sekarang
        ->first();

    if ($gajiExisting) {
        // Jika periode masih aktif, update data gaji yang sudah ada
        $totalJamKerjaBaru = $gajiExisting->total_jam_kerja + $totalJamKerja;
        $totalGajiBaru = $totalJamKerjaBaru * $settingGaji->gaji_per_jam;

        return $gajiExisting->update([
            'total_jam_kerja' => $totalJamKerjaBaru,
            'total_gaji' => $totalGajiBaru
        ]);
    } else {
        // Jika periode sudah berakhir, buat data gaji baru
        return self::create([
            'user_id' => $user->id,
            'setting_gaji_id' => $settingGaji->id,
            'periode_awal' => $periodeAwal->toDateString(),
            'periode_akhir' => $periodeAkhir->toDateString(),
            'total_jam_kerja' => $totalJamKerja,
            'gaji_per_jam' => $settingGaji->gaji_per_jam,
            'total_gaji' => $totalJamKerja * $settingGaji->gaji_per_jam,
            'status_pembayaran' => 'belum_dibayar'
        ]);
    }
}

    /**
     * Check and generate salary for users with expired periods
     *
     * @return void
     */
    public static function checkAndGenerateSalaries()
    {
        // Get users with expired salary periods
        $expiredSalaries = self::where('periode_akhir', '<', Carbon::now())
            ->distinct('user_id')
            ->pluck('user_id');

        foreach ($expiredSalaries as $userId) {
            $user = User::find($userId);
            if ($user) {
                self::generateSalary($user);
            }
        }
    }
}