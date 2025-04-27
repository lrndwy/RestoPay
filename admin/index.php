<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';

// Hitung total pendapatan hari ini
$stmt = $pdo->query("SELECT SUM(total) as today_income FROM transactions WHERE DATE(transaction_time) = CURDATE()");
$today_income = $stmt->fetch()['today_income'] ?? 0;

// Hitung total pendapatan bulan ini
$stmt = $pdo->query("SELECT SUM(total) as month_income FROM transactions WHERE MONTH(transaction_time) = MONTH(CURRENT_DATE()) AND YEAR(transaction_time) = YEAR(CURRENT_DATE())");
$month_income = $stmt->fetch()['month_income'] ?? 0;

// Ambil statistik menu terlaris
$stmt = $pdo->query("
    SELECT m.name, SUM(oi.quantity) as total_ordered
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    GROUP BY m.id
    ORDER BY total_ordered DESC
    LIMIT 5
");
$popular_menu = $stmt->fetchAll();

// Ambil statistik transaksi per metode pembayaran
$stmt = $pdo->query("
    SELECT payment_method, COUNT(*) as total
    FROM transactions
    WHERE DATE(transaction_time) = CURDATE()
    GROUP BY payment_method
");
$payment_methods = $stmt->fetchAll();
?>

<!-- Tambahkan CSS kustom -->
<style>
:root {
    --primary-color: #4361ee;
    --success-color: #2ec4b6;
    --warning-color: #ff9f1c;
    --info-color: #4cc9f0;
    --dark-color: #2b2d42;
    --light-color: #f8f9fa;
    --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.container-fluid {
    background-color: #f5f7fb;
    min-height: 100vh;
    padding: 2rem !important;
}

.dashboard-title {
    color: var(--dark-color);
    font-weight: 600;
    margin-bottom: 1.5rem;
    font-size: 1.75rem;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: var(--shadow);
    transition: transform 0.2s ease;
    margin-bottom: 1rem;
}

.card:hover {
    transform: translateY(-5px);
}

.bg-primary {
    background: var(--primary-color) !important;
}

.bg-success {
    background: var(--success-color) !important;
}

.bg-warning {
    background: var(--warning-color) !important;
}

.bg-info {
    background: var(--info-color) !important;
}

.card-body {
    padding: 1.5rem;
}

.card-header {
    background: none;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 1.25rem 1.5rem;
}

.card-title {
    color: var(--dark-color);
    font-weight: 600;
    margin: 0;
    font-size: 1.1rem;
}

.table {
    margin: 0;
}

.table thead th {
    border-top: none;
    border-bottom: 2px solid #e9ecef;
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9rem;
}

.table td {
    vertical-align: middle;
    color: var(--dark-color);
    font-size: 0.95rem;
    border-color: #e9ecef;
}

.info-card h5 {
    color: rgba(255,255,255,0.8);
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.info-card h3 {
    color: white;
    font-size: 1.75rem;
    font-weight: 600;
    margin: 0;
}

.financial-summary .border {
    border-radius: 10px;
    transition: all 0.2s ease;
}

.financial-summary .border:hover {
    background-color: var(--light-color);
    border-color: var(--primary-color) !important;
}

.financial-summary h6 {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}

.financial-summary h4 {
    color: var(--dark-color);
    font-weight: 600;
    margin: 0;
}

@media print {
    /* Sembunyikan elemen yang tidak perlu dicetak */
    .btn, 
    #sidebar,
    .action-buttons {
        display: none !important;
    }

    /* Atur tampilan untuk cetak */
    body {
        background: white;
        font-family: 'Arial', sans-serif;
        padding: 20px;
    }

    .container-fluid {
        width: 100%;
        padding: 0;
        margin: 0;
        background: white;
    }

    .card {
        break-inside: avoid;
        border: 1px solid #ddd;
        box-shadow: none;
        margin-bottom: 20px;
    }

    /* Header laporan */
    .print-header {
        text-align: center;
        margin-bottom: 30px;
        display: none; /* Akan ditampilkan saat print */
    }

    .print-header.show {
        display: block;
    }

    /* Atur ukuran font */
    .dashboard-title {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .info-card h3 {
        font-size: 18px;
    }

    .table {
        font-size: 12px;
    }

    /* Atur warna background card agar terlihat di print */
    .bg-primary, .bg-success, .bg-warning, .bg-info {
        background-color: white !important;
        color: black !important;
    }

    .info-card h5 {
        color: #333 !important;
    }

    .info-card h3 {
        color: #000 !important;
    }
}
</style>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="dashboard-title">Dashboard Administrator</h2>
            <button class="btn btn-primary" onclick="printDashboard()">
                <i class="fas fa-print me-2"></i>Cetak Laporan
            </button>
        </div>
        
        <!-- Kartu Informasi Utama -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white info-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-table me-2" style="font-size: 24px;"></i>
                            <h5>Total Meja</h5>
                        </div>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tables");
                        $total_tables = $stmt->fetch()['total'];
                        ?>
                        <h3><?php echo $total_tables; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white info-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-utensils me-2" style="font-size: 24px;"></i>
                            <h5>Total Menu</h5>
                        </div>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM menu");
                        $total_menu = $stmt->fetch()['total'];
                        ?>
                        <h3><?php echo $total_menu; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white info-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clipboard-list me-2" style="font-size: 24px;"></i>
                            <h5>Pesanan Hari Ini</h5>
                        </div>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE DATE(order_time) = CURDATE()");
                        $total_orders = $stmt->fetch()['total'];
                        ?>
                        <h3><?php echo $total_orders; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white info-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-dollar-sign me-2" style="font-size: 24px;"></i>
                            <h5>Pendapatan Hari Ini</h5>
                        </div>
                        <h3>Rp <?php echo number_format($today_income, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik dan Statistik -->
        <div class="row mt-4">
            <!-- Menu Terlaris -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-line me-2"></i>
                            <h5 class="card-title">Top Menu Performance</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Total Dipesan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($popular_menu as $menu): ?>
                                <tr>
                                    <td><?php echo $menu['name']; ?></td>
                                    <td><?php echo $menu['total_ordered']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Metode Pembayaran Hari Ini -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-credit-card me-2"></i>
                            <h5 class="card-title">Payment Analytics</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Metode</th>
                                    <th>Jumlah Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($payment_methods as $method): ?>
                                <tr>
                                    <td><?php echo ucfirst($method['payment_method']); ?></td>
                                    <td><?php echo $method['total']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Keuangan -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-bar me-2"></i>
                            <h5 class="card-title">Financial Overview</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <h6>Pendapatan Hari Ini</h6>
                                    <h4>Rp <?php echo number_format($today_income, 0, ',', '.'); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <h6>Pendapatan Bulan Ini</h6>
                                    <h4>Rp <?php echo number_format($month_income, 0, ',', '.'); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <h6>Rata-rata Transaksi</h6>
                                    <h4>Rp <?php echo $total_orders > 0 ? number_format($today_income / $total_orders, 0, ',', '.') : 0; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Hapus script Lucide dan ganti dengan Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Tambahkan Chart.js untuk visualisasi data -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function printDashboard() {
    // Tambahkan header laporan
    const header = document.createElement('div');
    header.className = 'print-header show';
    header.innerHTML = `
        <h1 style="font-size: 24px; font-weight: bold;">RESTORAN KAMI</h1>
        <p>Jl. Contoh No. 123, Kota</p>
        <p>Telp: (021) 123456</p>
        <h2 style="margin: 20px 0;">Laporan Dashboard Administrator</h2>
        <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        })}</p>
        <hr style="margin: 20px 0;">
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(header, container.firstChild);

    // Cetak halaman
    window.print();

    // Hapus header setelah cetak
    setTimeout(() => {
        header.remove();
    }, 1000);
}
</script>
