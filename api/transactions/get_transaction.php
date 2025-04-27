<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID transaksi tidak ditemukan');
    }

    $transaction_id = $_GET['id'];

    // Ambil detail transaksi
    $stmt = $pdo->prepare("
        SELECT t.*, o.table_id, o.customer_id, 
        c.name as customer_name, c.phone as customer_phone,
        tb.table_number, u_waiter.username as waiter_name
        FROM transactions t
        JOIN orders o ON t.order_id = o.id
        JOIN tables tb ON o.table_id = tb.id
        LEFT JOIN users u_waiter ON o.waiter_id = u_waiter.id
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE t.id = ?
    ");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        throw new Exception('Transaksi tidak ditemukan');
    }

    // Ambil item pesanan
    $stmt = $pdo->prepare("
        SELECT oi.*, m.name as menu_name
        FROM order_items oi
        JOIN menu m ON oi.menu_id = m.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$transaction['order_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $transaction['items'] = $items;

    echo json_encode([
        'success' => true,
        'data' => $transaction
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 