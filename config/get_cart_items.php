<?php
session_start();
require_once '../config/connection.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User tidak terautentikasi');
    }

    // Get cart items dengan paper specifications
    $sql = "SELECT c.jumlah as quantity, 
                   p.paper_type, 
                   p.paper_size,
                   p.product_id,
                   p.nama_produk
            FROM carts c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE c.user_id = :user_id";

    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        throw new Exception('Keranjang belanja kosong');
    }

    echo json_encode([
        'status' => 'success',
        'items' => $items
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}