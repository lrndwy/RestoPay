<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'waiter') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

try {
    // Validasi input
    if (empty($_POST['name']) || empty($_POST['phone']) || empty($_POST['gender'])) {
        throw new Exception('Semua field wajib diisi');
    }

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $address = $_POST['address'] ?? null;
    $customer_id = $_POST['customer_id'] ?? null;

    $pdo->beginTransaction();

    if ($customer_id) {
        // Mode Edit
        // Cek nomor telepon unik kecuali untuk pelanggan yang sedang diedit
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $customer_id]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Nomor telepon sudah terdaftar');
        }

        // Update data pelanggan
        $stmt = $pdo->prepare("
            UPDATE customers 
            SET name = ?, phone = ?, gender = ?, address = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $gender, $address, $customer_id]);
        
        $message = 'Data pelanggan berhasil diperbarui';
    } else {
        // Mode Tambah
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
        
        $message = 'Data pelanggan berhasil disimpan';
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'customer_id' => $customer_id
    ]);

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 