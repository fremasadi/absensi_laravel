<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingGaji extends Model
{
    // Tentukan nama tabel jika tidak mengikuti konvensi Laravel (tabel plural)
    protected $table = 'setting_gajis'; 

    // Tentukan kolom mana yang dapat diisi massal
    protected $fillable = [
        'name',
        'user_id',
        'gaji_per_jam',
        'periode_gaji',
    ];

    // Tentukan kolom yang tidak bisa diisi massal
    protected $guarded = [];

    // Tentukan tipe data untuk atribut tertentu (misalnya decimal)
    protected $casts = [
        'gaji_per_jam' => 'decimal:2', // memastikan gaji_per_jam sebagai decimal dengan 2 angka di belakang koma
        'periode_gaji' => 'integer',  // memastikan periode_gaji sebagai integer
    ];

    // Relasi dengan model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Jika ada pengaturan default nilai untuk gaji per jam
    public static function boot()
    {
        parent::boot();

        static::creating(function ($settingGaji) {
            if (!$settingGaji->gaji_per_jam) {
                $settingGaji->gaji_per_jam = 10000.00; // default gaji per jam jika tidak diisi
            }
        });
    }

   
}
