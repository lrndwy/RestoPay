<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'waiter') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

try {
    // Terima data JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Data tidak valid');
    }

    // Validasi data yang diperlukan
    if (!isset($data['table_id']) || !isset($data['items']) || empty($data['items'])) {
        throw new Exception('Data pesanan tidak lengkap');
    }

    $pdo->beginTransaction();
    
    try {
        // Jika pelanggan baru
        if (isset($data['customer_data'])) {
            $customer = $data['customer_data'];
            $stmt = $pdo->prepare("INSERT INTO customers (name, phone, gender, address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$customer['name'], $customer['phone'], $customer['gender'], $customer['address']]);
            $customer_id = $pdo->lastInsertId();
        } else {
            if (!isset($data['customer_id'])) {
                throw new Exception('ID pelanggan tidak ditemukan');
            }
            $customer_id = $data['customer_id'];
        }
        
        // Buat pesanan baru
        $stmt = $pdo->prepare("INSERT INTO orders (table_id, customer_id, waiter_id, status, order_time) VALUES (?, ?, ?, 'pending', NOW())");
        $waiter_id = $_SESSION['user_id'];
        $stmt->execute([$data['table_id'], $customer_id, $waiter_id]);
        $order_id = $pdo->lastInsertId();
        
        // Simpan detail pesanan
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($data['items'] as $item) {
            if (!isset($item['menu_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                throw new Exception('Data item pesanan tidak lengkap');
            }
            $stmt->execute([$order_id, $item['menu_id'], $item['quantity'], $item['price']]);
        }
        
        // Update status meja
        $stmt = $pdo->prepare("UPDATE tables SET status = 'occupied' WHERE id = ?");
        $stmt->execute([$data['table_id']]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pesanan berhasil disimpan',
            'order_id' => $order_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => [
            'file' => __FILE__,
            'line' => __LINE__
        ]
    ]);
}
?> 