<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use Illuminate\Support\Facades\Auth;

class RiwayatAbsensiController extends Controller
{
    public function index()
    {
        // Ambil data absensi untuk user yang sedang login
        $absensi = Absensi::where('id_user', Auth::id())
            ->orderBy('tanggal_absen', 'asc')
            ->get();

        return view('riwayat-absensi.index', compact('absensi'));
    }
}