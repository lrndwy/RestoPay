<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';

// Hitung pendapatan hari ini
$stmt = $pdo->query("
    SELECT SUM(total) as today_income 
    FROM transactions 
    WHERE DATE(transaction_time) = CURDATE()
");
$today_income = $stmt->fetch()['today_income'] ?? 0;

// Hitung pendapatan bulan ini
$stmt = $pdo->query("
    SELECT SUM(total) as month_income 
    FROM transactions 
    WHERE MONTH(transaction_time) = MONTH(CURRENT_DATE()) 
    AND YEAR(transaction_time) = YEAR(CURRENT_DATE())
");
$month_income = $stmt->fetch()['month_income'] ?? 0;

// Top 5 menu terlaris bulan ini
$stmt = $pdo->query("
    SELECT m.name, COUNT(oi.id) as total_ordered, SUM(oi.quantity) as total_qty,
           SUM(oi.quantity * oi.price) as total_income
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE MONTH(o.order_time) = MONTH(CURRENT_DATE())
    AND YEAR(o.order_time) = YEAR(CURRENT_DATE())
    GROUP BY m.id
    ORDER BY total_qty DESC
    LIMIT 5
");
$popular_menu = $stmt->fetchAll();

// Performa kasir bulan ini
$stmt = $pdo->query("
    SELECT u.username, COUNT(t.id) as total_transactions, 
           SUM(t.total) as total_income
    FROM transactions t
    JOIN users u ON t.kasir_id = u.id
    WHERE MONTH(t.transaction_time) = MONTH(CURRENT_DATE())
    AND YEAR(t.transaction_time) = YEAR(CURRENT_DATE())
    GROUP BY t.kasir_id
    ORDER BY total_income DESC
");
$kasir_performance = $stmt->fetchAll();

// Statistik metode pembayaran bulan ini
$stmt = $pdo->query("
    SELECT payment_method, COUNT(*) as total_transactions, 
           SUM(total) as total_income
    FROM transactions
    WHERE MONTH(transaction_time) = MONTH(CURRENT_DATE())
    AND YEAR(transaction_time) = YEAR(CURRENT_DATE())
    GROUP BY payment_method
");
$payment_stats = $stmt->fetchAll();
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
    overflow: hidden;
}

.card:hover {
    transform: translateY(-5px);
}

.card-body {
    padding: 1.5rem;
}

.card-header {
    background-color: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem 1.5rem;
}

.card-header h5 {
    margin: 0;
    color: var(--dark-color);
    font-weight: 600;
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
    padding: 1rem;
}

.table td {
    vertical-align: middle;
    color: var(--dark-color);
    font-size: 0.95rem;
    border-color: #e9ecef;
    padding: 1rem;
}

.income-card {
    position: relative;
    overflow: hidden;
}

.income-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    pointer-events: none;
}

.income-card .card-body {
    position: relative;
    z-index: 1;
}

.income-card h5 {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.income-card h3 {
    font-size: 2rem;
    font-weight: 600;
    margin: 0;
}

.table-responsive {
    border-radius: 8px;
    overflow-x: auto;
}

.bg-primary {
    background: linear-gradient(45deg, var(--primary-color), #6c8cff) !important;
}

.bg-success {
    background: linear-gradient(45deg, var(--success-color), #40e0d0) !important;
}

.bg-warning {
    background: linear-gradient(45deg, var(--warning-color), #ffbe0b) !important;
}

.bg-info {
    background: linear-gradient(45deg, var(--info-color), #00b4d8) !important;
}
</style>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <h2 class="dashboard-title">Dashboard Owner</h2>
        
        <!-- Ringkasan Pendapatan -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card income-card bg-primary text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-chart-line me-2"></i>Pendapatan Hari Ini</h5>
                        <h3>Rp <?php echo number_format($today_income, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card income-card bg-success text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-chart-bar me-2"></i>Pendapatan Bulan Ini</h5>
                        <h3>Rp <?php echo number_format($month_income, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Menu Terlaris -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-award me-2"></i>Top 5 Menu Terlaris Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Menu</th>
                                        <th>Qty Terjual</th>
                                        <th>Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popular_menu as $menu): ?>
                                    <tr>
                                        <td><i class="fas fa-utensils me-2"></i><?php echo $menu['name']; ?></td>
                                        <td><?php echo $menu['total_qty']; ?></td>
                                        <td>Rp <?php echo number_format($menu['total_income'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performa Kasir -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-users me-2"></i>Performa Kasir Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kasir</th>
                                        <th>Total Transaksi</th>
                                        <th>Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kasir_performance as $kasir): ?>
                                    <tr>
                                        <td><i class="fas fa-user me-2"></i><?php echo $kasir['username']; ?></td>
                                        <td><?php echo $kasir['total_transactions']; ?></td>
                                        <td>Rp <?php echo number_format($kasir['total_income'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Pembayaran -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-credit-card me-2"></i>Statistik Metode Pembayaran Bulan Ini</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Metode Pembayaran</th>
                                <th>Jumlah Transaksi</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_stats as $stat): ?>
                            <tr>
                                <td>
                                    <i class="fas <?php echo $stat['payment_method'] === 'cash' ? 'fa-money-bill-wave' : 'fa-credit-card'; ?> me-2"></i>
                                    <?php echo strtoupper($stat['payment_method']); ?>
                                </td>
                                <td><?php echo $stat['total_transactions']; ?></td>
                                <td>Rp <?php echo number_format($stat['total_income'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Ganti script Lucide dengan Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
