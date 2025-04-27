<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';
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
    margin-bottom: 1.5rem;
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 1.5rem;
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

.order-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5em 0.8em;
    border-radius: 6px;
    font-weight: 500;
}

.status-pending {
    background-color: rgba(255, 159, 28, 0.1);
    color: var(--warning-color);
}

.status-completed {
    background-color: rgba(46, 196, 182, 0.1);
    color: var(--success-color);
}

.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.modal-header {
    background-color: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    background-color: rgba(0, 0, 0, 0.03);
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem 1.5rem;
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

.input-group-text {
    background-color: #f8f9fa;
    border-color: #e9ecef;
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
                <i class="fas fa-clipboard-list me-2"></i>Daftar Pesanan
            </h2>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="ordersTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID Pesanan</th>
                                <th><i class="fas fa-coffee me-2"></i>Nomor Meja</th>
                                <th><i class="fas fa-user me-2"></i>Pelayan</th>
                                <th><i class="fas fa-clock me-2"></i>Waktu Pesan</th>
                                <th><i class="fas fa-dollar-sign me-2"></i>Total</th>
                                <th><i class="fas fa-exclamation-circle me-2"></i>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT o.*, t.table_number, u.username as waiter_name,
                                     (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) as total
                                     FROM orders o 
                                     JOIN tables t ON o.table_id = t.id 
                                     LEFT JOIN users u ON o.waiter_id = u.id
                                     WHERE o.status = 'pending'
                                     ORDER BY o.order_time DESC";
                            $stmt = $pdo->query($query);
                            while ($row = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>{$row['table_number']}</td>";
                                echo "<td>{$row['waiter_name']}</td>";
                                echo "<td>" . date('d/m/Y H:i', strtotime($row['order_time'])) . "</td>";
                                echo "<td>Rp " . number_format($row['total'], 0, ',', '.') . "</td>";
                                echo "<td><span class='order-status status-pending'><i class='fas fa-clock'></i>Pending</span></td>";
                                echo "<td>
                                        <button class='btn btn-sm btn-primary' onclick='processPayment({$row['id']}, {$row['total']})'>
                                            <i class='fas fa-credit-card me-1'></i>
                                            Proses Pembayaran
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-credit-card me-2"></i>
                    Proses Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="order_id" name="order_id">
                    <input type="hidden" id="total_amount" name="total_amount">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="mb-3">
                                <i class="fas fa-clipboard-list me-2"></i>
                                Detail Pesanan
                            </h6>
                            <div id="orderDetails"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Total Pembayaran
                                </label>
                                <input type="text" class="form-control" id="display_total" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Metode Pembayaran
                                </label>
                                <select class="form-select" name="payment_method" id="payment_method" required>
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
                            <div class="mb-3" id="cashPaymentSection">
                                <label class="form-label">
                                    <i class="fas fa-wallet me-2"></i>
                                    Jumlah Uang
                                </label>
                                <input type="number" class="form-control" name="cash_amount" id="cash_amount">
                                <div class="mt-2">
                                    <span>Kembalian: </span>
                                    <span id="changeAmount">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="submitPayment()">
                    <i class="fas fa-check me-1"></i>Proses Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let currentTotal = 0;

function processPayment(orderId, total) {
    currentTotal = total;
    document.getElementById('order_id').value = orderId;
    document.getElementById('total_amount').value = total;
    document.getElementById('display_total').value = `Rp ${total.toLocaleString('id-ID')}`;
    
    // Ambil detail pesanan
    fetch(`/RestoPay/api/orders/get_order.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.data);
                new bootstrap.Modal(document.getElementById('paymentModal')).show();
            }
        });
}

function displayOrderDetails(order) {
    let html = `
        <table class="table table-sm">
            <tr>
                <td width="150">No. Meja</td>
                <td><i class="fas fa-coffee me-2"></i>${order.table_number}</td>
            </tr>
            <tr>
                <td>Waktu Pesan</td>
                <td><i class="fas fa-clock me-2"></i>${new Date(order.order_time).toLocaleString('id-ID')}</td>
            </tr>
            <tr>
                <td>Pelayan</td>
                <td><i class="fas fa-user me-2"></i>${order.waiter_name}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td><i class="fas fa-user me-2"></i>${order.customer_name}</td>
            </tr>
            ${order.customer_phone ? `
            <tr>
                <td>No. Telepon</td>
                <td><i class="fas fa-phone me-2"></i>${order.customer_phone}</td>
            </tr>
            ` : ''}
        </table>
        <h6 class="mt-3">
            <i class="fas fa-utensils me-2"></i>
            Item Pesanan:
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
    `;
    
    document.getElementById('orderDetails').innerHTML = html;
}

// Event listener untuk metode pembayaran
document.getElementById('payment_method').addEventListener('change', function() {
    const cashSection = document.getElementById('cashPaymentSection');
    cashSection.style.display = this.value === 'cash' ? 'block' : 'none';
});

// Event listener untuk perhitungan kembalian
document.getElementById('cash_amount').addEventListener('input', function() {
    const cashAmount = parseInt(this.value) || 0;
    const change = cashAmount - currentTotal;
    document.getElementById('changeAmount').textContent = `Rp ${change.toLocaleString('id-ID')}`;
});

function submitPayment() {
    const formData = new FormData(document.getElementById('paymentForm'));
    const submitBtn = document.querySelector('#paymentModal .btn-primary');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
    
    // Validasi pembayaran tunai
    if (formData.get('payment_method') === 'cash') {
        const cashAmount = parseInt(formData.get('cash_amount')) || 0;
        if (cashAmount < currentTotal) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Jumlah uang tunai kurang dari total pembayaran'
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Proses Pembayaran';
            return;
        }
    }

    fetch('/RestoPay/api/orders/process_payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.change_amount > 0 ? 
                    `Pembayaran berhasil!\nKembalian: Rp ${data.change_amount.toLocaleString('id-ID')}` : 
                    'Pembayaran berhasil!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Gagal memproses pembayaran'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan saat memproses pembayaran'
        });
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Proses Pembayaran';
    });
}

// Refresh halaman setiap 30 detik
setTimeout(function() {
    location.reload();
}, 30000);
</script>
