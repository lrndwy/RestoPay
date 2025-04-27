<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';

// Ambil data transaksi default (7 hari terakhir)
$stmt = $pdo->prepare("
    SELECT t.*, o.table_id, tb.table_number, 
           u_waiter.username as waiter_name,
           o.waiter_id
    FROM transactions t
    JOIN orders o ON t.order_id = o.id
    JOIN tables tb ON o.table_id = tb.id
    LEFT JOIN users u_waiter ON o.waiter_id = u_waiter.id
    WHERE t.kasir_id = ? 
    AND DATE(t.transaction_time) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    ORDER BY t.transaction_time DESC
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

// Hitung summary untuk hari ini
$summary = [
    'total_transactions' => count($transactions),
    'total_income' => array_sum(array_column($transactions, 'total')),
    'average_transaction' => count($transactions) > 0 ? 
        array_sum(array_column($transactions, 'total')) / count($transactions) : 0
];
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

.payment-method-icon {
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
    color: var(--primary-color);
}
</style>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="dashboard-title">
                <i class="fas fa-receipt me-2"></i>Riwayat Transaksi
            </h2>

        </div>

        <!-- Filter Transaksi -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar me-2"></i>
                            Tanggal Mulai
                        </label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo date('Y-m-d', strtotime('-6 days')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar me-2"></i>
                            Tanggal Akhir
                        </label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-credit-card me-2"></i>
                            Metode Pembayaran
                        </label>
                        <select class="form-select" id="payment_method" name="payment_method">
                            <option value="">Semua</option>
                            <option value="cash">
                                <i class="fas fa-wallet me-2"></i>
                                Tunai
                            </option>
                            <option value="debit">
                                <i class="fas fa-credit-card me-2"></i>
                                Debit
                            </option>
                            <option value="credit">
                                <i class="fas fa-credit-card me-2"></i>
                                Kartu Kredit
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary d-block" onclick="filterTransactions()">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ringkasan Transaksi -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white info-card">
                    <div class="card-body">
                        <h5>
                            <i class="fas fa-receipt me-2"></i>
                            Total Transaksi
                        </h5>
                        <h3 id="totalTransactions"><?php echo $summary['total_transactions']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white info-card">
                    <div class="card-body">
                        <h5>
                            <i class="fas fa-dollar-sign me-2"></i>
                            Total Pendapatan
                        </h5>
                        <h3 id="totalIncome">Rp <?php echo number_format($summary['total_income'], 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white info-card">
                    <div class="card-body">
                        <h5>
                            <i class="fas fa-chart-line me-2"></i>
                            Rata-rata Transaksi
                        </h5>
                        <h3 id="averageTransaction">Rp <?php echo number_format($summary['average_transaction'], 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Transaksi -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID Transaksi</th>
                                <th><i class="fas fa-coffee me-2"></i>No. Meja</th>
                                <th><i class="fas fa-user me-2"></i>Pelayan</th>
                                <th><i class="fas fa-dollar-sign me-2"></i>Total</th>
                                <th><i class="fas fa-credit-card me-2"></i>Metode Pembayaran</th>
                                <th><i class="fas fa-clock me-2"></i>Waktu Transaksi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsBody">
                            <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="text-center"><?php echo $t['id']; ?></td>
                                <td class="text-center"><?php echo $t['table_number']; ?></td>
                                <td class="text-center"><?php echo $t['waiter_name']; ?></td>
                                <td class="text-center">Rp <?php echo number_format($t['total'], 0, ',', '.'); ?></td>
                                <td class="text-center"><?php echo $t['payment_method']; ?></td>
                                <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($t['transaction_time'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" onclick="viewTransaction(<?php echo $t['id']; ?>)">
                                        <i class="fas fa-eye me-1"></i>
                                        Detail
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="printReceipt(<?php echo $t['id']; ?>)">
                                        <i class="fas fa-print me-1"></i>
                                        Cetak
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada transaksi</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt me-2"></i>
                    Detail Transaksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-1"></i>Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let currentTransactionId = null;

function filterTransactions() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const paymentMethod = document.getElementById('payment_method').value;
    const filterBtn = document.querySelector('#filterForm .btn-primary');
    
    filterBtn.disabled = true;
    filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

    fetch(`/RestoPay/api/transactions/get_filtered.php?start_date=${startDate}&end_date=${endDate}&payment_method=${paymentMethod}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateTransactionTable(data.data);
                updateSummary(data.summary);
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat memfilter transaksi'
            });
        })
        .finally(() => {
            filterBtn.disabled = false;
            filterBtn.innerHTML = '<i class="fas fa-filter me-2"></i>Filter';
        });
}

function updateTransactionTable(transactions) {
    const tbody = document.getElementById('transactionsBody');
    tbody.innerHTML = '';

    transactions.forEach(t => {
        tbody.innerHTML += `
            <tr>
                <td>${t.id}</td>
                <td>${t.table_number}</td>
                <td>${t.waiter_name}</td>
                <td>Rp ${parseInt(t.total).toLocaleString('id-ID')}</td>
                <td>
                    ${t.payment_method.toUpperCase()}
                </td>
                <td>${new Date(t.transaction_time).toLocaleString('id-ID')}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewTransaction(${t.id})">
                        <i class="fas fa-eye me-1"></i>
                        Detail
                    </button>
                    <button class="btn btn-sm btn-success" onclick="printReceipt(${t.id})">
                        <i class="fas fa-print me-1"></i>
                        Cetak
                    </button>
                </td>
            </tr>
        `;
    });

    if (transactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">Tidak ada transaksi</td>
            </tr>
        `;
    }
}

function updateSummary(summary) {
    document.getElementById('totalTransactions').textContent = summary.total_transactions;
    document.getElementById('totalIncome').textContent = `Rp ${parseInt(summary.total_income).toLocaleString('id-ID')}`;
    document.getElementById('averageTransaction').textContent = `Rp ${parseInt(summary.average_transaction).toLocaleString('id-ID')}`;
}

function viewTransaction(transactionId) {
    currentTransactionId = transactionId;
    const viewBtn = document.querySelector(`button[onclick="viewTransaction(${transactionId})"]`);
    
    viewBtn.disabled = true;
    viewBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memuat...';
    
    fetch(`/RestoPay/api/transactions/get_transaction.php?id=${transactionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTransactionDetails(data.data);
                new bootstrap.Modal(document.getElementById('transactionModal')).show();
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat memuat detail transaksi'
            });
        })
        .finally(() => {
            viewBtn.disabled = false;
            viewBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Detail';
        });
}

function displayTransactionDetails(transaction) {
    let html = `
        <div class="mb-3">
            <h6 class="mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Informasi Transaksi
            </h6>
            <table class="table table-sm">
                <tr>
                    <td width="150">ID Transaksi</td>
                    <td><i class="fas fa-hashtag me-2"></i>${transaction.id}</td>
                </tr>
                <tr>
                    <td>No. Meja</td>
                    <td><i class="fas fa-coffee me-2"></i>${transaction.table_number}</td>
                </tr>
                <tr>
                    <td>Pelayan</td>
                    <td><i class="fas fa-user me-2"></i>${transaction.waiter_name}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td><i class="fas fa-user me-2"></i>${transaction.customer_name}</td>
                </tr>
                ${transaction.customer_phone ? `
                <tr>
                    <td>No. Telepon</td>
                    <td><i class="fas fa-phone me-2"></i>${transaction.customer_phone}</td>
                </tr>
                ` : ''}
                <tr>
                    <td>Waktu Transaksi</td>
                    <td><i class="fas fa-clock me-2"></i>${new Date(transaction.transaction_time).toLocaleString('id-ID')}</td>
                </tr>
                <tr>
                    <td>Metode Pembayaran</td>
                    <td>
                        <span class="payment-method-icon">
                            <i class="fas fa-${transaction.payment_method === 'cash' ? 'wallet' : 'credit-card'} me-2"></i>
                        </span>
                        ${transaction.payment_method.toUpperCase()}
                    </td>
                </tr>
            </table>
        </div>

        <div class="mb-3">
            <h6 class="mb-3">
                <i class="fas fa-utensils me-2"></i>
                Detail Pesanan
            </h6>
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

    transaction.items.forEach(item => {
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
                        <th>Rp ${parseInt(transaction.total).toLocaleString('id-ID')}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

    document.getElementById('transactionDetails').innerHTML = html;
}

function printReceipt(transactionId = null) {
    if (transactionId) {
        currentTransactionId = transactionId;
    }
    
    if (currentTransactionId) {
        window.open(`/RestoPay/print_receipt.php?id=${currentTransactionId}`, '_blank');
    }
}

// Load transaksi hari ini saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    filterTransactions();
});
</script>
