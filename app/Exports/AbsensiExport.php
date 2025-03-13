<?php
namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AbsensiExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return Absensi::query()
            ->with(['user', 'jadwalShift.shift'])
            ->select([
                'id_user',
                'id_jadwal',
                'tanggal_absen',
                'waktu_masuk_time',
                'waktu_keluar_time',
                'durasi_hadir',
                'status_kehadiran',
                'keterangan'
            ]);
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Nama Shift',
            'Waktu Mulai Shift',
            'Waktu Selesai Shift', 
            'Tanggal Absen',
            'Waktu Masuk',
            'Waktu Keluar',
            'Durasi Hadir (Menit)',
            'Status Kehadiran',
            'Keterangan',
        ];
    }

    public function map($absensi): array
    {
        return [
            $absensi->user->name ?? 'Tidak Diketahui',
            $absensi->jadwalShift->shift->name ?? 'Tidak Ada Shift',
            $absensi->jadwalShift->shift->start_time ?? '-',
            $absensi->jadwalShift->shift->end_time ?? '-',
            $absensi->tanggal_absen,
            $absensi->waktu_masuk_time,
            $absensi->waktu_keluar_time,
            $absensi->durasi_hadir,
            $absensi->status_kehadiran,
            $absensi->keterangan,
        ];
    }
}