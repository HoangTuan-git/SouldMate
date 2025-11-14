<?php
/**
 * Trang Premium - Chọn gói và thanh toán
 */
require_once(__DIR__ . '/../config/momo.config.php');
require_once(__DIR__ . '/../controller/cPayment.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['uid'])) {
    header('Location: dangnhap.php');
    exit;
}

$userId = $_SESSION['uid'];
$controller = new controlPayment();

// Kiểm tra trạng thái Premium hiện tại
$isPremium = $controller->checkPremiumStatus($userId);
$premiumInfo = $isPremium ? $controller->getPremiumInfo($userId) : null;

// Xử lý thanh toán
$paymentError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package'])) {
    // Kiểm tra trạng thái hồ sơ trước khi thanh toán
    include_once(__DIR__ . '/../controller/cHoSo.php');
    $hosoController = new controlHoSo();
    $profileResult = $hosoController->getProfile($userId);
    
    if (!$profileResult || $profileResult->num_rows == 0) {
        $paymentError = "Bạn chưa có hồ sơ! Vui lòng tạo hồ sơ trước.";
        echo '<script>
            alert("' . $paymentError . '");
            window.location.href = "home.php?page=profile_quiz";
        </script>';
        exit;
    }
    
    $profile = $profileResult->fetch_assoc();
    $trangThaiHenHo = $profile['trangThaiHenHo'] ?? 'trải nghiệm';
    
    // Kiểm tra nếu là trải nghiệm thì không cho mua Premium
    if ($trangThaiHenHo === 'trải nghiệm') {
        $paymentError = "Chỉ thành viên nghiêm túc mới có thể đăng ký Premium!";
        echo '<script>
            alert("Chỉ thành viên nghiêm túc mới có thể đăng ký Premium!\\n\\nVui lòng chuyển sang chế độ nghiêm túc tại trang chỉnh sửa hồ sơ.");
            window.location.href = "home.php?page=ChinhSuaHS";
        </script>';
        exit;
    }
    
    // Nếu hợp lệ thì tiếp tục xử lý thanh toán
    $packageId = $_POST['package'];
    
    $result = $controller->createPayment($userId, $packageId);
    
    if ($result['success']) {
        // Chuyển hướng đến trang thanh toán MoMo
        header('Location: ' . $result['payUrl']);
        exit;
    } else {
        $paymentError = $result['message'];
    }
}

// Lấy danh sách gói
$packages = PREMIUM_PACKAGES;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nâng cấp Premium - SoulMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .premium-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .premium-header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        .premium-header h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .premium-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .premium-card.featured {
            border: 3px solid #ffd700;
            position: relative;
        }
        .premium-card.featured::before {
            content: "PHỔ BIẾN NHẤT";
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffd700;
            color: #333;
            padding: 5px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
        }
        .package-price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        .package-duration {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        .package-features {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }
        .package-features li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .package-features li:last-child {
            border-bottom: none;
        }
        .package-features i {
            color: #28a745;
            margin-right: 10px;
        }
        .current-premium {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #333;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .current-premium h3 {
            margin-bottom: 20px;
        }
        .btn-purchase {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 10px;
        }
        .features-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-top: 50px;
        }
        .feature-item {
            text-align: center;
            margin-bottom: 30px;
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .payment-methods {
            text-align: center;
            margin-top: 30px;
            color: white;
        }
        .payment-methods img {
            height: 40px;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="premium-container">
        <!-- Header -->
        <div class="premium-header">
            <h1><i class="fas fa-crown"></i> Nâng cấp Premium</h1>
            <p class="lead">Trải nghiệm đầy đủ tính năng và tìm kiếm nửa kia của bạn nhanh hơn</p>
        </div>

        <!-- Thông báo lỗi -->
        <?php if ($paymentError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($paymentError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Trạng thái Premium hiện tại -->
        <?php if ($isPremium): ?>
            <div class="current-premium">
                <h3><i class="fas fa-crown me-2"></i>Bạn đang là thành viên Premium</h3>
                <p class="mb-2"><strong>Gói:</strong> <?= htmlspecialchars($premiumInfo['loaiGoi'] ?? 'Premium') ?></p>
                <p class="mb-2"><strong>Ngày hết hạn:</strong> <?= date('d/m/Y H:i', strtotime($premiumInfo['ngayKetThuc'])) ?></p>
                <p class="mb-2"><strong>Còn lại:</strong> <?= $premiumInfo['soNgayConLai'] ?> ngày</p>
                <p class="text-muted mb-0">Gia hạn ngay để tiếp tục sử dụng các tính năng cao cấp</p>
            </div>
        <?php endif; ?>

        <!-- Danh sách gói Premium -->
        <div class="row">
            <?php 
            $featured = ['premium_3months']; // Gói nổi bật
            foreach ($packages as $id => $package): 
            ?>
                <div class="col-lg-3 col-md-6">
                    <div class="premium-card <?= in_array($id, $featured) ? 'featured' : '' ?>">
                        <h3 class="text-center"><?= htmlspecialchars($package['name']) ?></h3>
                        <div class="package-price text-center">
                            <?= number_format($package['price']) ?> ₫
                        </div>
                        <div class="package-duration text-center">
                            <?= $package['duration'] ?> ngày
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="package" value="<?= htmlspecialchars($id) ?>">
                            <button type="submit" class="btn btn-primary btn-purchase">
                                <i class="fas fa-shopping-cart me-2"></i>Mua ngay
                            </button>
                        </form>

                        <ul class="package-features">
                            <li><i class="fas fa-check"></i> Lượt thích không giới hạn</li>
                            <li><i class="fas fa-check"></i> Xem ai đã thích bạn</li>
                            <li><i class="fas fa-check"></i> Boost hồ sơ</li>
                            <li><i class="fas fa-check"></i> Rewind lượt vuốt</li>
                            <li><i class="fas fa-check"></i> 5 Super Like/ngày</li>
                            <li><i class="fas fa-check"></i> 1 Boost miễn phí/tháng</li>
                            <li><i class="fas fa-check"></i> Ưu tiên trong tìm kiếm</li>
                            <li><i class="fas fa-check"></i> Không quảng cáo</li>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tính năng Premium -->
        <div class="features-section">
            <h2 class="text-center mb-5">Tại sao nên nâng cấp Premium?</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <h4>Không giới hạn</h4>
                        <p>Thích và match không giới hạn mỗi ngày</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4>Xem ai thích bạn</h4>
                        <p>Biết ngay ai đã thích bạn để match nhanh hơn</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4>Boost hồ sơ</h4>
                        <p>Xuất hiện nhiều hơn trong kết quả tìm kiếm</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h4>Rewind</h4>
                        <p>Quay lại lượt vuốt trước đó nếu vuốt nhầm</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="payment-methods">
            <h4 class="mb-3">Phương thức thanh toán</h4>
            <img src="img/MoMo_Logo.png" alt="MoMo" style="background: white; padding: 10px; border-radius: 10px;">
            <p class="mt-3 text-white-50">Thanh toán an toàn với MoMo</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
