<?php
/**
 * Debug MoMo Payment Request
 * Chạy file này để xem chi tiết request gửi đến MoMo
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/config/momo.config.php');
require_once(__DIR__ . '/helper/MoMoHelper.php');

echo "<h1>Debug MoMo Payment Request</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    pre { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; overflow-x: auto; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; }
</style>";

echo "<div class='section'>";
echo "<h2>1. Cấu hình hiện tại</h2>";
echo "<pre>";
echo "Partner Code: " . MOMO_PARTNER_CODE . "\n";
echo "Access Key: " . MOMO_ACCESS_KEY . "\n";
echo "Secret Key: " . substr(MOMO_SECRET_KEY, 0, 10) . "..." . "\n";
echo "Endpoint: " . MOMO_ENDPOINT . "\n";
echo "Return URL: " . MOMO_RETURN_URL . "\n";
echo "Notify URL: " . MOMO_NOTIFY_URL . "\n";
echo "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>2. Tạo test request</h2>";

$momoHelper = new MoMoHelper();

// Test data
$orderId = "TEST_" . time();
$amount = 50000;
$orderInfo = "Test Payment - Gói Premium 1 tháng";
$extraData = [
    'userId' => 999,
    'packageId' => 'premium_1month',
    'duration' => 30
];

echo "<pre>";
echo "Order ID: $orderId\n";
echo "Amount: " . number_format($amount) . " VNĐ\n";
echo "Order Info: $orderInfo\n";
echo "Extra Data: " . json_encode($extraData, JSON_PRETTY_PRINT) . "\n";
echo "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>3. Gửi request đến MoMo</h2>";
echo "<p><em>Đang gửi request...</em></p>";

$response = $momoHelper->createPaymentRequest($orderId, $amount, $orderInfo, $extraData);

echo "<h3>Response từ MoMo:</h3>";
echo "<pre>";
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

if (isset($response['resultCode'])) {
    if ($response['resultCode'] == 0) {
        echo "<p class='success'>✓ Thành công! ResultCode = 0</p>";
        if (isset($response['payUrl'])) {
            echo "<p><strong>Payment URL:</strong></p>";
            echo "<pre>" . htmlspecialchars($response['payUrl']) . "</pre>";
            echo "<p><a href='{$response['payUrl']}' target='_blank' class='btn'>Mở link thanh toán</a></p>";
        }
        if (isset($response['qrCodeUrl'])) {
            echo "<p><strong>QR Code:</strong></p>";
            echo "<img src='{$response['qrCodeUrl']}' alt='QR Code' style='max-width: 300px;'>";
        }
    } else {
        echo "<p class='error'>✗ Lỗi! ResultCode = {$response['resultCode']}</p>";
        echo "<p><strong>Message:</strong> " . ($response['message'] ?? 'N/A') . "</p>";
        
        // Giải thích mã lỗi
        $errorCodes = [
            1000 => 'Giao dịch đã được khởi tạo, chờ người dùng xác nhận thanh toán',
            1001 => 'Giao dịch thanh toán thất bại do người dùng từ chối xác nhận thanh toán',
            1002 => 'Giao dịch bị từ chối do nhà phát hành tài khoản thanh toán',
            1003 => 'Giao dịch bị huỷ do quá thời gian thanh toán',
            1004 => 'Giao dịch thất bại do số dư tài khoản không đủ để thanh toán',
            1005 => 'Giao dịch thất bại do url hoặc QR code đã hết hạn',
            1006 => 'Giao dịch thất bại do người dùng đã từ chối xác nhận thanh toán',
            2001 => 'Giao dịch thất bại do sai tham số',
            9000 => 'Giao dịch thành công',
            9999 => 'Giao dịch thất bại (lỗi hệ thống)'
        ];
        
        if (isset($errorCodes[$response['resultCode']])) {
            echo "<p><strong>Giải thích:</strong> " . $errorCodes[$response['resultCode']] . "</p>";
        }
        
        // Gợi ý sửa lỗi
        if ($response['resultCode'] == 1005) {
            echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 15px;'>";
            echo "<h4>Cách khắc phục lỗi 1005:</h4>";
            echo "<ol>";
            echo "<li><strong>Kiểm tra Return URL & Notify URL:</strong><br>";
            echo "- Return URL: " . MOMO_RETURN_URL . "<br>";
            echo "- Notify URL: " . MOMO_NOTIFY_URL . "<br>";
            echo "→ Phải là URL public (không dùng localhost cho Notify URL)</li>";
            echo "<li><strong>Kiểm tra Signature:</strong><br>";
            echo "- Đảm bảo thứ tự fields trong rawHash đúng<br>";
            echo "- Secret Key phải chính xác</li>";
            echo "<li><strong>Kiểm tra Partner Code:</strong><br>";
            echo "- Test credentials: MOMOBKUN20180529<br>";
            echo "- Nếu dùng production, phải có credentials thật từ MoMo Business</li>";
            echo "<li><strong>Test lại với credentials mới:</strong><br>";
            echo "- Đăng ký tại: <a href='https://developers.momo.vn' target='_blank'>https://developers.momo.vn</a></li>";
            echo "</ol>";
            echo "</div>";
        }
    }
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>4. Check error logs</h2>";
echo "<p>Xem chi tiết trong error log của server (error.log hoặc php_error.log)</p>";
echo "<p>Các thông tin debug đã được ghi vào error_log():</p>";
echo "<ul>";
echo "<li>Raw Hash signature</li>";
echo "<li>Request data gửi đến MoMo</li>";
echo "<li>Response từ MoMo</li>";
echo "<li>CURL errors (nếu có)</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><em>Debug completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
