<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'absensi';

    // Primary key
    protected $primaryKey = 'id_absensi';

    // Kolom yang dapat diisi
    protected $fillable = [
        'id_pengguna',
        'id_jadwal',
        'waktu_masuk',
        'waktu_keluar',
        'durasi_terlambat',
        'status_kehadiran',
        'keterangan',
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna', 'id');
    }

    /**
     * Relasi ke model JadwalShift
     */
    public function jadwalShift()
    {
        return $this->belongsTo(JadwalShift::class, 'id_jadwal', 'id_jadwal');
    }
}