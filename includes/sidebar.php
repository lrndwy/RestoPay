<?php
$role = $_SESSION['role'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<button id="sidebarToggle" class="navbar-toggler d-md-none">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar bg-gradient-dark text-gray" id="sidebar">
    <div class="sidebar-header p-4 text-center">
        <div class="brand-container">
            <div class="logo-circle">
                <h4 class="brand-text">R<span class="text-success">P</span></h4>
            </div>
            <h4 class="brand-text-full mt-2">Resto<span class="text-success">Pay</span></h4>
            <div class="role-badge"><?php echo ucfirst($role); ?> Panel</div>
        </div>
    </div>
    <div class="sidebar-content">
        <div class="menu-section pt-3">
            <ul class="nav flex-column">
                <?php if($role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="/RestoPay/admin/index.php">
                            <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="/RestoPay/admin/users.php">
                            <div class="menu-icon"><i class="fas fa-users"></i></div>
                            <span>Pengguna</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'tables.php' ? 'active' : ''; ?>" href="/RestoPay/admin/tables.php">
                            <div class="menu-icon"><i class="fas fa-table"></i></div>
                            <span>Meja</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'menu.php' ? 'active' : ''; ?>" href="/RestoPay/admin/menu.php">
                            <div class="menu-icon"><i class="fas fa-book-open"></i></div>
                            <span>Menu</span>
                        </a>
                    </li>
                <?php elseif($role == 'waiter'): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="/RestoPay/waiter/index.php">
                            <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'tables.php' ? 'active' : ''; ?>" href="/RestoPay/waiter/tables.php">
                            <div class="menu-icon"><i class="fas fa-table"></i></div>
                            <span>Status Meja</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>" href="/RestoPay/waiter/orders.php">
                            <div class="menu-icon"><i class="fas fa-clipboard-list"></i></div>
                            <span>Pesanan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>" href="/RestoPay/waiter/customers.php">
                            <div class="menu-icon"><i class="fas fa-users"></i></div>
                            <span>Pelanggan</span>
                        </a>
                    </li>
                <?php elseif($role == 'kasir'): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="/RestoPay/kasir/index.php">
                            <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>" href="/RestoPay/kasir/orders.php">
                            <div class="menu-icon"><i class="fas fa-check-square"></i></div>
                            <span>Daftar Pesanan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'transactions.php' ? 'active' : ''; ?>" href="/RestoPay/kasir/transactions.php">
                            <div class="menu-icon"><i class="fas fa-money-bill-wave"></i></div>
                            <span>Transaksi</span>
                        </a>
                    </li>
                <?php elseif($role == 'owner'): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="/RestoPay/owner/index.php">
                            <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="/RestoPay/owner/reports.php">
                            <div class="menu-icon"><i class="fas fa-chart-line"></i></div>
                            <span>Laporan</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="logout-section">
            <a class="nav-link menu-link text-danger" href="#" onclick="confirmLogout(event)">
                <div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
/* Reset dan base styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 95vh;
    width: 230px;
    background-color: #ffffff;
    border-right: 1px solid #edf2f7;
    overflow-y: hidden;
    overflow-x: hidden;
    transition: all 0.3s ease;
    margin-top: 30px;
    margin-left: 20px;
    border-radius: 10px;
    border: 3px solid #e2e8f0;
    box-shadow: 0 4px 5px rgba(0, 0, 0, 0.15);
}

/* Header sidebar baru */
.sidebar-header {
    padding: 2rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #e6faf8 0%, #c5f1ec 100%);
    position: relative;
    overflow: hidden;
}

.sidebar-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 60%);
    animation: rotate360 20s linear infinite;
}

.brand-container {
    position: relative;
    z-index: 1;
}

.logo-circle {
    width: 60px;
    height: 60px;
    background: #ffffff;
    border-radius: 50%;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(46, 196, 182, 0.15);
    transition: transform 0.3s ease;
}

.logo-circle:hover {
    transform: scale(1.05);
}

.logo-circle .brand-text {
    font-size: 1.8rem;
    margin: 0;
    line-height: 1;
}

.brand-text-full {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1a7268;
    margin: 0.5rem 0;
    letter-spacing: 1px;
}

.role-badge {
    display: inline-block;
    padding: 0.4rem 1rem;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    color: #1a7268;
    font-size: 0.8rem;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(46, 196, 182, 0.1);
}

@keyframes rotate360 {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Menu items */
.nav-item {
    margin: 0.1rem 0;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 1.5rem;
    color: #4a5568 !important;
    transition: all 0.2s ease;
    border-radius: 50px;
    margin: 0.25rem 1rem;
}

.menu-link:hover, 
.menu-link.active {
    background: #e6faf8;
    color: #2ec4b6 !important;
    transform: none;
}

.menu-icon {
    width: 2rem;
    height: 2rem;
    margin-right: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f5f7fb;
    transition: all 0.2s ease;
}

.menu-icon i {
    font-size: 1rem;
    color: #4a5568;
    transition: all 0.2s ease;
}

.menu-link:hover .menu-icon,
.menu-link.active .menu-icon {
    background: #2ec4b6;
}

.menu-link:hover .menu-icon i,
.menu-link.active .menu-icon i {
    color: #ffffff;
}

/* Layout adjustments */
body {
    margin-left: 240px;
    background:rgb(255, 255, 255);
    min-height: 100vh;
    padding: 2rem;

}

.container-fluid {
    max-width: 100%;
    padding: 0 1rem;
    border-radius: 10px;
    border: 3px solid #e2e8f0;
    box-shadow: 0 4px 5px rgba(0, 0, 0, 0.15);
    background: linear-gradient(150deg,rgb(254, 254, 254) 40%, #c5f1ec 80%);
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        left: -240px;
        z-index: 1040;
        transition: left 0.3s ease;
    }
    
    body {
        margin-left: 0;
        padding-top: 4rem;
    }
    
    .navbar-toggler {
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1050;
        width: 2.5rem;
        height: 2.5rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 50px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4a5568;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .navbar-toggler i {
        font-size: 1.25rem;
        color: #4a5568;
    }
    
    .sidebar.show {
        left: 0;
    }
    
    .sidebar-overlay {
        background: rgba(0, 0, 0, 0.3);
        z-index: 1030;
    }
}

/* Perbaikan overlay dan transisi sidebar */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1030;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.show {
    display: block;
    opacity: 1;
}

/* Perbarui style untuk sidebar content dan logout */
.sidebar-content {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px); /* Kurangi tinggi untuk menaikkan logout */
    padding-bottom: 0; /* Hapus padding bawah */
}

.menu-section {
    flex: 1;
    overflow-y: auto;
    padding-bottom: 1rem; /* Tambah padding bawah di menu */
}

.logout-section {
    position: sticky; /* Tambahkan sticky positioning */
    bottom: 0;
    padding: 1rem;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
    margin-top: auto; /* Pastikan selalu di bawah menu */
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1); /* Tambah shadow di atas */
}

.logout-section .menu-link {
    margin: 0;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

/* Hapus style lama untuk logout */
.nav-item.mt-4 {
    margin-top: 0 !important;
}

/* Ganti style untuk logout section */
.logout-section .menu-link:hover {
    background: #fee2e2; /* Background merah muda saat hover */
    color: #dc2626 !important; /* Warna teks merah yang lebih gelap */
}

.logout-section .menu-link:hover .menu-icon {
    background: #dc2626; /* Background icon merah saat hover */
}

.logout-section .menu-link:hover .menu-icon i {
    color: #ffffff; /* Warna icon putih saat hover */
}
</style>

<link rel="stylesheet" href="/RestoPay/assets/css/fontawesome/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
});

function confirmLogout(event) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Konfirmasi Logout',
        text: "Apakah Anda yakin ingin keluar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/RestoPay/auth/logout.php';
        }
    });
}
</script>
