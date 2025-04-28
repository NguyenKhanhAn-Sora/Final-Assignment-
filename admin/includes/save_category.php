<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

// Xử lý thêm danh mục mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    // Kiểm tra dữ liệu đầu vào
                    if (empty($_POST['name'])) {
                        throw new Exception('Tên danh mục không được để trống');
                    }

                    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                    if ($stmt->execute([
                        trim($_POST['name']),
                        !empty($_POST['description']) ? trim($_POST['description']) : null
                    ])) {
                        $_SESSION['success_message'] = 'Thêm danh mục thành công!';
                    } else {
                        throw new Exception('Không thể thêm danh mục');
                    }
                    break;

                case 'edit':
                    if (empty($_POST['name']) || empty($_POST['category_id'])) {
                        throw new Exception('Thiếu thông tin cần thiết');
                    }

                    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
                    if ($stmt->execute([
                        trim($_POST['name']),
                        !empty($_POST['description']) ? trim($_POST['description']) : null,
                        $_POST['category_id']
                    ])) {
                        $_SESSION['success_message'] = 'Cập nhật danh mục thành công!';
                    } else {
                        throw new Exception('Không thể cập nhật danh mục');
                    }
                    break;

                case 'delete':
                    if (empty($_POST['category_id'])) {
                        throw new Exception('ID danh mục không được để trống');
                    }

                    // Kiểm tra xem danh mục có sản phẩm không
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                    $stmt->execute([$_POST['category_id']]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception('Không thể xóa danh mục đang có sản phẩm!');
                    }

                    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
                    if ($stmt->execute([$_POST['category_id']])) {
                        $_SESSION['success_message'] = 'Xóa danh mục thành công!';
                    } else {
                        throw new Exception('Không thể xóa danh mục');
                    }
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }
    }
}

// Luôn chuyển hướng về trang categories sau khi xử lý
header('Location: ../pages/categories.php');
exit();
