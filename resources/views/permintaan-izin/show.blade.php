@extends('layouts.sb-admin-2')

@section('title', 'Detail Permintaan Izin')
@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detail Permintaan Izin</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Nama Pemohon</th>
                        <td>{{ $permintaanIzin->user->name }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Mulai</th>
                        <td>{{ \Carbon\Carbon::parse($permintaanIzin->tanggal_mulai)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Selesai</th>
                        <td>{{ \Carbon\Carbon::parse($permintaanIzin->tanggal_selesai)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Jenis Izin</th>
                        <td>{{ $permintaanIzin->jenis_izin }}</td>
                    </tr>
                    <tr>
                        <th>Alasan</th>
                        <td>{{ $permintaanIzin->alasan }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge {{ $permintaanIzin->status ? 'badge-success' : 'badge-warning' }}">
                                {{ $permintaanIzin->status ? 'Disetujui' : 'Pending' }}
                            </span>
                        </td>
                    </tr>
                    @if($permintaanIzin->image)
                    <tr>
                        <th>Bukti</th>
                        <td>
                            <img src="{{ asset('storage/izin-images/' . $permintaanIzin->image) }}" alt="Bukti Izin" class="img-fluid" style="max-height: 300px;">
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="mt-3">
                @if(auth()->user()->role === 'admin')
                <form action="{{ route('permintaan-izin.update-status', $permintaanIzin) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn {{ $permintaanIzin->status ? 'btn-warning' : 'btn-success' }}">
                        {{ $permintaanIzin->status ? 'Batalkan Persetujuan' : 'Setujui' }}
                    </button>
                </form>
                @endif
                
                <a href="{{ route('permintaan-izin.edit', $permintaanIzin) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                
                <form action="{{ route('permintaan-izin.destroy', $permintaanIzin) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
                
                <a href="{{ route('permintaan-izin.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection