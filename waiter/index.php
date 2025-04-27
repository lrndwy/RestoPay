<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'waiter') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';

// Hitung pesanan aktif hari ini
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_orders 
    FROM orders 
    WHERE waiter_id = ? 
    AND DATE(order_time) = CURDATE()
");
$stmt->execute([$_SESSION['user_id']]);
$total_orders = $stmt->fetch()['total_orders'];

// Ambil status meja
$stmt = $pdo->query("
    SELECT status, COUNT(*) as total
    FROM tables
    GROUP BY status
");
$table_stats = $stmt->fetchAll();

// Ambil pesanan aktif
$stmt = $pdo->prepare("
    SELECT o.*, t.table_number,
    (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) as total
    FROM orders o 
    JOIN tables t ON o.table_id = t.id 
    WHERE o.waiter_id = ? 
    AND o.status = 'pending'
    ORDER BY o.order_time DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$active_orders = $stmt->fetchAll();
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

.stats-card {
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    pointer-events: none;
}

.stats-card .card-body {
    position: relative;
    z-index: 1;
}

.stats-card h5 {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.stats-card h3 {
    font-size: 2rem;
    font-weight: 600;
    margin: 0;
}

.btn-info {
    background-color: var(--info-color);
    border: none;
    color: white;
}

.btn-info:hover {
    background-color: #00b4d8;
    color: white;
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

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

@media print {
    /* Sembunyikan elemen yang tidak perlu dicetak */
    .btn, 
    #sidebar,
    .action-buttons,
    .btn-close,
    .modal {
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
        background: white !important;
        color: black !important;
        background-image: none !important;
    }

    .stats-card h5 {
        color: #333 !important;
    }

    .stats-card h3 {
        color: #000 !important;
    }

    .table {
        font-size: 12px;
    }

    .badge {
        border: 1px solid #333;
        color: #333 !important;
        background: none !important;
    }
}
</style>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="dashboard-title">Dashboard Waiter</h2>
            <button class="btn btn-primary" onclick="printDashboard()">
                <i class="fas fa-print me-2"></i>Cetak Laporan
            </button>
        </div>
        
        <!-- Kartu Informasi -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-clipboard-list me-2"></i>Total Pesanan Hari Ini</h5>
                        <h3><?php echo $total_orders; ?></h3>
                    </div>
                </div>
            </div>
            <?php foreach ($table_stats as $stat): ?>
            <div class="col-md-4">
                <div class="card stats-card <?php echo $stat['status'] == 'available' ? 'bg-success' : 'bg-warning'; ?> text-white">
                    <div class="card-body">
                        <h5>
                            <i class="fas <?php echo $stat['status'] == 'available' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                            Meja <?php echo $stat['status'] == 'available' ? 'Tersedia' : 'Terisi'; ?>
                        </h5>
                        <h3><?php echo $stat['total']; ?></h3>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pesanan Aktif -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-clock me-2"></i>Pesanan Aktif</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Meja</th>
                                <th>Waktu Pesan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_orders as $order): ?>
                            <tr>
                                <td><i class="fas fa-coffee me-2"></i><?php echo $order['table_number']; ?></td>
                                <td><i class="fas fa-clock me-2"></i><?php echo date('H:i', strtotime($order['order_time'])); ?></td>
                                <td><i class="fas fa-dollar-sign me-2"></i>Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-eye me-1"></i>Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($active_orders)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada pesanan aktif</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pesanan -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i>Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function viewOrder(orderId) {
    fetch(`/RestoPay/api/orders/get_order.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.data);
                new bootstrap.Modal(document.getElementById('orderModal')).show();
            }
        });
}

function displayOrderDetails(order) {
    let html = `
        <div class="mb-3">
            <table class="table table-sm">
                <tr>
                    <td width="150">No. Meja</td>
                    <td><i class="fas fa-coffee me-2"></i>${order.table_number}</td>
                </tr>
                <tr>
                    <td>Waktu Pesan</td>
                    <td><i class="fas fa-clock me-2"></i>${new Date(order.order_time).toLocaleString('id-ID')}</td>
                </tr>
            </table>
        </div>
        <div class="mb-3">
            <h6><i class="fas fa-utensils me-2"></i>Item Pesanan:</h6>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    order.items.forEach(item => {
        html += `
            <tr>
                <td>${item.menu_name}</td>
                <td>${item.quantity}</td>
                <td>Rp ${parseInt(item.price).toLocaleString('id-ID')}</td>
                <td>Rp ${(item.quantity * item.price).toLocaleString('id-ID')}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>Rp ${parseInt(order.total).toLocaleString('id-ID')}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    document.getElementById('orderDetails').innerHTML = html;
}

// Refresh halaman setiap 30 detik
setTimeout(function() {
    location.reload();
}, 30000);

function printDashboard() {
    // Tambahkan header laporan
    const header = document.createElement('div');
    header.className = 'print-header show';
    header.innerHTML = `
        <h1 style="font-size: 24px; font-weight: bold;">RESTORAN KAMI</h1>
        <p>Jl. Contoh No. 123, Kota</p>
        <p>Telp: (021) 123456</p>
        <h2 style="margin: 20px 0;">Laporan Dashboard Waiter</h2>
        <p>Waiter: <?php echo $_SESSION['username']; ?></p>
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
</script>
