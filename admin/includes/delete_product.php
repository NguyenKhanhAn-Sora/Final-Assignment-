<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

if (isset($_GET['id'])) {
    try {
        $conn->beginTransaction();
        
        $product_id = $_GET['id'];

        // Lấy danh sách ảnh trước khi xóa
        $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Xóa ảnh từ thư mục
        foreach ($images as $imageUrl) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $imageUrl;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Xóa ảnh từ database
        $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);

        // Xóa sản phẩm
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);

        $conn->commit();
        $_SESSION['success_message'] = "Xóa sản phẩm thành công!";

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Lỗi: " . $e->getMessage();
    }
}

// Luôn chuyển hướng về trang products
header('Location: ../pages/products.php');
exit();
