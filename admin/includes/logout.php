<?php
// Bắt đầu session
session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Xóa session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Hủy session
session_destroy();

// Chuyển hướng về trang login
header("Location: /Assignment/admin/pages/login.php");
exit();
