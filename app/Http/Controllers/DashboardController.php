<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gaji;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
{
    $userId = Auth::id(); // atau auth()->id()

    // Ambil total gaji user yang sedang login
    $totalGaji = Gaji::where('user_id', $userId)->sum('total_gaji');

    // Hitung total jam hadir user
    $totalJamHadir = Absensi::where('id_user', $userId)->sum('durasi_hadir');

    // Asumsikan kehadiran ideal adalah 8 jam per absensi
    $totalJamIdeal = Absensi::where('id_user', $userId)->count() * 8;

    $persentaseKehadiran = $totalJamIdeal > 0
        ? round(($totalJamHadir / $totalJamIdeal) * 100, 2)
        : 0;

    return view('dashboard', [
        'totalGaji' => $totalGaji,
        'persentaseKehadiran' => $persentaseKehadiran,
    ]);
}
}
