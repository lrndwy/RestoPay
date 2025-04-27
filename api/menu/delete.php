<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    try {
        $id = $_GET['id'];
        
        // Cek apakah menu masih digunakan di order_items
        $stmt = $pdo->prepare("SELECT COUNT(*) as used FROM order_items WHERE menu_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['used'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Menu tidak dapat dihapus karena masih digunakan dalam pesanan']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM menu WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Menu gagal dihapus']);
    }
}
?> 