<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RekapAbsensiGaji extends Model
{
    use HasFactory;

    protected $table = 'rekap_absensi_gaji';

    protected $fillable = [
        'user_id',
        'gaji_id',
        'setting_gaji_id',
        'periode_awal',
        'periode_akhir',
        'bulan_tahun',
        'total_hari_kerja',
        'total_hadir',
        'total_sakit',
        'total_izin',
        'total_alpha',
        'total_terlambat',
        'total_jam_kerja',
        'total_menit_kerja',
        'gaji_per_jam',

        'total_gaji',
        'status_rekap',
        'keterangan',
        'is_final',
        'tanggal_rekap',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'tanggal_rekap' => 'datetime',
        'approved_at' => 'datetime',
        'is_final' => 'boolean',
        'total_jam_kerja' => 'decimal:2',
        'gaji_per_jam' => 'decimal:2',
        'total_gaji' => 'decimal:2',
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke model Gaji
     */
    public function gaji()
    {
        return $this->belongsTo(Gaji::class, 'gaji_id');
    }

    /**
     * Relasi ke model SettingGaji
     */
    public function settingGaji()
    {
        return $this->belongsTo(SettingGaji::class, 'setting_gaji_id');
    }

    /**
     * Relasi ke user yang membuat rekap
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke user yang approve rekap
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope untuk filter berdasarkan bulan tahun
     */
    public function scopeByBulanTahun($query, $bulanTahun)
    {
        return $query->where('bulan_tahun', $bulanTahun);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_rekap', $status);
    }

    /**
     * Scope untuk rekap yang sudah final
     */
    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Method untuk generate rekap absensi dan gaji
     */
    public static function generateRekap($userId, $periodeAwal, $periodeAkhir, $settingGajiId)
    {
        // Ambil data absensi dalam periode
        $absensiData = Absensi::where('id_user', $userId)
            ->whereBetween('tanggal_absen', [$periodeAwal, $periodeAkhir])
            ->get();

        // Ambil setting gaji
        $settingGaji = SettingGaji::find($settingGajiId);
        
        if (!$settingGaji) {
            throw new \Exception('Setting gaji tidak ditemukan');
        }

        // Hitung statistik absensi
        $totalHadir = $absensiData->where('status_kehadiran', 'hadir')->count();
        $totalSakit = $absensiData->where('status_kehadiran', 'sakit')->count();
        $totalIzin = $absensiData->where('status_kehadiran', 'izin')->count();
        $totalAlpha = $absensiData->where('status_kehadiran', 'tidak hadir')->count();
        $totalTerlambat = $absensiData->whereNotNull('durasi_terlambat')->count();

        // Hitung total jam kerja (dalam menit, kemudian convert ke jam)
        $totalMenitKerja = $absensiData->where('status_kehadiran', 'hadir')->sum('durasi_hadir');
        $totalJamKerja = $totalMenitKerja / 60; // convert ke jam

        // Hitung gaji - sederhana: total_jam_kerja * gaji_per_jam
        $totalGaji = $totalJamKerja * $settingGaji->gaji_per_jam;

        // Buat bulan_tahun dari periode_awal
        $bulanTahun = Carbon::parse($periodeAwal)->format('Y-m');

        // Cek apakah rekap sudah ada
        $existingRekap = self::where('user_id', $userId)
            ->where('periode_awal', $periodeAwal)
            ->where('periode_akhir', $periodeAkhir)
            ->first();

        $data = [
            'user_id' => $userId,
            'setting_gaji_id' => $settingGajiId,
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'bulan_tahun' => $bulanTahun,
            'total_hari_kerja' => $absensiData->count(),
            'total_hadir' => $totalHadir,
            'total_sakit' => $totalSakit,
            'total_izin' => $totalIzin,
            'total_alpha' => $totalAlpha,
            'total_terlambat' => $totalTerlambat,
            'total_jam_kerja' => $totalJamKerja,
            'total_menit_kerja' => $totalMenitKerja,
            'gaji_per_jam' => $settingGaji->gaji_per_jam,
            'total_gaji' => $totalGaji,
            'tanggal_rekap' => now(),
        ];

        if ($existingRekap) {
            $existingRekap->update($data);
            return $existingRekap;
        } else {
            return self::create($data);
        }
    }

    /**
     * Method untuk approve rekap
     */
    public function approve($approvedBy)
    {
        $this->update([
            'status_rekap' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'is_final' => true,
        ]);
    }

    /**
     * Method untuk mark as paid
     */
    public function markAsPaid()
    {
        $this->update([
            'status_rekap' => 'paid'
        ]);
    }

    /**
     * Accessor untuk persentase kehadiran
     */
    public function getPersentaseKehadiranAttribute()
    {
        if ($this->total_hari_kerja == 0) return 0;
        return round(($this->total_hadir / $this->total_hari_kerja) * 100, 2);
    }

    /**
     * Accessor untuk format periode yang readable
     */
    public function getFormatPeriodeAttribute()
    {
        return Carbon::parse($this->periode_awal)->format('d/m/Y') . ' - ' . 
               Carbon::parse($this->periode_akhir)->format('d/m/Y');
    }
}