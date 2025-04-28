<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News & Promotions - Kim Phát Gold</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Add TinyMCE for rich text editing -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../includes/header.php'; ?>
            
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage News & Promotions</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newsModal">
                        <i class="fas fa-plus"></i> Add New Post
                    </button>
                </div>

                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#news">News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#promotions">Promotions</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="news">
                        <div class="row" id="newsGrid">
                            <?php
                            $query = "SELECT * FROM news WHERE type = 'news' ORDER BY created_at DESC";
                            $stmt = $conn->query($query);

                            while ($row = $stmt->fetch()) {
                                echo '<div class="col-md-4 mb-4">
                                    <div class="card">
                                        <img src="' . ($row['image_url'] ?: '../assets/img/default-news.jpg') . '" class="card-img-top" alt="News Image">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-' . ($row['status'] == 'published' ? 'success' : 'warning') . '">
                                                    ' . ucfirst($row['status']) . '
                                                </span>
                                                <small class="text-muted">' . date('Y-m-d', strtotime($row['created_at'])) . '</small>
                                            </div>
                                            <h5 class="card-title">' . $row['title'] . '</h5>
                                            <p class="card-text">' . substr(strip_tags($row['content']), 0, 100) . '...</p>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-primary" onclick="editNews(' . $row['news_id'] . ')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteNews(' . $row['news_id'] . ')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="promotions">
                        <div class="row" id="promotionsGrid">
                            <?php
                            $query = "SELECT * FROM news WHERE type = 'promotion' ORDER BY created_at DESC";
                            $stmt = $conn->query($query);

                            while ($row = $stmt->fetch()) {
                                echo '<div class="col-md-4 mb-4">
                                    <div class="card">
                                        <img src="' . ($row['image_url'] ?: '../assets/img/default-promotion.jpg') . '" class="card-img-top" alt="Promotion Image">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-' . ($row['status'] == 'published' ? 'success' : 'warning') . '">
                                                    ' . ucfirst($row['status']) . '
                                                </span>
                                                <small class="text-muted">' . date('Y-m-d', strtotime($row['created_at'])) . '</small>
                                            </div>
                                            <h5 class="card-title">' . $row['title'] . '</h5>
                                            <p class="card-text">' . substr(strip_tags($row['content']), 0, 100) . '...</p>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-primary" onclick="editNews(' . $row['news_id'] . ')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteNews(' . $row['news_id'] . ')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit News Modal -->
    <div class="modal fade" id="newsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newsForm" enctype="multipart/form-data">
                        <input type="hidden" name="news_id" id="newsId">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Type</label>
                                    <select class="form-control" name="type" required>
                                        <option value="news">News</option>
                                        <option value="promotion">Promotion</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" id="newsContent" rows="10"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Featured Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <div id="currentImage" class="mt-2"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveNews()">Save Post</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/news.js"></script>
</body>
</html>
