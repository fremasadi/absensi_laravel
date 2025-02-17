@extends('layouts.sb-admin-2')

@section('title', 'Edit Permintaan Izin')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Permintaan Izin</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Permintaan Izin</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('permintaan-izin.update', $permintaanIzin) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ $permintaanIzin->tanggal_mulai }}" class="form-control @error('tanggal_mulai') is-invalid @enderror" required>
                    @error('tanggal_mulai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $permintaanIzin->tanggal_selesai }}" class="form-control @error('tanggal_selesai') is-invalid @enderror" required>
                    @error('tanggal_selesai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Izin</label>
                    <select name="jenis_izin" class="form-control @error('jenis_izin') is-invalid @enderror" required>
                        <option value="">Pilih Jenis Izin</option>
                        <option value="Sakit" {{ $permintaanIzin->jenis_izin == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="Cuti" {{ $permintaanIzin->jenis_izin == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                        <option value="Keperluan Keluarga" {{ $permintaanIzin->jenis_izin == 'Keperluan Keluarga' ? 'selected' : '' }}>Keperluan Keluarga</option>
                    </select>
                    @error('jenis_izin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Alasan</label>
                    <textarea name="alasan" class="form-control @error('alasan') is-invalid @enderror" rows="3" required>{{ $permintaanIzin->alasan }}</textarea>
                    @error('alasan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Bukti</label>
                    @if($permintaanIzin->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/izin-images/' . $permintaanIzin->image) }}" alt="Bukti Izin" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                    <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah bukti</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('permintaan-izin.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
@endsection

