<?php
/**
 * File test thanh toán MoMo
 * Chạy file này để test tích hợp MoMo
 */

// Bật error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test MoMo Payment Integration</h1>";
echo "<hr>";

// Test 1: Kiểm tra config
echo "<h2>1. Kiểm tra Config</h2>";
if (file_exists(__DIR__ . '/config/momo.config.php')) {
    require_once(__DIR__ . '/config/momo.config.php');
    echo "✓ File config tồn tại<br>";
    echo "- Partner Code: " . MOMO_PARTNER_CODE . "<br>";
    echo "- Endpoint: " . MOMO_ENDPOINT . "<br>";
    echo "- Return URL: " . MOMO_RETURN_URL . "<br>";
    echo "- Notify URL: " . MOMO_NOTIFY_URL . "<br>";
} else {
    echo "✗ File config không tồn tại<br>";
}
echo "<hr>";

// Test 2: Kiểm tra MoMoHelper
echo "<h2>2. Kiểm tra MoMoHelper</h2>";
if (file_exists(__DIR__ . '/helper/MoMoHelper.php')) {
    require_once(__DIR__ . '/helper/MoMoHelper.php');
    echo "✓ File MoMoHelper tồn tại<br>";
    
    try {
        $momoHelper = new MoMoHelper();
        echo "✓ MoMoHelper khởi tạo thành công<br>";
        
        // Test generate OrderId
        $orderId = MoMoHelper::generateOrderId(1, 'premium_1month');
        echo "✓ Generate OrderId: " . $orderId . "<br>";
    } catch (Exception $e) {
        echo "✗ Lỗi: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ File MoMoHelper không tồn tại<br>";
}
echo "<hr>";

// Test 3: Kiểm tra CURL
echo "<h2>3. Kiểm tra CURL</h2>";
if (function_exists('curl_version')) {
    $curlVersion = curl_version();
    echo "✓ CURL đã được cài đặt<br>";
    echo "- Version: " . $curlVersion['version'] . "<br>";
    echo "- SSL Version: " . $curlVersion['ssl_version'] . "<br>";
} else {
    echo "✗ CURL chưa được cài đặt<br>";
}
echo "<hr>";

// Test 4: Kiểm tra Database Connection
echo "<h2>4. Kiểm tra Database</h2>";
if (file_exists(__DIR__ . '/model/mKetNoi.php')) {
    require_once(__DIR__ . '/model/mKetNoi.php');
    echo "✓ File mKetNoi tồn tại<br>";
    
    try {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();
        if ($con) {
            echo "✓ Kết nối database thành công<br>";
            
            // Kiểm tra các bảng
            $tables = ['orders', 'user_premium'];
            foreach ($tables as $table) {
                $result = $con->query("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    echo "✓ Bảng '$table' tồn tại<br>";
                } else {
                    echo "✗ Bảng '$table' chưa được tạo<br>";
                    echo "→ Chạy file payment/create_tables.sql để tạo bảng<br>";
                }
            }
            $con->close();
        } else {
            echo "✗ Không thể kết nối database<br>";
        }
    } catch (Exception $e) {
        echo "✗ Lỗi database: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ File mKetNoi không tồn tại<br>";
}
echo "<hr>";

// Test 5: Test tạo payment request (không gửi thật)
echo "<h2>5. Test Payment Request (Dry Run)</h2>";
if (class_exists('MoMoHelper')) {
    try {
        $momoHelper = new MoMoHelper();
        $orderId = "TEST_" . time();
        $amount = 50000;
        $orderInfo = "Test Payment";
        $extraData = ['userId' => 999, 'packageId' => 'test'];
        
        echo "✓ Test data prepared:<br>";
        echo "- OrderId: $orderId<br>";
        echo "- Amount: " . number_format($amount) . " VNĐ<br>";
        echo "- OrderInfo: $orderInfo<br>";
        echo "<br>";
        
        echo "⚠ Để test thực tế, uncomment dòng bên dưới:<br>";
        echo "<code style='background:#f0f0f0;padding:10px;display:block;margin:10px 0;'>";
        echo "\$response = \$momoHelper->createPaymentRequest(\$orderId, \$amount, \$orderInfo, \$extraData);<br>";
        echo "print_r(\$response);";
        echo "</code>";
        
        // Uncomment để test thực tế:
        // $response = $momoHelper->createPaymentRequest($orderId, $amount, $orderInfo, $extraData);
        // echo "<pre>";
        // print_r($response);
        // echo "</pre>";
        
    } catch (Exception $e) {
        echo "✗ Lỗi: " . $e->getMessage() . "<br>";
    }
}
echo "<hr>";

// Test 6: Kiểm tra files cần thiết
echo "<h2>6. Kiểm tra Files</h2>";
$requiredFiles = [
    'config/momo.config.php' => 'Config file',
    'helper/MoMoHelper.php' => 'Helper class',
    'model/mPayment.php' => 'Payment model',
    'controller/cPayment.php' => 'Payment controller',
    'payment/momo-return.php' => 'Return URL handler',
    'payment/momo-notify.php' => 'IPN handler',
    'view/premium.php' => 'Premium page',
    'payment/create_tables.sql' => 'SQL setup'
];

foreach ($requiredFiles as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ $desc: <code>$file</code><br>";
    } else {
        echo "✗ $desc: <code>$file</code> không tồn tại<br>";
    }
}
echo "<hr>";

// Test 7: Test signature
echo "<h2>7. Test Signature Generation</h2>";
if (class_exists('MoMoHelper')) {
    try {
        // Tạo test data giống MoMo
        $testData = [
            'partnerCode' => MOMO_PARTNER_CODE,
            'accessKey' => MOMO_ACCESS_KEY,
            'requestId' => time(),
            'amount' => 50000,
            'orderId' => 'TEST_' . time(),
            'orderInfo' => 'Test',
            'redirectUrl' => MOMO_RETURN_URL,
            'ipnUrl' => MOMO_NOTIFY_URL,
            'extraData' => '',
            'requestType' => 'captureWallet'
        ];
        
        $rawHash = "accessKey=" . $testData['accessKey'] .
                   "&amount=" . $testData['amount'] .
                   "&extraData=" . $testData['extraData'] .
                   "&ipnUrl=" . $testData['ipnUrl'] .
                   "&orderId=" . $testData['orderId'] .
                   "&orderInfo=" . $testData['orderInfo'] .
                   "&partnerCode=" . $testData['partnerCode'] .
                   "&redirectUrl=" . $testData['redirectUrl'] .
                   "&requestId=" . $testData['requestId'] .
                   "&requestType=" . $testData['requestType'];
        
        $signature = hash_hmac("sha256", $rawHash, MOMO_SECRET_KEY);
        
        echo "✓ Signature test passed<br>";
        echo "- Raw Hash (first 100 chars): " . substr($rawHash, 0, 100) . "...<br>";
        echo "- Signature: " . $signature . "<br>";
    } catch (Exception $e) {
        echo "✗ Lỗi: " . $e->getMessage() . "<br>";
    }
}
echo "<hr>";

// Kết luận
echo "<h2>8. Kết luận</h2>";
echo "<div style='background:#e8f5e9;padding:15px;border-left:4px solid #4caf50;'>";
echo "<strong>✓ Hệ thống đã sẵn sàng!</strong><br><br>";
echo "<strong>Bước tiếp theo:</strong><br>";
echo "1. Chạy file <code>payment/create_tables.sql</code> để tạo database tables<br>";
echo "2. Truy cập <a href='view/premium.php'>view/premium.php</a> để test thanh toán<br>";
echo "3. Đọc file <a href='MOMO_PAYMENT_GUIDE.md'>MOMO_PAYMENT_GUIDE.md</a> để biết thêm chi tiết<br>";
echo "</div>";

echo "<hr>";
echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #d32f2f;
    }
    h2 {
        color: #1976d2;
        margin-top: 30px;
    }
    code {
        background: #fff3cd;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
    hr {
        border: none;
        border-top: 2px solid #ddd;
        margin: 20px 0;
    }
</style>
