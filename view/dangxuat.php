<?php
// Xóa tất cả session variables
$_SESSION = array();

// Xóa session cookie nếu có
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect về trang index.php
header("refresh:0.5;url=index.php");
exit();
