<?php
session_start();
require_once 'db.php';

// Kiểm tra xem có phải yêu cầu AJAX không
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Kiểm tra phương thức và các trường bắt buộc
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {        
        // Debug để xem kết nối database
        if (!isset($conn) || $conn === null) {
            throw new Exception("Kết nối database bị lỗi");
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['full_name'])) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc.");
        }
        
        // Kiểm tra xác nhận mật khẩu
        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception("Mật khẩu xác nhận không khớp.");
        }

        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception("Email không hợp lệ.");
        }

        // Kiểm tra bảng users có tồn tại không
        try {
            $checkTable = $conn->query("SHOW TABLES LIKE 'users'");
            if ($checkTable->rowCount() == 0) {
                // Bảng users chưa tồn tại, tạo bảng
                $conn->exec("CREATE TABLE users (
                    user_id INT PRIMARY KEY AUTO_INCREMENT,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    full_name VARCHAR(100),
                    phone VARCHAR(15),
                    address TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
            }
        } catch (Exception $tableError) {
            throw new Exception("Lỗi kiểm tra bảng: " . $tableError->getMessage());
        }

        // Kiểm tra email đã tồn tại chưa
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email này đã được đăng ký.");
            }
        } catch (Exception $emailError) {
            if (strpos($emailError->getMessage(), "doesn't exist") !== false) {
                throw new Exception("Lỗi cấu trúc bảng. Vui lòng liên hệ quản trị viên.");
            } else {
                throw new Exception("Lỗi kiểm tra email: " . $emailError->getMessage());
            }
        }

        // Kiểm tra mật khẩu
        if (strlen($_POST['password']) < 6) {
            throw new Exception("Mật khẩu phải có ít nhất 6 ký tự.");
        }

        // Mã hóa mật khẩu
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Lưu thông tin người dùng
        $stmt = $conn->prepare("
            INSERT INTO users (email, password_hash, full_name, phone, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");        $stmt->execute([
            $email,
            $password_hash,
            $_POST['full_name'],
            $_POST['phone'] ?? null
        ]);        // Tạo phiên đăng nhập
        $user_id = $conn->lastInsertId();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['full_name'] = $_POST['full_name'];

        $response['success'] = true;
        $response['message'] = "Đăng ký thành công!";
        $response['redirect'] = "/Assignment/index.php";
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    // Trả về kết quả dạng JSON nếu là AJAX request
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } 
      // Nếu không phải AJAX, lưu thông báo vào session và chuyển hướng
    if ($response['success']) {
        $_SESSION['success_message'] = $response['message'];
        header('Location: ../index.php');
    } else {
        $_SESSION['register_error'] = $response['message'];
        header('Location: ../index.php');
    }
    exit();
}

// Nếu không phải POST request, chuyển hướng về trang chủ
header('Location: ../index.php');
exit();
