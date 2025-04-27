<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID pesanan tidak ditemukan');
    }

    $order_id = $_GET['id'];

    // Ambil detail pesanan
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.table_id,
            o.waiter_id,
            o.status,
            o.order_time,
            o.customer_id,
            c.name as customer_name,
            c.phone as customer_phone,
            t.table_number,
            u.username as waiter_name
        FROM orders o
        JOIN tables t ON o.table_id = t.id
        LEFT JOIN users u ON o.waiter_id = u.id
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Pesanan tidak ditemukan');
    }

    // Ambil item pesanan dengan detail menu
    $stmt = $pdo->prepare("
        SELECT 
            oi.order_id,
            oi.menu_id,
            oi.quantity,
            oi.price as item_price,
            m.name as menu_name,
            (oi.quantity * oi.price) as subtotal
        FROM order_items oi
        JOIN menu m ON oi.menu_id = m.id
        WHERE oi.order_id = ?
        ORDER BY m.name ASC
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung total dari database
    $stmt = $pdo->prepare("
        SELECT SUM(quantity * price) as total 
        FROM order_items 
        WHERE order_id = ?
    ");
    $stmt->execute([$order_id]);
    $total_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = floatval($total_row['total']);

    // Format items
    foreach ($items as &$item) {
        $item['menu_id'] = intval($item['menu_id']);
        $item['quantity'] = intval($item['quantity']);
        $item['price'] = floatval($item['item_price']);
        $item['subtotal'] = floatval($item['subtotal']);
        unset($item['item_price']);
    }

    // Format response data
    $response_data = [
        'id' => intval($order['id']),
        'table_id' => intval($order['table_id']),
        'table_number' => $order['table_number'],
        'waiter_id' => intval($order['waiter_id']),
        'waiter_name' => $order['waiter_name'],
        'status' => $order['status'],
        'order_time' => $order['order_time'],
        'customer_name' => $order['customer_name'],
        'customer_phone' => $order['customer_phone'],
        'items' => $items,
        'total' => $total
    ];

    echo json_encode([
        'success' => true,
        'data' => $response_data
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 