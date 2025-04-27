<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /RestoPay/");
    exit;
}
include '../includes/header.php';

// Default periode (bulan ini)
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
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

.btn-secondary {
    background: #6c757d;
    border: none;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
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

.form-label {
    color: var(--dark-color);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.summary-card {
    position: relative;
    overflow: hidden;
}

.summary-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    pointer-events: none;
}

.summary-card .card-body {
    position: relative;
    z-index: 1;
}

.summary-card h5 {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.summary-card h3 {
    font-size: 2rem;
    font-weight: 600;
    margin: 0;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-buttons .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
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

<!-- Tambahkan Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="dashboard-title">Laporan Penjualan</h2>
            <div class="action-buttons">
                <button type="button" class="btn btn-primary" onclick="printReport()">
                    <i class="fas fa-print me-1"></i>Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Filter Periode -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-calendar me-2"></i>Filter Periode</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary d-block w-100" onclick="generateReport()">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ringkasan Laporan -->
        <div class="row mb-4" id="reportSummary">
            <!-- Akan diisi oleh JavaScript -->
        </div>

        <!-- Detail Transaksi -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title"><i class="fas fa-list me-2"></i>Detail Transaksi</h5>
                <h5 class="card-title"><i data-lucide="list" class="me-2"></i>Detail Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>ID Transaksi</th>
                                <th>Kasir</th>
                                <th>Pelayan</th>
                                <th>No. Meja</th>
                                <th>Total</th>
                                <th>Metode Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsBody">
                            <!-- Akan diisi oleh JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Tambahkan Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<script>
    function generateReport() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        fetch(`/RestoPay/api/reports/get_report.php?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateReportSummary(data.summary);
                    updateTransactionsTable(data.transactions);
                }
            });
    }

    function updateReportSummary(summary) {
        const html = `
            <div class="col-md-3">
                <div class="card summary-card bg-primary text-white">
                    <div class="card-body">
                        <h5><i data-lucide="shopping-cart" class="me-2"></i>Total Transaksi</h5>
                        <h3>${summary.total_transactions}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-success text-white">
                    <div class="card-body">
                        <h5><i data-lucide="dollar-sign" class="me-2"></i>Total Pendapatan</h5>
                        <h3>Rp ${parseInt(summary.total_income).toLocaleString('id-ID')}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-info text-white">
                    <div class="card-body">
                        <h5><i data-lucide="trending-up" class="me-2"></i>Rata-rata Transaksi</h5>
                        <h3>Rp ${parseInt(summary.average_transaction).toLocaleString('id-ID')}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-warning text-white">
                    <div class="card-body">
                        <h5><i data-lucide="package" class="me-2"></i>Total Menu Terjual</h5>
                        <h3>${summary.total_items}</h3>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('reportSummary').innerHTML = html;
        lucide.createIcons();
    }

    function updateTransactionsTable(transactions) {
        const tbody = document.getElementById('transactionsBody');
        tbody.innerHTML = '';

        transactions.forEach(t => {
            tbody.innerHTML += `
                <tr>
                    <td><i data-lucide="calendar" class="me-2"></i>${new Date(t.transaction_time).toLocaleDateString('id-ID')}</td>
                    <td><i data-lucide="hash" class="me-2"></i>${t.id}</td>
                    <td><i data-lucide="user" class="me-2"></i>${t.kasir_name}</td>
                    <td><i data-lucide="user" class="me-2"></i>${t.waiter_name}</td>
                    <td><i data-lucide="coffee" class="me-2"></i>${t.table_number}</td>
                    <td><i data-lucide="dollar-sign" class="me-2"></i>Rp ${parseInt(t.total).toLocaleString('id-ID')}</td>
                    <td><i data-lucide="${t.payment_method === 'cash' ? 'dollar-sign' : 'credit-card'}" class="me-2"></i>${t.payment_method.toUpperCase()}</td>
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

        lucide.createIcons();
    }

    function downloadPDF() {
        const startDate = document.getElementById('start_date')?.value || '';
        const endDate = document.getElementById('end_date')?.value || '';
        const category = document.getElementById('category')?.value || '';
        
        let url = `/RestoPay/generate_pdf.php?page=${getCurrentPage()}`;
        if (startDate) url += `&start_date=${startDate}`;
        if (endDate) url += `&end_date=${endDate}`;
        if (category) url += `&category=${category}`;
        
        window.open(url, '_blank');
    }

    function getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('index.php')) return 'dashboard';
        if (path.includes('reports.php')) return 'reports';
        if (path.includes('sales.php')) return 'sales';
        return '';
    }

    function exportToExcel() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        window.location.href = `/RestoPay/api/reports/export_excel.php?start_date=${startDate}&end_date=${endDate}`;
    }

    function printReport() {
        // Mengambil tanggal default dari PHP
        const defaultStartDate = '<?php echo $start_date; ?>';
        const defaultEndDate = '<?php echo $end_date; ?>';
        
        // Menggunakan nilai input jika ada, jika tidak gunakan default
        const startDate = document.getElementById('start_date').value || defaultStartDate;
        const endDate = document.getElementById('end_date').value || defaultEndDate;

        // Menyembunyikan elemen yang tidak perlu saat print
        const elementsToHide = document.querySelectorAll('.action-buttons, .card-header, .form-control, .btn, .card:has(#filterForm)');
        elementsToHide.forEach(el => el.style.display = 'none');

        // Menambahkan judul laporan
        const reportTitle = document.createElement('div');
        reportTitle.className = 'text-center mb-4';
        reportTitle.innerHTML = `
            <h2>Laporan Penjualan</h2>
            <p>Periode: ${startDate} s/d ${endDate}</p>
        `;
        document.querySelector('.container-fluid').insertBefore(reportTitle, document.querySelector('.card'));

        // Menambahkan style khusus untuk print
        const printStyle = document.createElement('style');
        printStyle.textContent = `
            @media print {
                body {
                    padding: 20px;
                }
                .card {
                    box-shadow: none;
                    border: 1px solid #ddd;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .table th, .table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                }
                .summary-card {
                    margin-bottom: 20px;
                }
                .summary-card h5, .summary-card h3 {
                    color: #000 !important;
                }
                .summary-card {
                    background: #fff !important;
                    border: 1px solid #ddd !important;
                }
            }
        `;
        document.head.appendChild(printStyle);

        // Menjalankan print
        window.print();

        // Mengembalikan tampilan setelah print
        setTimeout(() => {
            elementsToHide.forEach(el => el.style.display = '');
            reportTitle.remove();
            printStyle.remove();
        }, 1000);
    }

    // Generate laporan saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        generateReport();
        lucide.createIcons();
    });
</script>
