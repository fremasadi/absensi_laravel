<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanIzin extends Model
{
    use HasFactory;

    protected $table = 'permintaan_izins';

    protected $fillable = [
        'user_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_izin',
        'alasan',
        'image',
        'status',
        'bukti_uploaded_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'bukti_uploaded_at' => 'datetime',
        'status' => 'boolean',
    ];

    // Relasi dengan model User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk mendapatkan URL gambar
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    // Mutator untuk handle upload
    public function setImageAttribute($value)
    {
        if ($value) {
            $this->attributes['image'] = $value;
            $this->attributes['bukti_uploaded_at'] = now();
        }
    }
}