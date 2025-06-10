<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-left" id="sidebarLogo" href="{{ route('dashboard') }}">
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

<style>
/* SOLUSI 1: Coba berbagai kemungkinan class collapsed */
.sidebar.toggled .sidebar-brand-logo,
.sidebar.collapsed .sidebar-brand-logo,
.sidebar.sidebar-collapsed .sidebar-brand-logo,
.sidebar-collapsed .sidebar-brand-logo {
    display: none !important;
}

/* SOLUSI 2: Berdasarkan body class (AdminLTE style)
body.sidebar-collapse .sidebar-brand-logo,
body.sidebar-mini .sidebar-brand-logo {
    display: none !important;
}

/* SOLUSI 3: Media query untuk ukuran kecil */
@media (max-width: 768px) {
    .sidebar-brand-logo {
        display: none !important;
    }
}

/* SOLUSI 4: Sembunyikan seluruh sidebar brand */
.sidebar.toggled .sidebar-brand,
.sidebar.collapsed .sidebar-brand,
.sidebar.sidebar-collapsed .sidebar-brand {
    display: none !important;
}

/* SOLUSI 5: Paksa sembunyikan dengan JavaScript trigger */
.hide-logo .sidebar-brand-logo {
    display: none !important;
}

/* SOLUSI 6: Coba dengan ID selector */
#accordionSidebar.toggled .sidebar-brand-logo,
#accordionSidebar.collapsed .sidebar-brand-logo {
    display: none !important;
} */
</style>

<script>
// SCRIPT DEBUG: Cek class apa yang ditambahkan saat sidebar collapsed
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== SIDEBAR DEBUG ===');
    
    const sidebar = document.getElementById('accordionSidebar');
    const logo = document.querySelector('.sidebar-brand-logo');
    
    if (!sidebar) {
        console.log('Sidebar tidak ditemukan');
        return;
    }
    
    console.log('Initial sidebar classes:', sidebar.className);
    
    // Observer untuk memantau perubahan class
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                console.log('Sidebar class berubah menjadi:', sidebar.className);
                
                // Coba berbagai kondisi
                if (sidebar.classList.contains('toggled') || 
                    sidebar.classList.contains('collapsed') || 
                    sidebar.classList.contains('sidebar-collapsed')) {
                    
                    console.log('Sidebar dalam mode collapsed, menyembunyikan logo');
                    if (logo) {
                        logo.style.display = 'none';
                    }
                } else {
                    console.log('Sidebar dalam mode normal, menampilkan logo');
                    if (logo) {
                        logo.style.display = 'block';
                    }
                }
            }
        });
    });
    
    observer.observe(sidebar, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    // Cek juga perubahan pada body
    const bodyObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                console.log('Body class berubah menjadi:', document.body.className);
                
                if (document.body.classList.contains('sidebar-collapse') || 
                    document.body.classList.contains('sidebar-mini')) {
                    
                    console.log('Body dalam mode collapsed, menyembunyikan logo');
                    if (logo) {
                        logo.style.display = 'none';
                    }
                } else {
                    console.log('Body dalam mode normal, menampilkan logo');
                    if (logo) {
                        logo.style.display = 'block';
                    }
                }
            }
        });
    });
    
    bodyObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    // Manual trigger untuk testing
    window.hideSidebarLogo = function() {
        console.log('Manual hide logo triggered');
        if (logo) {
            logo.style.display = 'none';
        }
    };
    
    window.showSidebarLogo = function() {
        console.log('Manual show logo triggered');
        if (logo) {
            logo.style.display = 'block';
        }
    };
});
</script>