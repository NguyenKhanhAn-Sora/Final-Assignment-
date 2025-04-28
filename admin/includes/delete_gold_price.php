<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once 'db.php';

// Xử lý xóa giá vàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['price_id'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $price_id = intval($_POST['price_id']);
        
        // Kiểm tra xem giá vàng có tồn tại không
        $check_stmt = $conn->prepare('SELECT price_id FROM gold_prices WHERE price_id = ?');
        $check_stmt->execute([$price_id]);
        
        if ($check_stmt->rowCount() === 0) {
            throw new Exception('Không tìm thấy thông tin giá vàng để xóa');
        }
        
        // Thực hiện xóa giá vàng
        $delete_stmt = $conn->prepare('DELETE FROM gold_prices WHERE price_id = ?');
        if ($delete_stmt->execute([$price_id])) {
            $response['success'] = true;
            $response['message'] = 'Xóa giá vàng thành công';
        } else {
            throw new Exception('Có lỗi xảy ra khi xóa giá vàng');
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Kiểm tra nếu là AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        // Nếu không phải AJAX, lưu thông báo vào session và chuyển hướng
        if ($response['success']) {
            $_SESSION['success_msg'] = $response['message'];
        } else {
            $_SESSION['error_msg'] = $response['message'];
        }
        
        header('Location: ../pages/gold-prices.php');
        exit();
    }
}

// Nếu không phải POST request hoặc không có price_id, chuyển hướng về trang quản lý giá vàng
header('Location: ../pages/gold-prices.php');
exit();
?>
