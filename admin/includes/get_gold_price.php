<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once 'db.php';

// Xử lý lấy thông tin giá vàng
if (isset($_GET['price_id'])) {
    $response = ['success' => false, 'message' => '', 'data' => null];
    
    try {
        $price_id = intval($_GET['price_id']);
        
        // Lấy thông tin giá vàng từ database
        $stmt = $conn->prepare('SELECT * FROM gold_prices WHERE price_id = ?');
        $stmt->execute([$price_id]);
        $gold_price = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($gold_price) {
            $response['success'] = true;
            $response['data'] = $gold_price;
        } else {
            throw new Exception('Không tìm thấy thông tin giá vàng');
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Trả về kết quả dạng JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Nếu không có price_id, chuyển hướng về trang quản lý giá vàng
header('Location: ../pages/gold-prices.php');
exit();
?>
