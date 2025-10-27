<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start(); // Bắt đầu output buffering
session_start();

// Xóa mọi output trước đó
ob_clean();

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['uid'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thực hiện hành động này'
    ]);
    exit;
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$maBaiDang = $data['maBaiDang'] ?? null;

if (!$maBaiDang) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin bài đăng'
    ]);
    exit;
}

// Gọi controller xử lý
require_once(__DIR__ . '/../controller/cTuongTac.php');
require_once(__DIR__ . '/../model/mTuongTac.php');
require_once(__DIR__ . '/../model/mKetNoi.php');

$controller = new cTuongTac();
$maNguoiDung = $_SESSION['uid'];

try {
    $result = $controller->ToggleLike($maNguoiDung, $maBaiDang);
    
    // Log để debug
    error_log("Toggle like result: " . print_r($result, true));
    
    ob_clean(); // Xóa mọi output trước khi echo JSON
    echo json_encode([
        'success' => $result['success'],
        'action' => $result['action'],
        'likeCount' => $result['likeCount'],
        'isLiked' => $result['isLiked']
    ]);
} catch (Exception $e) {
    error_log("Toggle like error: " . $e->getMessage());
    ob_clean(); // Xóa mọi output trước khi echo JSON
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}
exit;
