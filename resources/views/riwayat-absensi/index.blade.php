@extends('layouts.sb-admin-2')

@section('title', 'Riwayat Absensi')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Riwayat Absensi</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Riwayat Absensi</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Absen</th>
                            <th>Waktu Masuk</th>
                            <th>Waktu Keluar</th>
                            <th>Durasi Hadir</th>
                            <th>Status Kehadiran</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absensi as $index => $absen)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($absen->tanggal_absen)->format('d/m/Y') }}</td>
                            <td>{{ $absen->waktu_masuk_time ? \Carbon\Carbon::parse($absen->waktu_masuk_time)->format('H:i:s') : '-' }}</td>
                            <td>{{ $absen->waktu_keluar_time ? \Carbon\Carbon::parse($absen->waktu_keluar_time)->format('H:i:s') : '-' }}</td>
                            <td>{{ $absen->durasi_hadir ? $absen->durasi_hadir . ' menit' : '-' }}</td>
                            <td>{{ $absen->status_kehadiran ?? '-' }}</td>
                            <td>{{ $absen->keterangan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection