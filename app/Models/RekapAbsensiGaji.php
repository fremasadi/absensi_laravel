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
        'periode_awal',
        'periode_akhir',
        'total_hari_kerja',
        'total_jam_kerja',
        'total_keterlambatan_menit',
        'total_pulang_cepat_menit',
        'total_tidak_hadir',
        'total_izin',
        'gaji_per_jam',
        'total_gaji_kotor',
        'potongan_keterlambatan',
        'potongan_tidak_hadir',
        'total_gaji_bersih',
        'status_pembayaran',
        'catatan'
    ];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'total_jam_kerja' => 'decimal:2',
        'total_keterlambatan_menit' => 'integer',
        'total_pulang_cepat_menit' => 'integer',
        'gaji_per_jam' => 'decimal:2',
        'total_gaji_kotor' => 'decimal:2',
        'potongan_keterlambatan' => 'decimal:2',
        'potongan_tidak_hadir' => 'decimal:2',
        'total_gaji_bersih' => 'decimal:2'
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Generate rekap untuk user tertentu dalam periode tertentu
     */
    public static function generateRekap($userId, $periodeAwal, $periodeAkhir)
    {
        $periodeAwal = Carbon::parse($periodeAwal);
        $periodeAkhir = Carbon::parse($periodeAkhir);

        // Ambil data absensi dalam periode
        $absensiData = Absensi::with(['user', 'jadwalShift.shift'])
            ->where('id_user', $userId)
            ->whereBetween('tanggal_absen', [$periodeAwal, $periodeAkhir])
            ->get();

        // Ambil setting gaji terbaru untuk user
        $user = User::find($userId);
        $settingGaji = SettingGaji::latest()->first(); // atau bisa disesuaikan dengan relasi user

        // Hitung statistik
        $totalHariKerja = $absensiData->count();
        $totalJamKerja = 0;
        $totalKeterlambatanMenit = 0;
        $totalPulangCepatMenit = 0;
        $totalTidakHadir = 0;
        $totalIzin = 0;

        foreach ($absensiData as $absensi) {
            // Hitung jam kerja
            if ($absensi->waktu_masuk_time && $absensi->waktu_keluar_time) {
                $masuk = Carbon::parse($absensi->tanggal_absen . ' ' . $absensi->waktu_masuk_time);
                $keluar = Carbon::parse($absensi->tanggal_absen . ' ' . $absensi->waktu_keluar_time);
                
                // Handle jika keluar di hari berikutnya
                if ($keluar->lt($masuk)) {
                    $keluar->addDay();
                }
                
                $jamKerja = $keluar->diffInHours($masuk);
                $totalJamKerja += $jamKerja;

                // Hitung keterlambatan
                if ($absensi->jadwalShift && $absensi->jadwalShift->shift) {
                    $shiftMulai = Carbon::parse($absensi->tanggal_absen . ' ' . $absensi->jadwalShift->shift->start_time);
                    if ($masuk->gt($shiftMulai)) {
                        $totalKeterlambatanMenit += $masuk->diffInMinutes($shiftMulai);
                    }

                    // Hitung pulang cepat
                    $shiftSelesai = Carbon::parse($absensi->tanggal_absen . ' ' . $absensi->jadwalShift->shift->end_time);
                    if ($shiftSelesai->lt($shiftMulai)) {
                        $shiftSelesai->addDay();
                    }
                    if ($keluar->lt($shiftSelesai)) {
                        $totalPulangCepatMenit += $shiftSelesai->diffInMinutes($keluar);
                    }
                }
            } else {
                // Tidak hadir atau izin
                if ($absensi->status_kehadiran == 'izin') {
                    $totalIzin++;
                } else {
                    $totalTidakHadir++;
                }
            }
        }

        // Hitung gaji
        $gajiPerJam = $settingGaji ? $settingGaji->gaji_per_jam : 0;
        $totalGajiKotor = $totalJamKerja * $gajiPerJam;
        
        // Hitung potongan (misal: per menit keterlambatan dipotong 1000, per hari tidak hadir dipotong gaji 8 jam)
        $potonganKeterlambatan = ($totalKeterlambatanMenit / 60) * $gajiPerJam * 0.5; // 50% dari gaji per jam
        $potonganTidakHadir = $totalTidakHadir * 8 * $gajiPerJam; // 8 jam per hari tidak hadir
        
        $totalGajiBersih = $totalGajiKotor - $potonganKeterlambatan - $potonganTidakHadir;

        // Simpan atau update rekap
        return self::updateOrCreate([
            'user_id' => $userId,
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
        ], [
            'total_hari_kerja' => $totalHariKerja,
            'total_jam_kerja' => $totalJamKerja,
            'total_keterlambatan_menit' => $totalKeterlambatanMenit,
            'total_pulang_cepat_menit' => $totalPulangCepatMenit,
            'total_tidak_hadir' => $totalTidakHadir,
            'total_izin' => $totalIzin,
            'gaji_per_jam' => $gajiPerJam,
            'total_gaji_kotor' => $totalGajiKotor,
            'potongan_keterlambatan' => $potonganKeterlambatan,
            'potongan_tidak_hadir' => $potonganTidakHadir,
            'total_gaji_bersih' => $totalGajiBersih,
            'status_pembayaran' => 'belum_dibayar',
        ]);
    }

    /**
     * Generate rekap untuk semua user dalam periode tertentu
     */
    public static function generateRekapSemua($periodeAwal, $periodeAkhir)
    {
        $users = User::all();
        $results = [];

        foreach ($users as $user) {
            $results[] = self::generateRekap($user->id, $periodeAwal, $periodeAkhir);
        }

        return $results;
    }

    /**
     * Get detail absensi untuk rekap ini
     */
    public function getDetailAbsensi()
    {
        return Absensi::with(['jadwalShift.shift'])
            ->where('id_user', $this->user_id)
            ->whereBetween('tanggal_absen', [$this->periode_awal, $this->periode_akhir])
            ->orderBy('tanggal_absen')
            ->get();
    }

    /**
     * Format jam kerja untuk display
     */
    public function getFormattedJamKerjaAttribute()
    {
        $jam = floor($this->total_jam_kerja);
        $menit = ($this->total_jam_kerja - $jam) * 60;
        
        return sprintf('%d jam %d menit', $jam, $menit);
    }

    /**
     * Format keterlambatan untuk display
     */
    public function getFormattedKeterlambatanAttribute()
    {
        $jam = floor($this->total_keterlambatan_menit / 60);
        $menit = $this->total_keterlambatan_menit % 60;
        
        return sprintf('%d jam %d menit', $jam, $menit);
    }

    /**
     * Get persentase kehadiran
     */
    public function getPersentaseKehadiranAttribute()
    {
        $totalHariKerjaSeharusnya = $this->periode_awal->diffInDays($this->periode_akhir) + 1;
        $totalHadir = $this->total_hari_kerja - $this->total_tidak_hadir;
        
        return $totalHariKerjaSeharusnya > 0 ? 
            round(($totalHadir / $totalHariKerjaSeharusnya) * 100, 2) : 0;
    }
}