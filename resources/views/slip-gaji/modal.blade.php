<div class="p-6">
    <!-- Header -->
    <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
        <h2 class="text-xl font-bold">{{ config('app.name', 'PERUSAHAAN') }}</h2>
        <p class="text-sm text-gray-600">Alamat Perusahaan</p>
        <h3 class="text-lg font-bold mt-2">SLIP GAJI KARYAWAN</h3>
    </div>

    <!-- Employee Information -->
    <div class="grid grid-cols-2 gap-6 mb-6">
        <div>
            <div class="mb-2">
                <span class="font-semibold">Nama Karyawan:</span>
                <span class="ml-2">{{ $user->name }}</span>
            </div>
            <div class="mb-2">
                <span class="font-semibold">NIK:</span>
                <span class="ml-2">{{ $user->nik ?? '-' }}</span>
            </div>
            <div class="mb-2">
                <span class="font-semibold">Jabatan:</span>
                <span class="ml-2">{{ $user->jabatan ?? '-' }}</span>
            </div>
        </div>
        <div>
            <div class="mb-2">
                <span class="font-semibold">Periode:</span>
                <span class="ml-2">{{ $rekap->periode_awal->format('d/m/Y') }} - {{ $rekap->periode_akhir->format('d/m/Y') }}</span>
            </div>
            <div class="mb-2">
                <span class="font-semibold">Bulan/Tahun:</span>
                <span class="ml-2">{{ $rekap->bulan_tahun }}</span>
            </div>
            <div class="mb-2">
                <span class="font-semibold">Status:</span>
                <span class="ml-2 px-2 py-1 rounded text-xs font-semibold
                    {{ $rekap->status_rekap === 'disetujui' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($rekap->status_rekap) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="mb-6">
        <h4 class="font-semibold mb-3">Data Kehadiran</h4>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Total Hari Kerja</th>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Hadir</th>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Sakit</th>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Izin</th>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Alpha</th>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Terlambat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $rekap->total_hari_kerja }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $rekap->total_hadir }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $rekap->total_sakit }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $rekap->total_izin }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $rekap->total_alpha }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $rekap->total_terlambat }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Work Hours -->
    <div class="mb-6">
        <h4 class="font-semibold mb-3">Jam Kerja</h4>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Total Jam Kerja</th>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Total Menit Kerja</th>
                        <th class="border border-gray-300 px-4 py-2 text-sm">Gaji Per Jam</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ number_format($rekap->total_jam_kerja, 2) }} jam</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $rekap->total_menit_kerja }} menit</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">Rp {{ number_format($rekap->gaji_per_jam, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Salary Calculation -->
    <div class="border-2 border-gray-800 p-4 mb-6">
        <h4 class="font-semibold text-center mb-4">PERHITUNGAN GAJI</h4>
        
        <div class="flex justify-between mb-2">
            <span>Jam Kerja × Gaji Per Jam:</span>
            <span>{{ number_format($rekap->total_jam_kerja, 2) }} × Rp {{ number_format($rekap->gaji_per_jam, 0, ',', '.') }}</span>
        </div>
        
        <div class="border-t-2 border-gray-800 pt-3 mt-3">
            <div class="flex justify-between font-bold text-lg">
                <span>TOTAL GAJI:</span>
                <span class="text-green-600">Rp {{ number_format($rekap->total_gaji, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    @if($rekap->keterangan)
    <div class="mb-6">
        <h4 class="font-semibold mb-2">Keterangan:</h4>
        <p class="text-gray-700 bg-gray-50 p-3 rounded">{{ $rekap->keterangan }}</p>
    </div>
    @endif

    <!-- Approval Info -->
    @if($rekap->status_rekap === 'disetujui' && $rekap->approved_at)
    <div class="text-center text-sm text-gray-600 border-t pt-4">
        <p>Disetujui oleh: <strong>{{ $rekap->approvedBy->name ?? 'HRD' }}</strong></p>
        <p>Tanggal Persetujuan: {{ $rekap->approved_at->format('d/m/Y H:i:s') }}</p>
    </div>
    @endif
</div>