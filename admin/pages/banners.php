<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
require_once '../includes/db.php';

// Kiểm tra nếu có thông báo từ quá trình xử lý trước
$successMsg = $_SESSION['success_msg'] ?? null;
$errorMsg = $_SESSION['error_msg'] ?? null;
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Khởi tạo biến cho chế độ chỉnh sửa
$editMode = false;
$editBanner = null;

// Xử lý hiển thị banner để sửa
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $bannerId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM banner WHERE banner_id = ?");
    $stmt->execute([$bannerId]);
    $editBanner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($editBanner) {
        $editMode = true;
    }
}

// Xử lý xóa banner
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $bannerId = intval($_GET['delete']);
    
    try {
        // Lấy thông tin banner để xóa file ảnh
        $stmt = $conn->prepare("SELECT banner_img_url FROM banner WHERE banner_id = ?");
        $stmt->execute([$bannerId]);
        $banner = $stmt->fetch();
        
        if ($banner) {
            // Xóa file ảnh nếu tồn tại
            if (!empty($banner['banner_img_url']) && file_exists('../../' . $banner['banner_img_url'])) {
                unlink('../../' . $banner['banner_img_url']);
            }
            
            // Xóa banner từ database
            $stmt = $conn->prepare("DELETE FROM banner WHERE banner_id = ?");
            $stmt->execute([$bannerId]);
            
            $_SESSION['success_msg'] = 'Banner đã được xóa thành công!';
        } else {
            $_SESSION['error_msg'] = 'Không tìm thấy banner để xóa!';
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = 'Lỗi khi xóa banner: ' . $e->getMessage();
    }
    
    // Chuyển hướng để tránh gửi lại form khi refresh
    header('Location: banners.php');
    exit();
}

// Xử lý thêm/cập nhật banner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_banner'])) {
    $bannerId = isset($_POST['banner_id']) && !empty($_POST['banner_id']) ? intval($_POST['banner_id']) : null;
    $bannerTitle = $_POST['banner_title'] ?? '';
    $bannerDesc = $_POST['banner_desc'] ?? '';
    
    try {
        // Kiểm tra nếu tiêu đề rỗng
        if (empty($bannerTitle)) {
            throw new Exception("Tiêu đề banner không được để trống");
        }
        
        // Xử lý tải lên ảnh nếu có
        $bannerImgUrl = '';
        $uploadSuccessful = false;
        
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
            // Thư mục lưu ảnh
            $uploadDir = '../../assets/images/banners/';
            
            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Tạo tên file duy nhất
            $fileExtension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
            $fileName = 'banner_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            $bannerImgUrl = 'assets/images/banners/' . $fileName;
            
            // Kiểm tra định dạng file
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                throw new Exception("Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif, webp)");
            }
            
            // Kiểm tra kích thước file (max 5MB)
            if ($_FILES['banner_image']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Kích thước file không được vượt quá 5MB");
            }
            
            // Di chuyển file tải lên vào thư mục đích
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $uploadPath)) {
                $uploadSuccessful = true;
            } else {
                throw new Exception("Có lỗi xảy ra khi tải lên file");
            }
        }
        
        // Thực hiện thêm hoặc cập nhật banner
        if ($bannerId) {
            // Cập nhật banner
            if ($uploadSuccessful) {
                // Nếu có ảnh mới, lấy và xóa ảnh cũ
                $stmt = $conn->prepare("SELECT banner_img_url FROM banner WHERE banner_id = ?");
                $stmt->execute([$bannerId]);
                $oldImage = $stmt->fetchColumn();
                
                if ($oldImage && file_exists('../../' . $oldImage)) {
                    unlink('../../' . $oldImage);
                }
                
                // Cập nhật với ảnh mới
                $stmt = $conn->prepare("UPDATE banner SET banner_title = ?, banner_desc = ?, banner_img_url = ? WHERE banner_id = ?");
                $stmt->execute([$bannerTitle, $bannerDesc, $bannerImgUrl, $bannerId]);
            } else {
                // Cập nhật không có ảnh mới
                $stmt = $conn->prepare("UPDATE banner SET banner_title = ?, banner_desc = ? WHERE banner_id = ?");
                $stmt->execute([$bannerTitle, $bannerDesc, $bannerId]);
            }
            
            $_SESSION['success_msg'] = 'Banner đã được cập nhật thành công!';
        } else {
            // Thêm banner mới
            if (!$uploadSuccessful) {
                throw new Exception("Vui lòng chọn ảnh cho banner");
            }
            
            $stmt = $conn->prepare("INSERT INTO banner (banner_title, banner_desc, banner_img_url) VALUES (?, ?, ?)");
            $stmt->execute([$bannerTitle, $bannerDesc, $bannerImgUrl]);
            
            $_SESSION['success_msg'] = 'Banner đã được thêm thành công!';
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = $e->getMessage();
    }
    
    // Chuyển hướng để tránh gửi lại form khi refresh
    header('Location: banners.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Banners - Kim Phát Gold</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../includes/header.php'; ?>
            
            <div class="container-fluid py-4">                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Banners</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bannerModal">
                        <i class="fas fa-plus"></i> Add New Banner
                    </button>
                </div>                <!-- Hiển thị thông báo thành công hoặc lỗi -->
                <?php if ($successMsg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $successMsg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($errorMsg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $errorMsg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row" id="bannerGrid">
                    <?php
                    $query = "SELECT * FROM banner ORDER BY create_at DESC";
                    $stmt = $conn->query($query);

                    while ($row = $stmt->fetch()) {
                        echo '<div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="../../' . $row['banner_img_url'] . '" class="card-img-top" alt="Banner" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($row['banner_title']) . '</h5>
                                    <p class="card-text">' . htmlspecialchars($row['banner_desc']) . '</p>
                                    <div class="action-buttons">
                                        <a href="banners.php?edit=' . $row['banner_id'] . '" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="banners.php?delete=' . $row['banner_id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Bạn có chắc chắn muốn xóa banner này?\')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                    
                    if ($stmt->rowCount() == 0) {
                        echo '<div class="col-12 text-center py-5"><p>Chưa có banner nào.</p></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>    <!-- Add/Edit Banner Modal -->
    <div class="modal fade" id="bannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $editMode ? 'Edit Banner' : 'Add New Banner'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="bannerForm" method="POST" action="banners.php" enctype="multipart/form-data">
                        <input type="hidden" name="banner_id" value="<?php echo $editMode ? $editBanner['banner_id'] : ''; ?>">
                        <div class="mb-3">
                            <label class="form-label">Banner Title</label>
                            <input type="text" class="form-control" name="banner_title" value="<?php echo $editMode ? htmlspecialchars($editBanner['banner_title']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="banner_desc" rows="3"><?php echo $editMode ? htmlspecialchars($editBanner['banner_desc']) : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Banner Image</label>
                            <input type="file" class="form-control" name="banner_image" accept="image/*" <?php echo !$editMode ? 'required' : ''; ?>>
                            <?php if ($editMode && !empty($editBanner['banner_img_url'])): ?>
                            <div class="mt-2">
                                <p>Ảnh hiện tại:</p>
                                <img src="../../<?php echo $editBanner['banner_img_url']; ?>" alt="Current Banner" style="max-width: 200px">
                                <p class="mt-2 text-muted">Ảnh hiện tại sẽ được giữ lại nếu không tải lên ảnh mới.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" name="save_banner" class="btn btn-primary">Lưu Banner</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/banners.js?v=<?php echo time(); ?>"></script>
</body>
</html>
