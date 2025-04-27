<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-left" href="{{ route('dashboard') }}">
        <img src="{{ asset('images/logo2.png') }}" alt="Logo" class="sidebar-brand-logo" style="height: 30px;">
    </a>    

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a class="nav-link" href="{{ url('/barcode') }}">

        <i class="fas fa-qrcode"></i>
            <span>Barcode Absensi</span>
        </a>
        <a class="nav-link" href="{{ route('riwayat-absensi.index') }}">
            <i class="fas fa-inbox"></i>
            <span>Riwayat Absensi</span>
        </a>
        <a class="nav-link" href="{{ route('permintaan-izin.index') }}">
            <i class="fas fa-book"></i>
            <span>Permintaan Ijin</span>
        </a>
    </li>
</ul>
