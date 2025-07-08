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
                            <th>Bukti</th>
                            {{-- <th>Aksi</th> --}}
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
                                @if($izin->image)
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-success mr-2">
                                            <i class="fas fa-check"></i> Ada Bukti
                                        </span>
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewImage('{{ asset('storage/' . $izin->image) }}', '{{ $izin->jenis_izin }}')">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    </div>
                                @else
                                    @php
                                        $tanggalMulai = \Carbon\Carbon::parse($izin->tanggal_mulai);
                                        $today = \Carbon\Carbon::today();
                                        $canUpload = $today->gte($tanggalMulai) && $today->lte($tanggalMulai->copy()->addDays(3)); // bisa upload sampai 3 hari setelah tanggal mulai
                                        $isAdmin = auth()->user()->role === 'admin';
                                    @endphp
                                    
                                    @if(($canUpload && auth()->user()->id == $izin->user_id) || $isAdmin)
                                        <button type="button" class="btn btn-warning btn-sm" onclick="showUploadModal({{ $izin->id }}, '{{ $tanggalMulai->format('d/m/Y') }}', '{{ $izin->user->name }}', {{ $isAdmin ? 'true' : 'false' }})">
                                            <i class="fas fa-upload"></i> Upload Bukti
                                            @if($isAdmin)
                                                <small class="d-block text-muted" style="font-size: 0.7em;">(Admin)</small>
                                            @endif
                                        </button>
                                    @elseif($today->lt($tanggalMulai))
                                        <div class="d-flex flex-column align-items-start">
                                            <span class="badge badge-secondary mb-1">
                                                <i class="fas fa-clock"></i> Belum Waktunya
                                            </span>
                                            @if($isAdmin)
                                                <button type="button" class="btn btn-warning btn-sm" onclick="showUploadModal({{ $izin->id }}, '{{ $tanggalMulai->format('d/m/Y') }}', '{{ $izin->user->name }}', true)">
                                                    <i class="fas fa-upload"></i> Upload (Admin)
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="d-flex flex-column align-items-start">
                                            <span class="badge badge-danger mb-1">
                                                <i class="fas fa-times"></i> Tidak Ada Bukti
                                            </span>
                                            @if($isAdmin)
                                                <button type="button" class="btn btn-warning btn-sm" onclick="showUploadModal({{ $izin->id }}, '{{ $tanggalMulai->format('d/m/Y') }}', '{{ $izin->user->name }}', true)">
                                                    <i class="fas fa-upload"></i> Upload (Admin)
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Upload Bukti -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Bukti Izin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="uploadForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div id="adminNotice" class="alert alert-info" style="display: none;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Admin:</strong> Anda sedang mengupload bukti untuk <span id="userName"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="buktiFile">Pilih File Bukti</label>
                            <input type="file" class="form-control-file" id="buktiFile" name="image" required accept="image/jpeg,image/png,image/jpg">
                            <small class="form-text text-muted">
                                Format yang diizinkan: JPG, JPEG, PNG. Maksimal 2MB.
                            </small>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Izin:</label>
                            <span id="tanggalIzin" class="font-weight-bold text-primary"></span>
                        </div>
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <label>Preview:</label>
                            <br>
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <i class="fas fa-upload"></i> Upload Bukti
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal View Image -->
    <div class="modal fade" id="viewImageModal" tabindex="-1" role="dialog" aria-labelledby="viewImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewImageModalLabel">Bukti Izin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="viewImg" src="" alt="Bukti Izin" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Global variables
    let currentUploadId = null;
    
    // Preview image saat file dipilih
    document.getElementById('buktiFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validasi ukuran file
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB!');
                this.value = '';
                document.getElementById('imagePreview').style.display = 'none';
                return;
            }
            
            // Validasi tipe file
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format file harus JPG, JPEG, atau PNG!');
                this.value = '';
                document.getElementById('imagePreview').style.display = 'none';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            document.getElementById('imagePreview').style.display = 'none';
        }
    });

    // Fungsi untuk menampilkan modal upload
    function showUploadModal(id, tanggal, userName = null, isAdmin = false) {
        currentUploadId = id;
        document.getElementById('tanggalIzin').textContent = tanggal;
        
        // Set form action
        document.getElementById('uploadForm').action = `/permintaan-izin/${id}/upload-bukti`;
        
        // Show/hide admin notice
        if (isAdmin && userName) {
            document.getElementById('adminNotice').style.display = 'block';
            document.getElementById('userName').textContent = userName;
        } else {
            document.getElementById('adminNotice').style.display = 'none';
        }
        
        $('#uploadModal').modal('show');
    }

    // Reset modal when hidden
    $('#uploadModal').on('hidden.bs.modal', function() {
        document.getElementById('uploadForm').reset();
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('adminNotice').style.display = 'none';
        currentUploadId = null;
    });
    
    // Fungsi untuk melihat gambar
    function viewImage(imageSrc, jenisIzin) {
        document.getElementById('viewImg').src = imageSrc;
        document.getElementById('viewImageModalLabel').textContent = `Bukti ${jenisIzin}`;
        $('#viewImageModal').modal('show');
    }
</script>
@endsection

@section('styles')
<style>
    /* Custom Styling untuk SweetAlert2 */
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

    .swal2-popup {
        font-family: inherit !important;
    }

    .swal2-actions {
        gap: 1rem !important;
        margin-top: 2rem !important;
    }

    /* Styling untuk badge dan tombol */
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.375rem 0.75rem;
    }

    /* Styling untuk admin button */
    .btn-warning small {
        line-height: 1;
        margin-top: 2px;
    }

    /* Alert styling */
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@endsection