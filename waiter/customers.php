<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'waiter') {
    header("Location: /RestoPay/");
    exit();
}

require_once '../config/database.php';

// Handle Delete
if (isset($_POST['delete'])) {
    try {
        $customer_id = $_POST['customer_id'];
        
        // Check if customer is used in any orders
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE customer_id = ?");
        $stmt->execute([$customer_id]);
        $result = $stmt->fetchAll();
        
        if (count($result) > 0) {
            $delete_error = "Pelanggan tidak dapat dihapus karena memiliki riwayat pesanan.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            if ($stmt->execute([$customer_id])) {
                $success_message = "Pelanggan berhasil dihapus.";
            } else {
                $error_message = "Gagal menghapus pelanggan.";
            }
        }
    } catch(PDOException $e) {
        $error_message = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Fetch all customers
$stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC");
$customers = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- CSS kustom -->
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

/* Update style untuk tabel */
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

.btn-sm {
    padding: 0.4rem 1rem;
    font-size: 0.875rem;
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
    
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="dashboard-title">Data Pelanggan</h2>
            <button class="btn btn-primary" onclick="showAddModal()">
                <i class="fas fa-plus"></i> Tambah Pelanggan
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Jenis Kelamin</th>
                                <th>Alamat</th>
                                <th>Tgl Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (count($customers) > 0):
                                $no = 1;
                                foreach ($customers as $customer):
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($customer['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCustomer(<?php echo htmlspecialchars(json_encode($customer)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteCustomer(<?php echo $customer['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data pelanggan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Pelanggan -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customerForm">
                    <input type="hidden" name="customer_id" id="customerId">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="tel" class="form-control" name="phone" id="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="gender" id="gender" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="address" id="address" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomer()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let customerModal = new bootstrap.Modal(document.getElementById('customerModal'));

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Pelanggan';
    document.getElementById('customerForm').reset();
    document.getElementById('customerId').value = '';
    customerModal.show();
}

function editCustomer(customer) {
    document.getElementById('modalTitle').textContent = 'Edit Pelanggan';
    document.getElementById('customerId').value = customer.id;
    document.getElementById('name').value = customer.name;
    document.getElementById('phone').value = customer.phone;
    document.getElementById('gender').value = customer.gender;
    document.getElementById('address').value = customer.address || '';
    customerModal.show();
}

function saveCustomer() {
    const form = document.getElementById('customerForm');
    const formData = new FormData(form);
    
    // Tampilkan loading
    Swal.fire({
        title: 'Menyimpan...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('../api/customers/save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
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
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat menyimpan data'
        });
    });
}

function deleteCustomer(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data pelanggan yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4d6d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../api/customers/delete.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message
                    });
                }
            });
        }
    });
}
</script> 