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

</body>
</html>