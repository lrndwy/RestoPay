<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'waiter') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';

// Ambil daftar meja
$stmt = $pdo->query("SELECT * FROM tables ORDER BY table_number");
$tables = $stmt->fetchAll();

// Ambil daftar menu
$stmt = $pdo->query("SELECT * FROM menu ORDER BY category, name");
$menu_items = $stmt->fetchAll();
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

.table-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 1rem 0;
}

.table-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    transition: transform 0.2s ease;
    overflow: hidden;
    cursor: pointer;
}

.table-card:hover {
    transform: translateY(-5px);
}

.table-card.occupied {
    border-left: 4px solid var(--warning-color);
}

.table-card.available {
    border-left: 4px solid var(--success-color);
}

.table-header {
    padding: 1rem;
    background: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.table-header h5 {
    margin: 0;
    color: var(--dark-color);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-body {
    padding: 1rem;
}

.table-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.9rem;
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
}

.card-body {
    padding: 1.5rem;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.menu-item {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.menu-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
}

.menu-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.menu-item-name {
    font-weight: 500;
    color: var(--dark-color);
    margin: 0;
}

.menu-item-price {
    color: var(--primary-color);
    font-weight: 600;
}

.menu-item-category {
    color: #6c757d;
    font-size: 0.875rem;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.quantity-control .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.quantity-control input {
    width: 60px;
    text-align: center;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 0.25rem;
}

.btn-primary {
    background: var(--primary-color);
    border: none;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
}

.btn-success {
    background: var(--success-color);
    border: none;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

.cart-total {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
</style>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="dashboard-title">Pilih Meja</h2>
            <a href="orders.php" class="btn btn-primary">
                <i class="fas fa-list me-2"></i>Lihat Pesanan
            </a>
        </div>

        <div class="table-grid">
            <?php foreach ($tables as $table): ?>
            <div class="table-card <?php echo $table['status'] == 'occupied' ? 'occupied' : 'available'; ?>"
                 <?php if($table['status'] != 'occupied'): ?>
                 onclick="selectTable(<?php echo $table['id']; ?>, <?php echo $table['table_number']; ?>)"
                 <?php endif; ?>
                 style="<?php echo $table['status'] == 'occupied' ? 'cursor: not-allowed;' : ''; ?>">
                <div class="table-header">
                    <h5>
                        <i class="fas fa-coffee me-2"></i>
                        Meja <?php echo $table['table_number']; ?>
                    </h5>
                </div>
                <div class="table-body">
                    <div class="table-status">
                        <i class="fas <?php echo $table['status'] == 'occupied' ? 'fa-users' : 'fa-user-check'; ?>"></i>
                        <?php echo $table['status'] == 'occupied' ? 'Terisi' : 'Tersedia'; ?>
                    </div>
                    <?php if($table['status'] == 'occupied'): ?>
                    <div class="mt-2 text-danger">
                        <small><i>Meja sedang terisi, tidak dapat menambah pesanan baru</i></small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal Pilih Meja -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pesanan - Meja <span id="selectedTableNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Tab untuk pilih pelanggan -->
                <ul class="nav nav-tabs mb-3" id="customerTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="existing-customer-tab" data-bs-toggle="tab" 
                                data-bs-target="#existing-customer" type="button" role="tab">
                            Pelanggan Terdaftar
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="new-customer-tab" data-bs-toggle="tab" 
                                data-bs-target="#new-customer" type="button" role="tab">
                            Pelanggan Baru
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="customerTabContent">
                    <!-- Tab Pelanggan Terdaftar -->
                    <div class="tab-pane fade show active" id="existing-customer" role="tabpanel">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="searchCustomer" 
                                   placeholder="Cari pelanggan berdasarkan nama atau nomor HP...">
                        </div>
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-hover" id="customerTable">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>No. HP</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data pelanggan akan diisi melalui AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Pelanggan Baru -->
                    <div class="tab-pane fade" id="new-customer" role="tabpanel">
                        <form id="newCustomerForm">
                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <select class="form-select" name="gender" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="address" rows="2"></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <hr>

                <!-- Form Pesanan -->
                <div id="orderForm" style="display: none;">
                    <h6 class="mb-3">Detail Pesanan</h6>
                    <div class="menu-grid">
                        <?php foreach ($menu_items as $item): ?>
                        <div class="menu-item">
                            <div class="menu-item-header">
                                <h6 class="menu-item-name"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <span class="menu-item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="menu-item-category"><?php echo htmlspecialchars($item['category']); ?></div>
                            <div class="quantity-control">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                <input type="number" min="0" value="0" 
                                       class="menu-quantity" data-id="<?php echo $item['id']; ?>"
                                       data-price="<?php echo $item['price']; ?>"
                                       onchange="updateTotal()">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="cart-total">
                        Total: <span id="orderTotal">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveOrder">Simpan Pesanan</button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let selectedTableId = null;
let selectedCustomerId = null;
let menuItems = <?php echo json_encode($menu_items); ?>;
let orderTotal = 0;

function selectTable(tableId, tableNumber) {
    selectedTableId = tableId;
    document.getElementById('selectedTableNumber').textContent = tableNumber;
    loadCustomers();
    resetOrderForm();
    new bootstrap.Modal(document.getElementById('orderModal')).show();
}

function loadCustomers() {
    fetch('../api/customers/get_customers.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
            if (!result.success) {
                throw new Error(result.message || 'Gagal mengambil data pelanggan');
            }
            
            const customers = result.data;
            const tbody = document.querySelector('#customerTable tbody');
            tbody.innerHTML = '';
            
            if (customers.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada data pelanggan</td>
                    </tr>
                `;
                return;
            }

            customers.forEach(customer => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${customer.name}</td>
                    <td>${customer.phone}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="selectCustomer(${customer.id}, '${customer.name}')">
                            Pilih
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            // Simpan data pelanggan ke variabel global untuk pencarian
            window.customersData = customers;
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat data pelanggan'
            });
        });
}

function selectCustomer(customerId, customerName) {
    // Hapus info pelanggan yang sebelumnya dipilih (jika ada)
    const existingInfo = document.querySelector('#orderForm .alert-info');
    if (existingInfo) {
        existingInfo.remove();
    }

    // Reset semua tombol ke keadaan awal terlebih dahulu
    document.querySelectorAll('#customerTable .btn').forEach(btn => {
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-primary');
        btn.disabled = false;
        btn.textContent = 'Pilih';
    });

    // Nonaktifkan tombol yang dipilih
    const selectedButton = event.target;
    selectedButton.classList.remove('btn-primary');
    selectedButton.classList.add('btn-secondary');
    selectedButton.disabled = true;
    selectedButton.textContent = 'Dipilih';

    selectedCustomerId = customerId;
    document.getElementById('orderForm').style.display = 'block';
    
    // Tampilkan nama pelanggan yang dipilih
    const customerInfo = document.createElement('div');
    customerInfo.className = 'alert alert-info';
    customerInfo.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <span>Pelanggan: <strong>${customerName}</strong></span>
            <button type="button" class="btn-close" onclick="resetCustomerSelection()"></button>
        </div>
    `;
    document.getElementById('orderForm').prepend(customerInfo);
}

function resetCustomerSelection() {
    selectedCustomerId = null;
    
    // Reset semua tombol ke keadaan awal
    document.querySelectorAll('#customerTable .btn').forEach(btn => {
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-primary');
        btn.disabled = false;
        btn.textContent = 'Pilih';
    });

    // Hapus info pelanggan
    const customerInfo = document.querySelector('#orderForm .alert-info');
    if (customerInfo) {
        customerInfo.remove();
    }

    // Reset form pesanan
    resetOrderForm();
}

function updateQuantity(menuId, change) {
    const input = document.querySelector(`.menu-quantity[data-id="${menuId}"]`);
    let value = parseInt(input.value) + change;
    if (value < 0) value = 0;
    input.value = value;
    updateTotal();
}

function updateTotal() {
    orderTotal = 0;
    menuItems.forEach(item => {
        const qty = parseInt(document.querySelector(`.menu-quantity[data-id="${item.id}"]`).value);
        orderTotal += qty * item.price;
    });
    document.getElementById('orderTotal').textContent = `Rp ${orderTotal.toLocaleString('id-ID')}`;
}

function resetOrderForm() {
    selectedCustomerId = null;
    document.querySelectorAll('.menu-quantity').forEach(input => input.value = 0);
    document.getElementById('orderForm').style.display = 'none';
    document.getElementById('orderTotal').textContent = 'Rp 0';
    document.getElementById('newCustomerForm').reset();
}

// Perbaikan fungsi pencarian
document.getElementById('searchCustomer').addEventListener('input', function(e) {
    const searchText = e.target.value.toLowerCase();
    const tbody = document.querySelector('#customerTable tbody');
    tbody.innerHTML = ''; // Kosongkan tabel

    // Filter dan tampilkan hasil pencarian
    window.customersData.forEach(customer => {
        if (
            customer.name.toLowerCase().includes(searchText) || 
            customer.phone.toLowerCase().includes(searchText)
        ) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${customer.name}</td>
                <td>${customer.phone}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="selectCustomer(${customer.id}, '${customer.name}')">
                        Pilih
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }
    });
});

// Save order
document.getElementById('saveOrder').addEventListener('click', function() {
    let customerId = selectedCustomerId;
    let customerData = null;
    
    // Jika tab pelanggan baru aktif
    if (document.getElementById('new-customer-tab').classList.contains('active')) {
        const form = document.getElementById('newCustomerForm');
        customerData = {
            name: form.elements['name'].value,
            phone: form.elements['phone'].value,
            gender: form.elements['gender'].value,
            address: form.elements['address'].value
        };
        
        if (!customerData.name || !customerData.phone || !customerData.gender) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Mohon lengkapi data pelanggan'
            });
            return;
        }
    } else if (!customerId) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Mohon pilih pelanggan'
        });
        return;
    }
    
    // Collect order items
    const items = [];
    document.querySelectorAll('.menu-quantity').forEach(input => {
        if (parseInt(input.value) > 0) {
            items.push({
                menu_id: input.dataset.id,
                quantity: parseInt(input.value),
                price: parseInt(input.dataset.price)
            });
        }
    });
    
    if (items.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Mohon pilih menu pesanan'
        });
        return;
    }
    
    // Save order
    fetch('../api/orders/save_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            table_id: selectedTableId,
            customer_id: customerId,
            customer_data: customerData,
            items: items
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Terjadi kesalahan pada server');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data.message || 'Gagal menyimpan pesanan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Terjadi kesalahan saat menyimpan pesanan'
        });
    });
});

// Modifikasi event listener untuk tab pelanggan baru
document.getElementById('new-customer-tab').addEventListener('click', function() {
    // Reset pilihan pelanggan yang ada
    resetCustomerSelection();
    showNewCustomerForm();
});

// Tambahkan event listener untuk tab pelanggan terdaftar
document.getElementById('existing-customer-tab').addEventListener('click', function() {
    // Reset form pelanggan baru
    document.getElementById('newCustomerForm').reset();
    // Sembunyikan form pesanan jika ada
    document.getElementById('orderForm').style.display = 'none';
    // Hapus info pelanggan jika ada
    const customerInfo = document.querySelector('#orderForm .alert-info');
    if (customerInfo) {
        customerInfo.remove();
    }
});

function showNewCustomerForm() {
    // Reset form pesanan sebelumnya jika ada
    resetOrderForm();
    // Tampilkan form pesanan
    document.getElementById('orderForm').style.display = 'block';
    // Tambahkan placeholder untuk info pelanggan baru
    const customerInfo = document.createElement('div');
    customerInfo.className = 'alert alert-info';
    customerInfo.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <span>Pelanggan: <strong>Pelanggan Baru</strong></span>
            <button type="button" class="btn-close" onclick="resetNewCustomerForm()"></button>
        </div>
    `;
    document.getElementById('orderForm').prepend(customerInfo);
}

// Tambahkan fungsi untuk reset form pelanggan baru
function resetNewCustomerForm() {
    document.getElementById('newCustomerForm').reset();
    document.getElementById('orderForm').style.display = 'none';
    const customerInfo = document.querySelector('#orderForm .alert-info');
    if (customerInfo) {
        customerInfo.remove();
    }
}
</script>
