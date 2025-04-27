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

.badge.bg-makanan {
    background-color: var(--success-color) !important;
}

.badge.bg-minuman {
    background-color: var(--info-color) !important;
}
</style>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="dashboard-title">Manajemen Menu</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuModal">
                <i class="fas fa-plus-circle me-2"></i>Tambah Menu
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM menu ORDER BY category, name");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>{$row['name']}</td>";
                            echo "<td>Rp " . number_format($row['price'], 0, ',', '.') . "</td>";
                            echo "<td><span class='badge bg-{$row['category']}'>" . ucfirst($row['category']) . "</span></td>";
                            echo "<td>
                                    <button class='icon-button edit text-white' onclick='editMenu({$row['id']})' title='Edit Menu'>
                                        <i class='fas fa-pencil-alt'></i>
                                    </button>
                                    <button class='icon-button delete text-white' onclick='deleteMenu({$row['id']})' title='Hapus Menu'>
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

<!-- Modal Tambah Menu -->
<div class="modal fade" id="addMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Tambah Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMenuForm">
                    <div class="mb-3">
                        <label class="form-label">Nama Menu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-utensils"></i></span>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="number" class="form-control" name="price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list"></i></span>
                            <select class="form-select" name="category" required>
                                <option value="makanan">Makanan</option>
                                <option value="minuman">Minuman</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="saveMenu()">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function saveMenu() {
    const form = document.getElementById('addMenuForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#addMenuModal .btn-primary');
    
    submitBtn.classList.add('loading');
    
    fetch('/RestoPay/api/menu/add.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Menu berhasil ditambahkan',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Gagal menambah menu'
            });
        }
    })
    .finally(() => {
        submitBtn.classList.remove('loading');
    });
}

function editMenu(id) {
    fetch(`/RestoPay/api/menu/get.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const menu = data.data;
                
                const modalHtml = `
                    <div class="modal fade" id="editMenuModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Menu</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editMenuForm">
                                        <input type="hidden" name="id" value="${menu.id}">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Menu</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-utensils"></i></span>
                                                <input type="text" class="form-control" name="name" value="${menu.name}" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Harga</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                                <input type="number" class="form-control" name="price" value="${menu.price}" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kategori</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                                <select class="form-select" name="category" required>
                                                    <option value="makanan" ${menu.category === 'makanan' ? 'selected' : ''}>Makanan</option>
                                                    <option value="minuman" ${menu.category === 'minuman' ? 'selected' : ''}>Minuman</option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="updateMenu()">
                                        <i class="fas fa-save me-1"></i>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                const modal = new bootstrap.Modal(document.getElementById('editMenuModal'));
                modal.show();
                
                document.getElementById('editMenuModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            }
        });
}

function updateMenu() {
    const form = document.getElementById('editMenuForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#editMenuModal .btn-primary');
    
    submitBtn.classList.add('loading');
    
    fetch('/RestoPay/api/menu/edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Menu berhasil diupdate',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Gagal mengupdate menu'
            });
        }
    })
    .finally(() => {
        submitBtn.classList.remove('loading');
    });
}

function deleteMenu(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Menu yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4d6d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/RestoPay/api/menu/delete.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Menu berhasil dihapus',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message || 'Gagal menghapus menu'
                    });
                }
            });
        }
    });
}
</script>
