<?php
/**
 * SIMULATE MOMO PAYMENT - Test không cần app
 * File này giả lập callback từ MoMo để test hệ thống
 * CHỈ DÙNG CHO TEST - KHÔNG DÙNG TRÊN PRODUCTION
 */

session_start();
require_once(__DIR__ . '/../controller/cPayment.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['uid'])) {
    die('Vui lòng đăng nhập trước');
}

$userId = $_SESSION['uid'];
$controller = new controlPayment();

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Simulate MoMo Payment</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
<div class='container py-5'>
    <div class='row justify-content-center'>
        <div class='col-md-8'>
            <div class='card shadow'>
                <div class='card-header bg-danger text-white'>
                    <h3 class='mb-0'><i class='bi bi-exclamation-triangle'></i> Simulate Payment (TEST ONLY)</h3>
                </div>
                <div class='card-body'>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulate'])) {
    $packageId = $_POST['package'];
    
    // Tạo đơn hàng thật
    $result = $controller->createPayment($userId, $packageId);
    
    if ($result['success']) {
        $orderId = $result['orderId'];
        
        echo "<div class='alert alert-info'>
            <strong>Đơn hàng đã tạo:</strong> $orderId
        </div>";
        
        // Giả lập callback từ MoMo (thanh toán thành công)
        $fakeCallback = [
            'partnerCode' => MOMO_PARTNER_CODE,
            'orderId' => $orderId,
            'requestId' => time(),
            'amount' => PREMIUM_PACKAGES[$packageId]['price'],
            'orderInfo' => 'Test Payment',
            'orderType' => 'momo_wallet',
            'transId' => 'TEST_TRANS_' . time(),
            'resultCode' => 0, // 0 = Success
            'message' => 'Success',
            'payType' => 'qr',
            'responseTime' => time() * 1000,
            'extraData' => json_encode([
                'userId' => $userId,
                'packageId' => $packageId,
                'duration' => PREMIUM_PACKAGES[$packageId]['duration']
            ]),
            'signature' => 'fake_signature_for_test'
        ];
        
        // Xử lý callback (bỏ qua verify signature trong test)
        require_once(__DIR__ . '/../model/mPayment.php');
        $model = new modelPayment();
        
        // Cập nhật đơn hàng
        $model->updateOrderStatus($orderId, 'completed', $fakeCallback['transId']);
        
        // Kích hoạt Premium
        $duration = PREMIUM_PACKAGES[$packageId]['duration'];
        $model->activatePremium($userId, $orderId, $duration);
        
        // Log
        $model->logTransaction($orderId, 'test', $fakeCallback);
        
        echo "<div class='alert alert-success'>
            <h4><i class='bi bi-check-circle'></i> Thanh toán giả lập thành công!</h4>
            <p><strong>Mã giao dịch:</strong> {$fakeCallback['transId']}</p>
            <p><strong>Gói:</strong> " . PREMIUM_PACKAGES[$packageId]['name'] . "</p>
            <p><strong>Thời hạn:</strong> $duration ngày</p>
        </div>";
        
        echo "<a href='../view/premium.php' class='btn btn-primary'>Xem trạng thái Premium</a>";
        echo " <a href='../view/me.php' class='btn btn-outline-secondary'>Về trang cá nhân</a>";
        
    } else {
        echo "<div class='alert alert-danger'>Lỗi: {$result['message']}</div>";
    }
    
} else {
    // Form chọn gói
    require_once(__DIR__ . '/../config/momo.config.php');
    
    echo "<div class='alert alert-warning'>
        <strong>⚠️ CHÚ Ý:</strong> File này chỉ dùng để TEST. 
        Nó sẽ giả lập thanh toán thành công mà KHÔNG CẦN qua MoMo thật.
    </div>";
    
    echo "<h4>Chọn gói Premium để test:</h4>";
    echo "<form method='POST'>";
    
    foreach (PREMIUM_PACKAGES as $id => $package) {
        echo "<div class='form-check mb-3 p-3 border rounded'>
            <input class='form-check-input' type='radio' name='package' value='$id' id='$id' required>
            <label class='form-check-label' for='$id'>
                <strong>{$package['name']}</strong> - " . number_format($package['price']) . " VNĐ
                <br><small class='text-muted'>{$package['description']} ({$package['duration']} ngày)</small>
            </label>
        </div>";
    }
    
    echo "<button type='submit' name='simulate' class='btn btn-danger btn-lg w-100'>
        <i class='bi bi-lightning'></i> Giả lập thanh toán thành công
    </button>";
    echo "</form>";
    
    echo "<hr>";
    echo "<p class='text-muted'><small>
        <strong>Lưu ý:</strong> File này bỏ qua xác thực signature từ MoMo. 
        Trên production, PHẢI XÓA file này để tránh bảo mật.
    </small></p>";
}

echo "       </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>";
?>
