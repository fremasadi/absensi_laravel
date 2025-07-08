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

    // Debug: log setiap kali image di-set
    public function setImageAttribute($value)
    {
        \Log::info('Setting image attribute:', ['value' => $value]);
        
        if ($value) {
            $this->attributes['image'] = $value;
            $this->attributes['bukti_uploaded_at'] = now();
            \Log::info('Image set successfully:', ['image' => $value]);
        } else {
            \Log::info('Image value is null or empty');
        }
    }

    // Debug: log setiap kali image di-get
    public function getImageAttribute($value)
    {
        \Log::info('Getting image attribute:', ['value' => $value]);
        return $value;
    }

    // Relasi dengan model User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Method untuk cek apakah field image ada di fillable
    public static function checkFillable()
    {
        $instance = new static;
        \Log::info('Fillable fields:', $instance->getFillable());
        return in_array('image', $instance->getFillable());
    }
}