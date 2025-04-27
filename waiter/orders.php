<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'waiter') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';

// Ambil semua pesanan waiter ini
$stmt = $pdo->prepare("
    SELECT o.*, t.table_number, c.name as customer_name, c.phone as customer_phone,
    (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) as total
    FROM orders o 
    JOIN tables t ON o.table_id = t.id 
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.waiter_id = ?
    ORDER BY o.order_time DESC
    LIMIT 100
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Ambil daftar pelanggan untuk modal
$stmt = $pdo->prepare("SELECT * FROM customers ORDER BY name ASC");
$stmt->execute();
$customers = $stmt->fetchAll();
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

.btn-primary {
    background: var(--primary-color);
    border: none;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
}

.btn-info {
    background-color: var(--info-color);
    border: none;
    color: white;
}

.btn-warning {
    background-color: var(--warning-color);
    border: none;
    color: white;
}

.btn-info:hover {
    background-color: #00b4d8;
    color: white;
}

.btn-warning:hover {
    background-color: #f48c06;
    color: white;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    padding: 0.6rem 1rem;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.form-select {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    padding: 0.6rem 1rem;
}

.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.form-label {
    color: var(--dark-color);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #e9ecef;
}
</style>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="dashboard-title">Daftar Pesanan</h2>
            <a href="tables.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Pesanan Baru
            </a>
        </div>

        <!-- Filter Pesanan -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filter Pesanan</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            <select class="form-select" id="status_filter">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" class="form-control" id="date_filter" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary d-block w-30" onclick="filterOrders()">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Pesanan -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-list me-2"></i>Daftar Pesanan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="ordersTable">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>No. Meja</th>
                                <th>Waktu Pesan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><i class="fas fa-hashtag me-2"></i><?php echo $order['id']; ?></td>
                                <td><i class="fas fa-coffee me-2"></i><?php echo $order['table_number']; ?></td>
                                <td><i class="fas fa-clock me-2"></i><?php echo date('d/m/Y H:i', strtotime($order['order_time'])); ?></td>
                                <td><i class="fas fa-dollar-sign me-2"></i>Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php echo $order['status'] == 'pending' ? 'bg-warning' : 'bg-success'; ?>">
                                        <?php echo $order['status'] == 'pending' ? 'Pending' : 'Selesai'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-eye me-1"></i>Detail
                                    </button>
                                    <?php if ($order['status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-warning" onclick="editOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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

<!-- Modal Edit Pesanan -->
<div class="modal fade" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm">
                    <input type="hidden" id="edit_order_id" name="order_id">
                    <div id="editMenuItems">
                        <!-- Menu items will be loaded here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="updateOrder()">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Pelanggan -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <input type="text" class="form-control" id="searchCustomer" placeholder="Cari pelanggan..." style="width: 250px;">
                        <button type="button" class="btn btn-primary" onclick="showNewCustomerForm()">
                            <i class="fas fa-plus-lg"></i> Pelanggan Baru
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="customerTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>No. HP</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Alamat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo $customer['gender'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                    <td><?php echo htmlspecialchars($customer['address'] ?? '-'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="selectCustomer(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['name']); ?>')">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Pelanggan Baru -->
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pelanggan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newCustomerForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Pelanggan</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Nomor HP</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveNewCustomer()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function filterOrders() {
    const status = document.getElementById('status_filter').value;
    const date = document.getElementById('date_filter').value;
    const table = document.getElementById('ordersTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let show = true;
        const statusCell = rows[i].getElementsByTagName('td')[4];
        const dateCell = rows[i].getElementsByTagName('td')[2];

        if (status && !statusCell.textContent.toLowerCase().includes(status)) {
            show = false;
        }

        if (date) {
            const orderDate = new Date(dateCell.textContent.split(' ')[0].split('/').reverse().join('-'));
            const filterDate = new Date(date);
            if (orderDate.toDateString() !== filterDate.toDateString()) {
                show = false;
            }
        }

        rows[i].style.display = show ? '' : 'none';
    }
}

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

function editOrder(orderId) {
    console.log('Fetching order with ID:', orderId);
    fetch(`/RestoPay/api/orders/get_order.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Raw API Response:', data);
            if (data.success) {
                document.getElementById('edit_order_id').value = orderId;
                displayEditForm(data.data);
                new bootstrap.Modal(document.getElementById('editOrderModal')).show();
            } else {
                console.error('API Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
        });
}

function displayEditForm(order) {
    console.log('Displaying order:', order);
    console.log('Order items:', order.items);

    fetch('/RestoPay/api/menu/get_all.php')
        .then(response => response.json())
        .then(data => {
            console.log('Menu API Response:', data);
            if (data.success) {
                let html = '<div class="mb-3">';
                data.data.forEach(item => {
                    // Cari item pesanan yang sesuai
                    const orderItem = order.items.find(oi => parseInt(oi.menu_id) === parseInt(item.id));
                    const quantity = orderItem ? parseInt(orderItem.quantity) : 0;
                    
                    console.log(`Menu ${item.id} quantity:`, quantity);
                    
                    html += `
                        <div class="row mb-2 align-items-center">
                            <div class="col">
                                <label>${item.name}</label>
                                <br>
                                <small class="text-muted">Rp ${parseInt(item.price).toLocaleString('id-ID')}</small>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2" 
                                            onclick="updateEditQuantity(${item.id}, -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           class="form-control form-control-sm text-center" 
                                           id="edit_qty_${item.id}" 
                                           name="items[${item.id}]" 
                                           value="${quantity}"
                                           onchange="this.value = Math.max(0, parseInt(this.value) || 0)"
                                           style="width: 60px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm ms-2" 
                                            onclick="updateEditQuantity(${item.id}, 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                // Update DOM dan inisialisasi ulang
                const menuItemsContainer = document.getElementById('editMenuItems');
                menuItemsContainer.innerHTML = html;
                
                // Pastikan nilai input terisi
                data.data.forEach(item => {
                    const input = document.getElementById(`edit_qty_${item.id}`);
                    if (input) {
                        const orderItem = order.items.find(oi => parseInt(oi.menu_id) === parseInt(item.id));
                        const quantity = orderItem ? parseInt(orderItem.quantity) : 0;
                        input.value = quantity;
                    }
                });
            }
        })
        .catch(error => {
            console.error('Menu Fetch Error:', error);
        });
}

function updateEditQuantity(menuId, change) {
    const input = document.getElementById(`edit_qty_${menuId}`);
    if (input) {
        let currentValue = parseInt(input.value) || 0;
        let newValue = Math.max(0, currentValue + change);
        input.value = newValue;
        
        // Trigger event change untuk memastikan nilai tersimpan
        const event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);
        
        console.log(`Updated quantity for menu ${menuId} to:`, newValue);
    }
}

function updateOrder() {
    const formData = new FormData(document.getElementById('editOrderForm'));
    const submitBtn = document.querySelector('#editOrderModal .btn-primary');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';

    fetch('/RestoPay/api/orders/update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Pesanan berhasil diupdate',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Gagal mengupdate pesanan'
            });
        }
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Perubahan';
    });
}

function displayOrderDetails(order) {
    let html = `
        <div class="mb-3">
            <table class="table table-sm">
                <tr>
                    <td width="150">ID Pesanan</td>
                    <td><i class="fas fa-hashtag me-2"></i>${order.id}</td>
                </tr>
                <tr>
                    <td>No. Meja</td>
                    <td><i class="fas fa-coffee me-2"></i>${order.table_number}</td>
                </tr>
                <tr>
                    <td>Waktu Pesan</td>
                    <td><i class="fas fa-clock me-2"></i>${new Date(order.order_time).toLocaleString('id-ID')}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td><i class="fas fa-user me-2"></i>${order.customer_name || '-'}</td>
                </tr>
                ${order.customer_phone ? `
                <tr>
                    <td>No. Telepon</td>
                    <td><i class="fas fa-phone me-2"></i>${order.customer_phone}</td>
                </tr>
                ` : ''}
                <tr>
                    <td>Status</td>
                    <td>
                        <span class="badge ${order.status === 'pending' ? 'bg-warning' : 'bg-success'}">
                            ${order.status === 'pending' ? 'Pending' : 'Selesai'}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="mb-3">
            <h6><i class="fas fa-utensils me-2"></i>Item Pesanan:</h6>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    order.items.forEach(item => {
        html += `
            <tr>
                <td>${item.menu_name}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">Rp ${parseInt(item.price).toLocaleString('id-ID')}</td>
                <td class="text-end">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-end">Rp ${parseInt(order.total).toLocaleString('id-ID')}</th>
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

function showCustomerModal() {
    $('#customerModal').modal('show');
}

function showNewCustomerForm() {
    $('#customerModal').modal('hide');
    $('#newCustomerModal').modal('show');
}

function selectCustomer(customerId, customerName) {
    $('#selected_customer_id').val(customerId);
    $('#selected_customer_name').val(customerName);
    $('#customerModal').modal('hide');
}

function saveNewCustomer() {
    const formData = new FormData(document.getElementById('newCustomerForm'));
    
    fetch('../api/customers/save_customer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            selectCustomer(data.customer_id, formData.get('name'));
            $('#newCustomerModal').modal('hide');
            // Refresh halaman untuk memperbarui daftar pelanggan
            location.reload();
        } else {
            alert(data.message || 'Gagal menyimpan data pelanggan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    });
}

// Filter pelanggan
document.getElementById('searchCustomer').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('#customerTable tbody tr');
    
    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        const phone = row.cells[1].textContent.toLowerCase();
        row.style.display = name.includes(searchText) || phone.includes(searchText) ? '' : 'none';
    });
});
</script>
