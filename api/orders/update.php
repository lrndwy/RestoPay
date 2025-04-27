<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        $order_id = $_POST['order_id'];
        $items = $_POST['items'];

        // Validasi items
        $has_items = false;
        foreach ($items as $menu_id => $quantity) {
            if ($quantity > 0) {
                $has_items = true;
                break;
            }
        }

        if (!$has_items) {
            throw new Exception('Pesanan harus memiliki minimal 1 item');
        }

        // Ambil item pesanan yang ada saat ini
        $stmt = $pdo->prepare("SELECT menu_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $existing_items = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Ambil harga terbaru dari menu
        $menu_stmt = $pdo->prepare("SELECT id, price FROM menu WHERE id = ?");

        // Prepare statements
        $insert_stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, menu_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        
        $update_stmt = $pdo->prepare("
            UPDATE order_items 
            SET quantity = ?, price = ?
            WHERE order_id = ? AND menu_id = ?
        ");
        
        $delete_stmt = $pdo->prepare("
            DELETE FROM order_items 
            WHERE order_id = ? AND menu_id = ?
        ");

        // Proses setiap item
        foreach ($items as $menu_id => $new_quantity) {
            $new_quantity = intval($new_quantity);
            
            // Ambil harga terbaru menu
            $menu_stmt->execute([$menu_id]);
            $menu = $menu_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$menu) {
                continue; // Skip jika menu tidak ditemukan
            }
            
            $current_price = $menu['price'];
            
            if (isset($existing_items[$menu_id])) {
                // Item sudah ada sebelumnya
                if ($new_quantity > 0) {
                    if ($new_quantity !== intval($existing_items[$menu_id])) {
                        // Update quantity dan harga
                        $update_stmt->execute([$new_quantity, $current_price, $order_id, $menu_id]);
                    }
                } else {
                    // Hapus item jika quantity 0
                    $delete_stmt->execute([$order_id, $menu_id]);
                }
            } else {
                // Item baru
                if ($new_quantity > 0) {
                    $insert_stmt->execute([$order_id, $menu_id, $new_quantity, $current_price]);
                }
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?> 