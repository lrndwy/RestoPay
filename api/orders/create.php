<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode tidak diizinkan');
    }

    // Validasi input
    if (!isset($_POST['table_id']) || !isset($_POST['waiter_id']) || !isset($_POST['items']) || !isset($_POST['customer_name'])) {
        throw new Exception('Data tidak lengkap');
    }

    $table_id = $_POST['table_id'];
    $waiter_id = $_POST['waiter_id'];
    $customer_name = $_POST['customer_name'];
    $customer_phone = isset($_POST['customer_phone']) ? $_POST['customer_phone'] : null;
    $items = $_POST['items'];

    // Mulai transaksi
    $pdo->beginTransaction();

    // Periksa status meja
    $stmt = $pdo->prepare("SELECT status FROM tables WHERE id = ?");
    $stmt->execute([$table_id]);
    $table = $stmt->fetch();

    if (!$table) {
        throw new Exception('Meja tidak ditemukan');
    }

    if ($table['status'] === 'occupied') {
        throw new Exception('Meja sudah terisi');
    }

    // Buat pesanan baru
    $stmt = $pdo->prepare("INSERT INTO orders (table_id, waiter_id, customer_name, customer_phone, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$table_id, $waiter_id, $customer_name, $customer_phone]);
    $order_id = $pdo->lastInsertId();

    // Update status meja menjadi occupied
    $stmt = $pdo->prepare("UPDATE tables SET status = 'occupied' WHERE id = ?");
    $stmt->execute([$table_id]);

    // Masukkan item pesanan
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, (SELECT price FROM menu WHERE id = ?))");
    
    foreach ($items as $menu_id => $quantity) {
        if ($quantity > 0) {
            $stmt->execute([$order_id, $menu_id, $quantity, $menu_id]);
        }
    }

    // Commit transaksi
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil dibuat',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 