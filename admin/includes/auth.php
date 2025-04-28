<?php
session_start();
require_once 'db.php';

// Kiểm tra nếu form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        // Chuẩn bị câu query với cấu trúc bảng admins mới
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();        // Kiểm tra tài khoản và mật khẩu
        if ($admin && $password === $admin['password']) {
            // Đăng nhập thành công
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_email'] = $admin['email'];

            // Chuyển hướng về trang dashboard
            header('Location: ../index.php');
            exit();
        } else {
            // Đăng nhập thất bại
            header('Location: ../pages/login.php?error=1');
            exit();
        }

    } catch (PDOException $e) {
        // Xử lý lỗi database
        error_log("Database Error: " . $e->getMessage());
        header('Location: ../pages/login.php?error=2');
        exit();
    }
} else {
    // Nếu không phải POST request, chuyển về trang login
    header('Location: ../pages/login.php');
    exit();
}
