<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - Tokokita</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; }
        .judul { text-align: center; margin-bottom: 20px; }
        .judul h2 { margin: 0; }
        .judul p { margin: 4px 0; }
        .judul hr { margin-top: 10px; }
    </style>
</head>
<body>

<div class="judul">
    <h2>{{ $perusahaan }}</h2>
    <p>{{ $alamat }}</p>
    <hr>
    <h3>SLIP GAJI KARYAWAN</h3>
    <p>Nama: {{ $nama }}</p>
    <p>Periode: {{ \Carbon\Carbon::parse($periode_awal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($periode_akhir)->format('d M Y') }}</p>
</div>
<div class="isi">
    <p>Total Jam Kerja: {{ $total_jam_kerja }}</p>
    <p>Total Gaji: Rp {{ number_format($total_gaji, 0, ',', '.') }}</p>
    <p>Status Pembayaran: {{ ucfirst($status_pembayaran) }}</p>
    <p>Tanggal Dibuat: {{ \Carbon\Carbon::parse($created_at)->format('d M Y H:i') }}</p>
</div>
<style>
    .isi p { margin: 4px 0; }
</style>
</body>
</html>