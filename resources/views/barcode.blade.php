@extends('layouts.sb-admin-2')

@section('title', 'Barcode')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $titleBarcode ?? 'Barcode' }}</h1>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        {{ $titleBarcode ?? 'Barcode Anda' }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    @if ($barcode)
                        {{-- Informasi Shift --}}
                        @if(isset($shift))
                        <div class="mb-4">
                            <div class="alert alert-info">
                                <h5>{{ $shiftName ?? $shift->name }}</h5>
                                <p class="mb-0">
                                    Jam Kerja: {{ $shiftStart->format('H:i') }} - {{ $shiftEnd->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Badge Jenis Absensi --}}
                        @if(isset($jenisAbsensi))
                        <div class="mb-3">
                            @if($jenisAbsensi == 'masuk')
                                <span class="badge badge-success badge-pill px-3 py-2" style="font-size: 14px;">
                                    <i class="fas fa-sign-in-alt"></i> ABSENSI MASUK
                                </span>
                            @else
                                <span class="badge badge-danger badge-pill px-3 py-2" style="font-size: 14px;">
                                    <i class="fas fa-sign-out-alt"></i> ABSENSI KELUAR
                                </span>
                            @endif
                        </div>
                        @endif
                        
                        <h5 class="mb-3">Scan Barcode Berikut:</h5>
                        <div class="mb-4">
                            {!! $barcode !!}
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            {{ $message }}
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Tidak ada barcode yang tersedia.
                        </div>
                        <div class="alert alert-info">
                            {{ $message ?? 'Tidak ada pesan.' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-pill {
    border-radius: 50rem !important;
}

.card-body .alert {
    border-radius: 0.5rem;
}

.badge {
    font-weight: 600;
    letter-spacing: 0.5px;
}
</style>
@endsection