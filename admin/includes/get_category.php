<?php
session_start();
require_once 'db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin_id'])) {
    die(json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]));
}

// Kiểm tra id category có được gửi lên không
if (!isset($_GET['id'])) {
    die(json_encode([
        'success' => false,
        'message' => 'Missing category ID'
    ]));
}

try {
    // Lấy thông tin category
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$_GET['id']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        echo json_encode([
            'success' => true,
            'category' => $category
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Category not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
