<?php
session_start();
require_once 'db.php';

// Kiểm tra xem có phải yêu cầu AJAX không
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $response = ['success' => false, 'message' => ''];

    try {
        // Kiểm tra email tồn tại
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
              // Nếu người dùng chọn "Ghi nhớ đăng nhập"
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/');
                
                // Lưu token vào session thay vì database vì bảng users không có cột remember_token
                $_SESSION['remember_token'] = $token;
            }$response['success'] = true;
            $response['message'] = 'Đăng nhập thành công';
            // Sử dụng đường dẫn đầy đủ bao gồm thư mục Assignment
            $response['redirect'] = '/Assignment/index.php';
        } else {
            $response['success'] = false;
            $response['message'] = 'Email hoặc mật khẩu không chính xác';
        }
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = 'Có lỗi xảy ra: ' . $e->getMessage();
    }

    // Phản hồi tùy theo loại yêu cầu
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        if ($response['success']) {
            // Đăng nhập thành công, chuyển hướng
            header('Location: ../index.php');
        } else {
            // Đăng nhập thất bại, lưu lỗi vào session và quay lại
            $_SESSION['error'] = $response['message'];
            header('Location: ../index.php');
        }
        exit();
    }
}

// Nếu không phải POST, chuyển hướng về trang chủ
header('Location: ../index.php');
exit();
