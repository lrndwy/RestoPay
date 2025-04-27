<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    transition: transform 0.2s ease;
    margin-bottom: 1rem;
}

.card:hover {
    transform: translateY(-5px);
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

.btn-warning {
    background: var(--warning-color);
    border: none;
    color: white;
}

.btn-danger {
    background: #ff4d6d;
    border: none;
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

.modal-footer {
    border-top: 1px solid rgba(0,0,0,0.05);
    background: var(--light-color);
    border-radius: 0 0 12px 12px;
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

.icon-button {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
    margin: 0 3px;
}

.icon-button i {
    width: 16px;
    height: 16px;
    color: white;
}

.icon-button:hover {
    transform: translateY(-2px);
}

.icon-button.edit {
    background-color: var(--warning-color);
    border: none;
}

.icon-button.edit:hover {
    background-color: #f48c06;
}

.icon-button.delete {
    background-color: #ff4d6d;
    border: none;
}

.icon-button.delete:hover {
    background-color: #e5383b;
}

.loading {
    position: relative;
    pointer-events: none;
}

.loading:after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    background: rgba(255,255,255,0.8) url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDBweCIgaGVpZ2h0PSI0MHB4IiB2aWV3Qm94PSIwIDAgNDAgNDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjE4IiBzdHJva2U9IiM0MzYxZWUiIHN0cm9rZS13aWR0aD0iNCIgZmlsbD0ibm9uZSI+CiAgICAgICAgPGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJyb3RhdGUiIGZyb209IjAgMjAgMjAiIHRvPSIzNjAgMjAgMjAiIGR1cj0iMXMiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIi8+CiAgICA8L2NpcmNsZT4KPC9zdmc+') center no-repeat;
}
</style>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="dashboard-title">Manajemen Meja</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTableModal">
                <i class="fas fa-plus-circle me-2"></i>Tambah Meja
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nomor Meja</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM tables ORDER BY table_number");
                        while ($row = $stmt->fetch()) {
                            $statusClass = $row['status'] == 'available' ? 'success' : 'warning';
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>{$row['table_number']}</td>";
                            echo "<td><span class='badge bg-{$statusClass}'>" . ucfirst($row['status']) . "</span></td>";
                            echo "<td>
                                    <button class='icon-button edit text-white' onclick='editTable({$row['id']})' title='Edit Meja'>
                                        <i class='fas fa-pencil-alt'></i>
                                    </button>
                                    <button class='icon-button delete text-white' onclick='deleteTable({$row['id']})' title='Hapus Meja'>
                                        <i class='fas fa-trash-alt'></i>
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

<!-- Modal Tambah Meja -->
<div class="modal fade" id="addTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Tambah Meja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addTableForm">
                    <div class="mb-3">
                        <label class="form-label">Nomor Meja</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                            <input type="text" class="form-control" name="table_number" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            <select class="form-select" name="status">
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="saveTable()">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function saveTable() {
    const form = document.getElementById('addTableForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#addTableModal .btn-primary');
    
    submitBtn.classList.add('loading');
    
    fetch('/RestoPay/api/tables/add.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Meja berhasil ditambahkan',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Gagal menambah meja'
            });
        }
    })
    .finally(() => {
        submitBtn.classList.remove('loading');
    });
}

function editTable(id) {
    fetch(`/RestoPay/api/tables/get.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const table = data.data;
                
                const modalHtml = `
                    <div class="modal fade" id="editTableModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Meja</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editTableForm">
                                        <input type="hidden" name="id" value="${table.id}">
                                        <div class="mb-3">
                                            <label class="form-label">Nomor Meja</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                <input type="text" class="form-control" name="table_number" value="${table.table_number}" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                                <select class="form-select" name="status">
                                                    <option value="available" ${table.status === 'available' ? 'selected' : ''}>Available</option>
                                                    <option value="occupied" ${table.status === 'occupied' ? 'selected' : ''}>Occupied</option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="updateTable()">
                                        <i class="fas fa-save me-1"></i>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                const modal = new bootstrap.Modal(document.getElementById('editTableModal'));
                modal.show();
                
                document.getElementById('editTableModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            }
        });
}

function updateTable() {
    const form = document.getElementById('editTableForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#editTableModal .btn-primary');
    
    submitBtn.classList.add('loading');
    
    fetch('/RestoPay/api/tables/edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Meja berhasil diupdate',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Gagal mengupdate meja'
            });
        }
    })
    .finally(() => {
        submitBtn.classList.remove('loading');
    });
}

function deleteTable(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Meja yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4d6d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/RestoPay/api/tables/delete.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Meja berhasil dihapus',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message || 'Gagal menghapus meja'
                    });
                }
            });
        }
    });
}
</script>
