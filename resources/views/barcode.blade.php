@extends('layouts.sb-admin-2')

@section('title', 'Barcode')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Barcode</h1>
    </div>
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Barcode Anda</h6>
                </div>
                <div class="card-body text-center">
                    @if ($barcode)
                        <h5 class="mb-4">Scan Barcode Berikut:</h5>
                        <div class="mb-4">
                            {!! $barcode !!} <!-- Pastikan ini tidak di-escape -->
                        </div>
                    @else
                        <h5 class="text-danger">Tidak ada barcode yang tersedia.</h5>
                        <p>{{ $message ?? 'Tidak ada pesan.' }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

