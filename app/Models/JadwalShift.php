<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalShift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_user',
        'id_shift',
        'status',
    ];

    /**
     * Get the user associated with the shift schedule.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the shift associated with the schedule.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'id_shift');
    }
}
