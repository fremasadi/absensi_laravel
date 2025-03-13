<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Absensi::select('user_id', 'tanggal_absen', 'waktu_masuk_time', 'waktu_keluar_time', 'durasi_hadir', 'status_kehadiran', 'keterangan')->get();
    }

    public function headings(): array
    {
        return ['User ID', 'Tanggal Absen', 'Waktu Masuk', 'Waktu Keluar', 'Durasi Hadir', 'Status Kehadiran', 'Keterangan'];
    }
}
