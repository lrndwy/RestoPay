<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'waiter') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';

try {
    // Validasi input
    if (empty($_POST['name']) || empty($_POST['phone']) || empty($_POST['gender'])) {
        throw new Exception('Semua field wajib diisi');
    }

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $address = $_POST['address'] ?? null;

    // Cek nomor telepon unik
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Nomor telepon sudah terdaftar');
    }

    // Insert data pelanggan baru
    $stmt = $pdo->prepare("
        INSERT INTO customers (name, phone, gender, address)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$name, $phone, $gender, $address]);
    $customer_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Data pelanggan berhasil disimpan',
        'customer_id' => $customer_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 