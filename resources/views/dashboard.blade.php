@extends('layouts.sb-admin-2')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard Karyawan</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Total Gaji Dibayarkan</h5>
            <h3>Rp {{ number_format($totalGaji, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Persentase Kehadiran</h5>
            <div id="chart"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Tambahkan ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var options = {
        chart: {
            type: 'pie'
        },
        series: [{{ $persentaseKehadiran }}, {{ 100 - $persentaseKehadiran }}],
        labels: ['Hadir', 'Tidak Hadir'],
        colors: ['#28a745', '#dc3545']
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>
@endsection