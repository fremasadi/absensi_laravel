<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Gaji; // ⬅️ Tambahkan ini
use Carbon\Carbon;

class GajiController extends Controller
{
    public function downloadSlipGaji($id)
    {
        $gaji = Gaji::with('user')->findOrFail($id);

        $pdf = Pdf::loadView('pdf.slip-gaji-simple', [
            'nama' => $gaji->user->name,
            'periode_awal' => $gaji->periode_awal,
            'periode_akhir' => $gaji->periode_akhir,
            'total_jam_kerja' => $gaji->total_jam_kerja,
            'total_gaji' => $gaji->total_gaji,
            'status_pembayaran' => $gaji->status_pembayaran,
            'created_at' => $gaji->created_at,
            'perusahaan' => 'TOKOKITA',
            'alamat' => 'Jl.Alamat No. 123, Kota'
        ]);

        return $pdf->download("Slip_Gaji_{$gaji->user->name}_Tokokita.pdf");
    }
}