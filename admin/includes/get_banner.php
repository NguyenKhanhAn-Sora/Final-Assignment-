<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once 'db.php';

// Kiểm tra nếu có request lấy thông tin banner
if (isset($_GET['banner_id'])) {
    $banner_id = intval($_GET['banner_id']);
    
    try {
        // Lấy thông tin banner từ database
        $stmt = $conn->prepare("SELECT * FROM banner WHERE banner_id = ?");
        $stmt->execute([$banner_id]);
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($banner) {
            // Trả về kết quả dạng JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'banner' => $banner
            ]);
        } else {
            throw new Exception("Không tìm thấy banner");
        }
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Chuyển hướng về trang banners nếu không có banner_id
header('Location: ../pages/banners.php');
exit();
?>
