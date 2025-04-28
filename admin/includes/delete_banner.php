<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once 'db.php';

// Kiểm tra nếu có request xóa banner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['banner_id'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $banner_id = intval($_POST['banner_id']);
        
        // Lấy thông tin banner để xóa file ảnh
        $stmt = $conn->prepare("SELECT banner_img_url FROM banner WHERE banner_id = ?");
        $stmt->execute([$banner_id]);
        $banner = $stmt->fetch();
        
        if ($banner) {
            // Xóa file ảnh nếu tồn tại
            if (!empty($banner['banner_img_url']) && file_exists('../../' . $banner['banner_img_url'])) {
                unlink('../../' . $banner['banner_img_url']);
            }
            
            // Xóa banner từ database
            $stmt = $conn->prepare("DELETE FROM banner WHERE banner_id = ?");
            $stmt->execute([$banner_id]);
            
            $response['success'] = true;
            $response['message'] = 'Banner đã được xóa thành công!';
        } else {
            throw new Exception("Không tìm thấy banner");
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Trả về kết quả dạng JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Chuyển hướng về trang banners nếu không phải POST request
header('Location: ../pages/banners.php');
exit();
?>
