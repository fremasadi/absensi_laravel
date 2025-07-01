<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanIzin extends Model
{
    use HasFactory;

    // Tentukan tabel yang digunakan
    protected $table = 'permintaan_izins';

    // Tentukan kolom yang dapat diisi (mass assignment)
    protected $fillable = [
        'user_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_izin',
        'alasan',
        'image',
        'status',
        'bukti_uploaded_at', // Tambahkan ini ke fillable

    ];

    // Relasi dengan model User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'bukti_uploaded_at' => 'datetime',
    ];
}