<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';

// Hitung total pendapatan hari ini untuk kasir ini
$stmt = $pdo->prepare("SELECT SUM(total) as today_income FROM transactions 
    WHERE kasir_id = ? AND DATE(transaction_time) = CURDATE()");
$stmt->execute([$_SESSION['user_id']]);
$today_income = $stmt->fetch()['today_income'] ?? 0;

// Hitung jumlah transaksi per metode pembayaran hari ini
$stmt = $pdo->prepare("SELECT payment_method, COUNT(*) as total, SUM(total) as amount 
    FROM transactions 
    WHERE kasir_id = ? AND DATE(transaction_time) = CURDATE()
    GROUP BY payment_method");
$stmt->execute([$_SESSION['user_id']]);
$payment_stats = $stmt->fetchAll();

// Ambil pesanan yang pending
$stmt = $pdo->query("
    SELECT o.*, t.table_number,
    (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) as total
    FROM orders o 
    JOIN tables t ON o.table_id = t.id 
    WHERE o.status = 'pending'
    ORDER BY o.order_time ASC
    LIMIT 5");
$pending_orders = $stmt->fetchAll();
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

.btn-primary {
    background: var(--primary-color);
    border: none;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
}

.btn-sm {
    padding: 0.4rem 1rem;
    font-size: 0.875rem;
}

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    padding: 0.6rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.form-label {
    color: var(--dark-color);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.modal-header {
    border-bottom: 1px solid rgba(0,0,0,0.05);
    background: var(--light-color);
    border-radius: 12px 12px 0 0;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid rgba(0,0,0,0.05);
    background: var(--light-color);
    border-radius: 0 0 12px 12px;
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
        display: none;
    }

    .print-header.show {
        display: block;
    }

    /* Atur warna untuk print */
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

    .table {
        font-size: 12px;
    }
}
</style>

<!-- Tambahkan Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="dashboard-title">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard Kasir
            </h2>
            <button class="btn btn-primary" onclick="printDashboard()">
                <i class="fas fa-print me-2"></i>Cetak Laporan
            </button>
        </div>
        
        <!-- Kartu Informasi Utama -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white info-card">
                    <div class="card-body">
                        <h5>
                            <i class="fas fa-clock me-2"></i>
                            Pesanan Pending
                        </h5>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
                        $pending_orders_count = $stmt->fetch()['total'];
                        ?>
                        <h3><?php echo $pending_orders_count; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white info-card">
                    <div class="card-body">
                        <h5>
                            <i class="fas fa-shopping-cart me-2"></i>
                            Transaksi Hari Ini
                        </h5>
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM transactions WHERE kasir_id = ? AND DATE(transaction_time) = CURDATE()");
                        $stmt->execute([$_SESSION['user_id']]);
                        $today_transactions = $stmt->fetch()['total'];
                        ?>
                        <h3><?php echo $today_transactions; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white info-card">
                    <div class="card-body">
                        <h5>
                            <i class="fas fa-dollar-sign me-2"></i>
                            Total Pendapatan Hari Ini
                        </h5>
                        <h3>Rp <?php echo number_format($today_income, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesanan Pending Terbaru -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>
                                Pesanan Pending Terbaru
                            </h5>
                            <a href="/RestoPay/kasir/orders.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-list me-1"></i>
                                Lihat Semua
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>No. Meja</th>
                                        <th><i class="fas fa-clock me-2"></i>Waktu Pesan</th>
                                        <th><i class="fas fa-dollar-sign me-2"></i>Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pending_orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['table_number']; ?></td>
                                        <td><?php echo date('H:i', strtotime($order['order_time'])); ?></td>
                                        <td>Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="processPayment(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-credit-card me-1"></i>
                                                Proses Pembayaran
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($pending_orders)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada pesanan pending</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Pembayaran -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-pie me-2"></i>
                            Statistik Pembayaran Hari Ini
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Metode</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($payment_stats as $stat): ?>
                                    <tr>
                                        <td>
                                            <i class="<?php echo $stat['payment_method'] == 'cash' ? 'fas fa-wallet' : 'fas fa-credit-card'; ?> me-2"></i>
                                            <?php echo ucfirst($stat['payment_method']); ?>
                                        </td>
                                        <td><?php echo $stat['total']; ?></td>
                                        <td>Rp <?php echo number_format($stat['amount'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($payment_stats)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada transaksi</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function processPayment(orderId) {
    window.location.href = `/RestoPay/kasir/orders.php?process=${orderId}`;
}

function printDashboard() {
    // Tambahkan header laporan
    const header = document.createElement('div');
    header.className = 'print-header show';
    header.innerHTML = `
        <h1 style="font-size: 24px; font-weight: bold;">RESTORAN KAMI</h1>
        <p>Jl. Contoh No. 123, Kota</p>
        <p>Telp: (021) 123456</p>
        <h2 style="margin: 20px 0;">Laporan Dashboard Kasir</h2>
        <p>Kasir: <?php echo $_SESSION['username']; ?></p>
        <p>Tanggal: ${new Date().toLocaleDateString('id-ID', {
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

// Refresh halaman setiap 30 detik untuk update data
setTimeout(function() {
    location.reload();
}, 30000);
</script>
