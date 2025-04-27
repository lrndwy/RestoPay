<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'waiter') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

try {
    if (!isset($pdo)) {
        throw new Exception('Koneksi database gagal');
    }

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $query = "SELECT id, name, phone FROM customers ORDER BY name ASC";
    $stmt = $pdo->query($query);
    
    if (!$stmt) {
        throw new Exception('Query error: ' . implode(' ', $pdo->errorInfo()));
    }
    
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $customers
    ]);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Gagal mengambil data pelanggan',
        'message' => $e->getMessage(),
        'file' => __FILE__,
        'line' => __LINE__
    ]);
    exit;
} 