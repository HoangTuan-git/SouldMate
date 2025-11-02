<?php
/**
 * File xử lý return URL từ MoMo (người dùng quay lại)
 */
session_start();
require_once(__DIR__ . '/../controller/cPayment.php');

$controller = new controlPayment();

// Lấy dữ liệu từ GET params
$data = $_GET;

// Xử lý callback
$result = $controller->handleCallback($data);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán - SoulMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .result-container {
            max-width: 500px;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .order-info p {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <?php if ($result['success']): ?>
            <i class="fas fa-check-circle success-icon"></i>
            <h2 class="text-success mb-3">Thanh toán thành công!</h2>
            <p class="text-muted">Cảm ơn bạn đã nâng cấp lên Premium</p>
            
            <div class="order-info">
                <p>
                    <strong>Mã giao dịch:</strong>
                    <span><?= htmlspecialchars($_GET['transId'] ?? 'N/A') ?></span>
                </p>
                <p>
                    <strong>Mã đơn hàng:</strong>
                    <span><?= htmlspecialchars($_GET['orderId'] ?? 'N/A') ?></span>
                </p>
                <p>
                    <strong>Số tiền:</strong>
                    <span><?= number_format($_GET['amount'] ?? 0) ?> VNĐ</span>
                </p>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-crown me-2"></i>
                Tài khoản Premium của bạn đã được kích hoạt!
            </div>

            <a href="../home.php?page=me" class="btn btn-primary btn-lg mt-3">
                <i class="fas fa-home me-2"></i>Về trang cá nhân
            </a>
        <?php else: ?>
            <i class="fas fa-times-circle error-icon"></i>
            <h2 class="text-danger mb-3">Thanh toán thất bại!</h2>
            <p class="text-muted"><?= htmlspecialchars($result['message']) ?></p>
            
            <div class="order-info">
                <p>
                    <strong>Mã đơn hàng:</strong>
                    <span><?= htmlspecialchars($_GET['orderId'] ?? 'N/A') ?></span>
                </p>
                <p>
                    <strong>Mã lỗi:</strong>
                    <span><?= htmlspecialchars($_GET['resultCode'] ?? 'N/A') ?></span>
                </p>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>
                Vui lòng thử lại hoặc liên hệ hỗ trợ nếu vấn đề vẫn tiếp diễn.
            </div>

            <div class="d-grid gap-2">
                <a href="../home.php?page=premium" class="btn btn-primary btn-lg">
                    <i class="fas fa-redo me-2"></i>Thử lại
                </a>
                <a href="../home.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Về trang chủ
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
