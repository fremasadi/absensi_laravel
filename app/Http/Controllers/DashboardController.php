<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gaji;
use App\Models\Absensi;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalGaji = Gaji::sum('total_gaji');

        $totalJamHadir = Absensi::sum('durasi_hadir');
        $totalJamIdeal = Absensi::count() * 8; // asumsi 8 jam per hari

        $persentaseKehadiran = $totalJamIdeal > 0
            ? round(($totalJamHadir / $totalJamIdeal) * 100, 2)
            : 0;

        return view('dashboard', [
            'totalGaji' => $totalGaji,
            'persentaseKehadiran' => $persentaseKehadiran,
        ]);
    }
}
