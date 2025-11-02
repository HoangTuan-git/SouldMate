<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;
$maBaiDang = isset($data['maBaiDang']) ? (int)$data['maBaiDang'] : 0;
$limit = isset($data['limit']) ? (int)$data['limit'] : 100;
$offset = isset($data['offset']) ? (int)$data['offset'] : 0;

if (!$action || !$maBaiDang) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit;
}

require_once(__DIR__ . '/../controller/cBinhLuan.php');
$controller = new cBinhLuan();
$maNguoiDung = (int)$_SESSION['uid'];

if ($action === 'add') {
    $noiDung = trim($data['noiDung'] ?? '');
    if (empty($noiDung)) {
        echo json_encode(['success' => false, 'message' => 'Nội dung trống']);
        exit;
    }
    
    $result = $controller->AddComment($maNguoiDung, $maBaiDang, $noiDung);
    
    if ($result) {
        $comments = $controller->GetComments($maBaiDang);
        echo json_encode([
            'success' => true,
            'comments' => $comments,
            'commentCount' => count($comments)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể thêm']);
    }
    
} elseif ($action === 'get') {
    $comments = $controller->GetComments($maBaiDang, $limit, $offset);
    $totalCount = $controller->CountComments($maBaiDang);
    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'commentCount' => count($comments),
        'totalCount' => $totalCount,
        'offset' => $offset,
        'limit' => $limit
    ]);
    
} elseif ($action === 'delete') {
    $maBinhLuan = isset($data['maBinhLuan']) ? (int)$data['maBinhLuan'] : 0;
    if (!$maBinhLuan) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
        exit;
    }
    
    $result = $controller->DeleteComment($maBinhLuan, $maNguoiDung);
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Đã xóa' : 'Không thể xóa'
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}
