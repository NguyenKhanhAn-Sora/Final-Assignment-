<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once 'db.php';

// Xử lý thêm hoặc cập nhật giá vàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Lấy dữ liệu từ form
        $price_id = isset($_POST['price_id']) && !empty($_POST['price_id']) ? intval($_POST['price_id']) : null;
        $gold_type = $_POST['gold_type'] ?? '';
        $buy_price = isset($_POST['buy_price']) ? str_replace(',', '', $_POST['buy_price']) : 0;
        $sell_price = isset($_POST['sell_price']) ? str_replace(',', '', $_POST['sell_price']) : 0;
        
        // Kiểm tra dữ liệu đầu vào
        if (empty($gold_type)) {
            throw new Exception('Vui lòng chọn loại vàng');
        }
        
        if (!is_numeric($buy_price) || $buy_price <= 0) {
            throw new Exception('Giá mua phải là số dương');
        }
        
        if (!is_numeric($sell_price) || $sell_price <= 0) {
            throw new Exception('Giá bán phải là số dương');
        }
        
        // Chuyển giá trị sang kiểu số thập phân
        $buy_price = floatval($buy_price);
        $sell_price = floatval($sell_price);
        
        // Nếu có ID, cập nhật giá vàng hiện có, ngược lại thêm mới
        if ($price_id) {
            // Kiểm tra xem giá vàng có tồn tại không
            $check_stmt = $conn->prepare('SELECT price_id FROM gold_prices WHERE price_id = ?');
            $check_stmt->execute([$price_id]);
            
            if ($check_stmt->rowCount() === 0) {
                throw new Exception('Không tìm thấy thông tin giá vàng để cập nhật');
            }
            
            // Cập nhật giá vàng
            $update_stmt = $conn->prepare('UPDATE gold_prices SET gold_type = ?, buy_price = ?, sell_price = ?, updated_at = NOW() WHERE price_id = ?');
            if ($update_stmt->execute([$gold_type, $buy_price, $sell_price, $price_id])) {
                $response['success'] = true;
                $response['message'] = 'Cập nhật giá vàng thành công';
            } else {
                throw new Exception('Có lỗi xảy ra khi cập nhật giá vàng');
            }
        } else {
            // Kiểm tra xem loại vàng đã tồn tại chưa
            $check_stmt = $conn->prepare('SELECT price_id FROM gold_prices WHERE gold_type = ?');
            $check_stmt->execute([$gold_type]);
            
            if ($check_stmt->rowCount() > 0) {
                throw new Exception('Loại vàng này đã tồn tại, vui lòng cập nhật thay vì thêm mới');
            }
            
            // Thêm mới giá vàng
            $insert_stmt = $conn->prepare('INSERT INTO gold_prices (gold_type, buy_price, sell_price) VALUES (?, ?, ?)');
            if ($insert_stmt->execute([$gold_type, $buy_price, $sell_price])) {
                $response['success'] = true;
                $response['message'] = 'Thêm giá vàng mới thành công';
                $response['price_id'] = $conn->lastInsertId();
            } else {
                throw new Exception('Có lỗi xảy ra khi thêm giá vàng mới');
            }
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

// Nếu không phải POST request, chuyển hướng về trang quản lý giá vàng
header('Location: ../pages/gold-prices.php');
exit();
?>
