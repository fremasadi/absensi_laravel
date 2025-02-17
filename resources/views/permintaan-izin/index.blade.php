@extends('layouts.sb-admin-2')

@section('title', 'Permintaan Izin')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Permintaan Izin</h1>
        <a href="{{ route('permintaan-izin.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm"></i> Buat Permintaan
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Permintaan Izin</h6>
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
                            <th>Nama</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Jenis Izin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permintaanIzins as $index => $izin)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $izin->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d/m/Y') }}</td>
                            <td>{{ $izin->jenis_izin }}</td>
                            <td>
                                @if(auth()->user()->role === 'admin')
                                <form action="{{ route('permintaan-izin.update-status', $izin) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $izin->status ? 'btn-success' : 'btn-warning' }}">
                                        {{ $izin->status ? 'Disetujui' : 'Pending' }}
                                    </button>
                                </form>
                                @else
                                    <span class="badge {{ $izin->status ? 'badge-success' : 'badge-warning' }}">
                                        {{ $izin->status ? 'Disetujui' : 'Pending' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('permintaan-izin.show', $izin) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Tombol Edit dan Delete hanya muncul jika status false (pending) -->
                                @if(!$izin->status)
                                    <a href="{{ route('permintaan-izin.edit', $izin) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('permintaan-izin.destroy', $izin) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection