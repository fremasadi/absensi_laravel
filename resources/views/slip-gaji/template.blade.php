<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .slip-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        .employee-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .employee-left, .employee-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .attendance-table th,
        .attendance-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        .attendance-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .salary-section {
            border: 2px solid #333;
            padding: 15px;
            margin-bottom: 20px;
        }
        .salary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .salary-total {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        .footer-left, .footer-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-box {
            border: 1px solid #333;
            height: 80px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ config('app.name', 'PERUSAHAAN') }}</div>
        <div>Alamat Perusahaan</div>
        <div class="slip-title">SLIP GAJI KARYAWAN</div>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <div class="employee-left">
            <div class="info-row">
                <span class="label">Nama Karyawan:</span>
                <span>{{ $user->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">NIK:</span>
                <span>{{ $user->nik ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Jabatan:</span>
                <span>{{ $user->jabatan ?? '-' }}</span>
            </div>
        </div>
        <div class="employee-right">
            <div class="info-row">
                <span class="label">Periode:</span>
                <span>{{ $rekap->periode_awal->format('d/m/Y') }} - {{ $rekap->periode_akhir->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Bulan/Tahun:</span>
                <span>{{ $rekap->bulan_tahun }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tanggal Cetak:</span>
                <span>{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <table class="attendance-table">
        <thead>
            <tr>
                <th>Total Hari Kerja</th>
                <th>Hadir</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Alpha</th>
                <th>Terlambat</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $rekap->total_hari_kerja }}</td>
                <td>{{ $rekap->total_hadir }}</td>
                <td>{{ $rekap->total_sakit }}</td>
                <td>{{ $rekap->total_izin }}</td>
                <td>{{ $rekap->total_alpha }}</td>
                <td>{{ $rekap->total_terlambat }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Work Hours -->
    <table class="attendance-table">
        <thead>
            <tr>
                <th>Total Jam Kerja</th>
                <th>Total Menit Kerja</th>
                <th>Gaji Per Jam</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($rekap->total_jam_kerja, 2) }} jam</td>
                <td>{{ $rekap->total_menit_kerja }} menit</td>
                <td>Rp {{ number_format($rekap->gaji_per_jam, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Salary Calculation -->
    <div class="salary-section">
        <h3 style="margin-top: 0; text-align: center;">PERHITUNGAN GAJI</h3>
        
        <div class="salary-row">
            <span>Jam Kerja × Gaji Per Jam:</span>
            <span>{{ number_format($rekap->total_jam_kerja, 2) }} × Rp {{ number_format($rekap->gaji_per_jam, 0, ',', '.') }}</span>
        </div>
        
        <!-- Bisa ditambahkan komponen lain seperti tunjangan, potongan, dll -->
        
        <div class="salary-total">
            <div class="salary-row">
                <span>TOTAL GAJI:</span>
                <span>Rp {{ number_format($rekap->total_gaji, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    @if($rekap->keterangan)
    <div style="margin-bottom: 20px;">
        <strong>Keterangan:</strong><br>
        {{ $rekap->keterangan }}
    </div>
    @endif

    <!-- Footer / Signatures -->
    <div class="footer">
        <div class="footer-left">
            <div>Diterima Oleh,</div>
            <div class="signature-box"></div>
            <div style="margin-top: 10px;">
                <strong>{{ $user->name }}</strong><br>
                Karyawan
            </div>
        </div>
        <div class="footer-right">
            <div>Disetujui Oleh,</div>
            <div class="signature-box"></div>
            <div style="margin-top: 10px;">
                <strong>{{ $rekap->approvedBy->name ?? 'HRD' }}</strong><br>
                {{ $rekap->approved_at->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <!-- Status -->
    <div style="text-align: center; margin-top: 20px; font-style: italic;">
        Status: {{ ucfirst($rekap->status_rekap) }} | 
        Tanggal Rekap: {{ $rekap->tanggal_rekap->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>