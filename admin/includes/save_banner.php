<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once 'db.php';

// Kiểm tra nếu form được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Lấy dữ liệu từ form
        $banner_id = isset($_POST['banner_id']) && !empty($_POST['banner_id']) ? intval($_POST['banner_id']) : null;
        $banner_title = $_POST['banner_title'] ?? '';
        $banner_desc = $_POST['banner_desc'] ?? '';
        
        // Kiểm tra nếu tiêu đề rỗng
        if (empty($banner_title)) {
            throw new Exception("Tiêu đề banner không được để trống");
        }
        
        // Xử lý tải lên ảnh nếu có
        $banner_img_url = '';
        $upload_successful = false;
        
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
            // Thư mục lưu ảnh
            $upload_dir = '../../assets/images/banners/';
            
            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Tạo tên file duy nhất
            $file_extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'banner_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $file_name;
            $banner_img_url = 'assets/images/banners/' . $file_name;
            
            // Kiểm tra định dạng file
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                throw new Exception("Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif, webp)");
            }
            
            // Kiểm tra kích thước file (max 5MB)
            if ($_FILES['banner_image']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Kích thước file không được vượt quá 5MB");
            }
            
            // Di chuyển file tải lên vào thư mục đích
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_path)) {
                $upload_successful = true;
            } else {
                throw new Exception("Có lỗi xảy ra khi tải lên file");
            }
        }
        
        // Thực hiện thêm hoặc cập nhật banner
        if ($banner_id) {
            // Cập nhật banner
            if ($upload_successful) {
                // Nếu có ảnh mới, lấy và xóa ảnh cũ
                $stmt = $conn->prepare("SELECT banner_img_url FROM banner WHERE banner_id = ?");
                $stmt->execute([$banner_id]);
                $old_image = $stmt->fetchColumn();
                
                if ($old_image && file_exists('../../' . $old_image)) {
                    unlink('../../' . $old_image);
                }
                
                // Cập nhật với ảnh mới
                $stmt = $conn->prepare("UPDATE banner SET banner_title = ?, banner_desc = ?, banner_img_url = ? WHERE banner_id = ?");
                $stmt->execute([$banner_title, $banner_desc, $banner_img_url, $banner_id]);
            } else {
                // Cập nhật không có ảnh mới
                $stmt = $conn->prepare("UPDATE banner SET banner_title = ?, banner_desc = ? WHERE banner_id = ?");
                $stmt->execute([$banner_title, $banner_desc, $banner_id]);
            }
            
            $response['message'] = 'Banner đã được cập nhật thành công!';
        } else {
            // Thêm banner mới
            if (!$upload_successful) {
                throw new Exception("Vui lòng chọn ảnh cho banner");
            }
            
            $stmt = $conn->prepare("INSERT INTO banner (banner_title, banner_desc, banner_img_url) VALUES (?, ?, ?)");
            $stmt->execute([$banner_title, $banner_desc, $banner_img_url]);
            
            $response['message'] = 'Banner đã được thêm thành công!';
        }
        
        $response['success'] = true;
        
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
