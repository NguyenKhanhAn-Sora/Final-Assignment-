<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Generate SKU
        $sku = 'PRD-' . strtoupper(uniqid());
        
        // Generate slug from name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));
        
        // Insert product basic information
        $stmt = $conn->prepare("INSERT INTO products (name, slug, sku, category_id, price, sale_price, description, material_id, weight, stone_type, stone_size, stock_status, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $sale_price = !empty($_POST['sale_price']) ? $_POST['sale_price'] : null;
        $description = $_POST['description'];
        $material_id = $_POST['material_id'];
        $weight = !empty($_POST['weight']) ? $_POST['weight'] : null;
        $stone_type = !empty($_POST['stone_type']) ? $_POST['stone_type'] : null;
        $stone_size = !empty($_POST['stone_size']) ? $_POST['stone_size'] : null;
        $stock_status = $_POST['stock_status'];

        $stmt->execute([
            $name, 
            $slug, 
            $sku, 
            $category_id, 
            $price, 
            $sale_price, 
            $description, 
            $material_id, 
            $weight, 
            $stone_type, 
            $stone_size, 
            $stock_status
        ]);
        
        $product_id = $conn->lastInsertId();

        // Handle image uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $uploadDir = '../../assets/images/products/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileType = $_FILES['images']['type'][$key];
                    $fileSize = $_FILES['images']['size'][$key];
                    
                    // Generate unique filename
                    $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $newFileName = $sku . '_' . ($key + 1) . '.' . $extension;
                    $targetPath = $uploadDir . $newFileName;

                    // Move uploaded file
                    if (move_uploaded_file($tmp_name, $targetPath)) {
                        // Save image info to database
                        $imageUrl = '/Assignment/assets/images/products/' . $newFileName;
                        $is_primary = ($key === 0) ? 1 : 0; // First image is primary
                        
                        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
                        $stmt->execute([$product_id, $imageUrl, $is_primary]);
                    }
                }
            }
        }        $conn->commit();
        $_SESSION['success_message'] = "Product added successfully!";
        header('Location: /Assignment/admin/pages/products.php');
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header('Location: /Assignment/admin/pages/products.php');
        exit();
    }
} else {
    // If not POST request, redirect to products page
    header('Location: ../pages/products.php');
    exit();
}
