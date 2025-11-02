<?php
// Kiểm tra đăng nhập
if (!isset($_SESSION['uid'])) {
    header("Location: home.php?page=dangnhap");
    exit();
}

// Kiểm tra có đủ thông tin không
if (!isset($_POST['uid']) || !isset($_POST['messageId']) || !isset($_POST['reason'])) {
    echo "<script>alert('Thiếu thông tin báo cáo');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

$currentUserId = $_SESSION['uid'];
$userToReport = $_POST['uid'];
$messageId = $_POST['messageId'];
$reason = trim($_POST['reason']);
$messageContent = isset($_POST['messageContent']) ? trim($_POST['messageContent']) : '';

// Validate
if (empty($reason)) {
    echo "<script>alert('Vui lòng nhập lý do báo cáo');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

// Không thể tự báo cáo mình
if ($currentUserId == $userToReport) {
    echo "<script>alert('Không thể báo cáo chính mình');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

// Xử lý báo cáo tin nhắn
include_once('controller/cQuanHeNguoiDung.php');
$controller = new controlQuanHeNguoiDung();

// Gọi hàm báo cáo tin nhắn với context
if ($controller->reportMessage($currentUserId, $messageId, $userToReport, $reason, $messageContent, $currentUserId, $userToReport)) {
    echo "<script>alert('Đã gửi báo cáo tin nhắn thành công. Chúng tôi sẽ xem xét trong thời gian sớm nhất.');</script>";
    echo "<script>window.location.href='home.php?page=tinnhan&uid=" . $userToReport . "';</script>";
} else {
    echo "<script>alert('Có lỗi xảy ra khi gửi báo cáo');</script>";
    echo "<script>window.history.back();</script>";
}
?>
