<?php
// Start the session to access session variables
session_start();

// Debug information - uncomment to troubleshoot
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// echo "Starting logout process...";

// Clear all session variables
$_SESSION = array();

// If a session cookie is used, destroy it
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy the session
session_destroy();

// Make sure there are no output/whitespace before the header
// Output buffering to catch any warnings or notices
ob_start();

// Redirect to homepage after logout with absolute path
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');
header("Location: http://$host$path/index.php");
ob_end_clean();
exit();
?>
