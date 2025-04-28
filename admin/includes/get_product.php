<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if (isset($_GET['id'])) {
    try {
        // Get product details
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name, m.material_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN materials m ON p.material_id = m.material_id
            WHERE p.product_id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get product images
        $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
        $stmt->execute([$_GET['id']]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($product) {
            echo json_encode([
                'success' => true,
                'product' => $product,
                'images' => $images
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
}
