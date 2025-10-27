<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['uid'])) {
    header("Location: home.php?page=dangnhap");
    exit();
}

// Kiểm tra có userId và lý do không
if (!isset($_POST['uid']) || !isset($_POST['reason'])) {
    echo "<script>alert('Thiếu thông tin báo cáo');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

$currentUserId = $_SESSION['uid'];
$userToReport = $_POST['uid'];
$reason = trim($_POST['reason']);

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

// URL nội dung (trang chat hiện tại)
$urlNoiDung = "home.php?page=tinnhan&uid=" . $userToReport;

// Xử lý báo cáo
include_once('controller/cQuanHeNguoiDung.php');
$controller = new controlQuanHeNguoiDung();

if ($controller->reportUser($currentUserId, $userToReport, $reason, $urlNoiDung)) {
    echo "<script>alert('Đã gửi báo cáo thành công');</script>";
    echo "<script>window.history.back();</script>";
} else {
    echo "<script>alert('Có lỗi xảy ra khi gửi báo cáo');</script>";
    echo "<script>window.history.back();</script>";
}
?>
