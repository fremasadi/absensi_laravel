<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tanda Terima Gaji</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 13px;
            margin: 20px;
        }

        .title {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .signature-box {
            width: 150px;
            height: 100px;
            border: 1px solid #000;
            float: right;
            text-align: center;
            line-height: 100px;
        }
    </style>
</head>
<body>

<div class="title">
    <h2>Tanda Terima Gaji</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($periode_awal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($periode_akhir)->format('d M Y') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Total Jam</th>
            <th>Total Gaji</th>
            <th>TTD</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $nama }}</td>
            <td>{{ $total_jam_kerja }}</td>
            <td>Rp {{ number_format($total_gaji, 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tbody>
</table>

<div class="signature-box">
    TTD
</div>

</body>
</html>
