@extends('layouts.sb-admin-2')

@section('title', 'Permintaan Izin')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Permintaan Izin</h1>
        <a href="{{ route('permintaan-izin.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm"></i> Buat Permintaan
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Permintaan Izin</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Jenis Izin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permintaanIzins as $index => $izin)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $izin->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d/m/Y') }}</td>
                            <td>{{ $izin->jenis_izin }}</td>
                            <td>
                                @if(auth()->user()->role === 'admin')
                                <form action="{{ route('permintaan-izin.update-status', $izin) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $izin->status ? 'btn-success' : 'btn-warning' }}">
                                        {{ $izin->status ? 'Disetujui' : 'Pending' }}
                                    </button>
                                </form>
                                @else
                                    <span class="badge {{ $izin->status ? 'badge-success' : 'badge-warning' }}">
                                        {{ $izin->status ? 'Disetujui' : 'Pending' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('permintaan-izin.show', $izin) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Tombol Edit dan Delete hanya muncul jika status false (pending) -->
                                @if(!$izin->status)
    <a href="{{ route('permintaan-izin.edit', $izin) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit"></i>
    </a>
    
    <!-- Tombol Delete dengan SweetAlert2 -->
    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ route('permintaan-izin.destroy', $izin) }}', '{{ $izin->name ?? 'data ini' }}')">
        <i class="fas fa-trash"></i>
    </button>
@endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

<script>
    function confirmDelete(deleteUrl, itemName = 'data ini') {
        Swal.fire({
            title: 'Hapus Data Ini',
            text: 'Apakah Anda yakin ingin melakukan ini?',
            icon: 'warning',
            iconColor: '#dc3545',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'swal2-popup-custom',
                icon: 'swal2-icon-custom',
                title: 'swal2-title-custom',
                htmlContainer: 'swal2-text-custom',
                confirmButton: 'swal2-confirm-custom',
                cancelButton: 'swal2-cancel-custom'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Buat form dan submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                form.style.display = 'none';
                
                // CSRF Token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                // Method DELETE
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    </script>
    
    <style>
    /* Custom Styling untuk SweetAlert2 agar mirip dengan gambar */
    .swal2-popup-custom {
        border-radius: 15px !important;
        padding: 2rem !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
    }
    
    .swal2-icon-custom {
        margin: 1rem auto 1.5rem !important;
        width: 80px !important;
        height: 80px !important;
        border: none !important;
        background-color: rgba(220, 53, 69, 0.1) !important;
        border-radius: 50% !important;
    }
    
    .swal2-icon-custom .swal2-icon-content {
        color: #dc3545 !important;
        font-size: 2.5rem !important;
    }
    
    .swal2-title-custom {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        color: #333 !important;
        margin-bottom: 0.5rem !important;
    }
    
    .swal2-text-custom {
        color: #6c757d !important;
        font-size: 1rem !important;
        margin-bottom: 2rem !important;
    }
    
    .swal2-confirm-custom {
        background-color: #dc3545 !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 0.75rem 2rem !important;
        font-weight: 500 !important;
        margin-left: 0.5rem !important;
    }
    
    .swal2-confirm-custom:hover {
        background-color: #c82333 !important;
    }
    
    .swal2-cancel-custom {
        background-color: #6c757d !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 0.75rem 2rem !important;
        font-weight: 500 !important;
        margin-right: 0.5rem !important;
    }
    
    .swal2-cancel-custom:hover {
        background-color: #5a6268 !important;
    }
    
    /* Override default SweetAlert2 styles */
    .swal2-popup {
        font-family: inherit !important;
    }
    
    .swal2-actions {
        gap: 1rem !important;
        margin-top: 2rem !important;
    }
    </style>
    
    <!-- ALTERNATIF: Jika ingin menggunakan ikon trash yang lebih mirip gambar -->
    <script>
    function confirmDeleteWithTrashIcon(deleteUrl, itemName = 'data ini') {
        Swal.fire({
            html: `
                <div style="text-align: center;">
                    <div style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; background-color: rgba(220, 53, 69, 0.1); border-radius: 50%; margin-bottom: 1rem;">
                        <i class="fas fa-trash" style="font-size: 2.5rem; color: #dc3545;"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.5rem; font-weight: 600; color: #333;">Hapus User</h3>
                    <p style="color: #6c757d; margin-bottom: 2rem;">Apakah Anda yakin ingin melakukan ini?</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'swal2-confirm-custom',
                cancelButton: 'swal2-cancel-custom'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form logic sama seperti di atas
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                form.style.display = 'none';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    </script>