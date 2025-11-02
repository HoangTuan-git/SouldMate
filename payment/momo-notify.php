<?php
/**
 * File xử lý IPN (Instant Payment Notification) từ MoMo
 * MoMo sẽ gửi thông báo đến đây khi thanh toán hoàn tất
 */
require_once(__DIR__ . '/../controller/cPayment.php');

// Log request để debug (optional)
$logFile = __DIR__ . '/momo-ipn.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . json_encode($_POST) . "\n", FILE_APPEND);

$controller = new controlPayment();

// Lấy dữ liệu từ POST
$data = $_POST;

// Xử lý callback
$result = $controller->handleCallback($data);

// Trả về response cho MoMo
header('Content-Type: application/json');
if ($result['success']) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Payment processed successfully'
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $result['message']
    ]);
}
exit;
