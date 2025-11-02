<?php
// Kiểm tra đăng nhập
if (!isset($_SESSION['uid'])) {
    header("Location: home.php?page=dangnhap");
    exit();
}

// Kiểm tra có userId cần chặn không (hỗ trợ cả GET và POST)
$userToBlock = null;
if (isset($_POST['uid'])) {
    $userToBlock = $_POST['uid'];
} elseif (isset($_GET['uid'])) {
    $userToBlock = $_GET['uid'];
}

if (!$userToBlock) {
    echo "<script>alert('Thiếu thông tin người dùng');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

$currentUserId = $_SESSION['uid'];

// Không thể tự chặn mình
if ($currentUserId == $userToBlock) {
    echo "<script>alert('Không thể chặn chính mình');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

// Xử lý chặn
include_once('controller/cQuanHeNguoiDung.php');
$controller = new controlQuanHeNguoiDung();

if ($controller->blockUser($currentUserId, $userToBlock)) {
    echo "<script>alert('Đã chặn người dùng thành công');</script>";
    echo "<script>window.location.href='home.php?page=tinnhan';</script>";
} else {
    echo "<script>alert('Có lỗi xảy ra khi chặn người dùng');</script>";
    echo "<script>window.history.back();</script>";
}
?>
