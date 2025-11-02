<?php
// Kiểm tra đăng nhập
if (!isset($_SESSION['uid'])) {
    header("Location: home.php?page=dangnhap");
    exit();
}

// Kiểm tra có đủ thông tin không
if (!isset($_POST['postId']) || !isset($_POST['ownerId']) || !isset($_POST['reason'])) {
    echo "<script>alert('Thiếu thông tin báo cáo');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

$currentUserId = $_SESSION['uid'];
$postId = $_POST['postId'];
$postOwnerId = $_POST['ownerId'];
$reason = trim($_POST['reason']);

// Validate
if (empty($reason)) {
    echo "<script>alert('Vui lòng nhập lý do báo cáo');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

// Không thể tự báo cáo mình
if ($currentUserId == $postOwnerId) {
    echo "<script>alert('Không thể báo cáo bài viết của chính mình');</script>";
    echo "<script>window.history.back();</script>";
    exit();
}

// Lấy nội dung bài viết để lưu vào báo cáo (snapshot)
// TODO: Cần thêm method mGetTinTucById vào model mBanTin
$noiDungViPham = 'Bài viết ID: ' . $postId;

// Xử lý báo cáo bài viết
include_once('controller/cQuanHeNguoiDung.php');
$controller = new controlQuanHeNguoiDung();

if ($controller->reportPost($currentUserId, $postId, $postOwnerId, $reason, $noiDungViPham)) {
    echo "<script>alert('Đã gửi báo cáo bài viết thành công. Chúng tôi sẽ xem xét trong thời gian sớm nhất.');</script>";
    echo "<script>window.location.href='home.php?page=bantin';</script>";
} else {
    echo "<script>alert('Có lỗi xảy ra khi gửi báo cáo');</script>";
    echo "<script>window.history.back();</script>";
}
?>
