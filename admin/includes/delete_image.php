<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if (isset($_POST['image_id'])) {
    try {
        $conn->beginTransaction();

        // Lấy thông tin ảnh trước khi xóa
        $stmt = $conn->prepare("
            SELECT pi.image_id, pi.product_id, pi.image_url, pi.is_primary 
            FROM product_images pi 
            WHERE pi.image_id = ?
        ");
        $stmt->execute([$_POST['image_id']]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            throw new Exception('Không tìm thấy ảnh');
        }

        // Xóa ảnh từ database
        $stmt = $conn->prepare("DELETE FROM product_images WHERE image_id = ?");
        $stmt->execute([$_POST['image_id']]);

        // Nếu ảnh bị xóa là ảnh chính, cập nhật ảnh chính mới
        if ($image['is_primary'] == 1) {
            // Tìm ảnh còn lại của sản phẩm
            $stmt = $conn->prepare("
                SELECT image_id 
                FROM product_images 
                WHERE product_id = ? 
                ORDER BY image_id ASC 
                LIMIT 1
            ");
            $stmt->execute([$image['product_id']]);
            $remainingImage = $stmt->fetch(PDO::FETCH_ASSOC);

            // Nếu còn ảnh, đặt làm ảnh chính
            if ($remainingImage) {
                $stmt = $conn->prepare("UPDATE product_images SET is_primary = 1 WHERE image_id = ?");
                $stmt->execute([$remainingImage['image_id']]);
            }
        }

        // Commit transaction
        $conn->commit();

        // Xóa file ảnh vật lý
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $image['image_url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin ảnh']);
}
