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
    protected $primaryKey = 'id';

    // Kolom yang dapat diisi
    protected $fillable = [
        'id_user',
        'id_jadwal',
        'tanggal_absen',
        'waktu_masuk_time',
        'waktu_keluar_time',
        'durasi_hadir',
        'status_kehadiran',
        'keterangan',
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    

    /**
     * Relasi ke model JadwalShift
     */
    public function jadwalShift()
    {
        return $this->belongsTo(JadwalShift::class, 'id_jadwal', 'id_jadwal');
    }

    public function shift()
{
    return $this->jadwalShift->shift;
}
}