@extends('layouts.sb-admin-2')

@section('title', 'Barcode')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Barcode Masuk</h1>
    </div>
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Barcode Masuk Anda</h6>
                </div>
                <div class="card-body text-center">
                    @if ($barcode)
                        <!-- Informasi Shift -->
                        @if(isset($shift))
                        <div class="mb-4">
                            <h5 class="text-primary">{{ $shiftName ?? $shift->name }}</h5>
                            <p class="text-muted">
                                Jam Kerja: {{ $shiftStart->format('H:i') }} - {{ $shiftEnd->format('H:i') }}
                            </p>
                        </div>
                        @endif

                        <h5 class="mb-4">Scan Barcode Berikut:</h5>
                        <div class="mb-4">
                            {!! $barcode !!}
                        </div>

                        <!-- Pesan informasi shift -->
                        <p class="text-info">{{ $message }}</p>
                    @else
                        <h5 class="text-danger">Tidak ada barcode yang tersedia.</h5>
                        <p>{{ $message ?? 'Tidak ada pesan.' }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection