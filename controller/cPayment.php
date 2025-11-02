<?php
include_once(__DIR__ . '/../model/mPayment.php');
include_once(__DIR__ . '/../helper/MoMoHelper.php');

/**
 * Controller xử lý thanh toán
 */
class controlPayment
{
    private $model;
    private $momoHelper;

    public function __construct()
    {
        $this->model = new modelPayment();
        $this->momoHelper = new MoMoHelper();
    }

    /**
     * Tạo yêu cầu thanh toán
     */
    public function createPayment($userId, $packageId)
    {
        require_once(__DIR__ . '/../config/momo.config.php');
        
        // Kiểm tra gói có tồn tại không
        $packages = PREMIUM_PACKAGES;
        if (!isset($packages[$packageId])) {
            return [
                'success' => false,
                'message' => 'Gói Premium không tồn tại!'
            ];
        }

        $package = $packages[$packageId];
        $amount = $package['price'];
        $orderInfo = $package['name'] . ' - ' . $package['description'];

        // Tạo mã đơn hàng (chỉ dùng cho MoMo, DB dùng AUTO_INCREMENT)
        $orderId = MoMoHelper::generateOrderId($userId, $packageId);

        // Lưu đơn hàng vào database - trả về maDonHang INT
        $orderCreated = $this->model->createOrder($userId, $packageId, $amount, $orderId, 'pending');

        if (!$orderCreated) {
            error_log("Failed to create order: userId=$userId, packageId=$packageId, amount=$amount");
            return [
                'success' => false,
                'message' => 'Không thể tạo đơn hàng! Vui lòng kiểm tra database.'
            ];
        }
        
        // $orderCreated là INT (maDonHang từ database AUTO_INCREMENT)
        $dbOrderId = $orderCreated;
        error_log("Order created with DB ID: $dbOrderId");

        // Tạo request thanh toán MoMo (dùng orderId string cho MoMo)
        $extraData = [
            'userId' => $userId,
            'packageId' => $packageId,
            'duration' => $package['duration'],
            'dbOrderId' => $dbOrderId // Lưu ID thật từ DB để callback dùng
        ];

        $momoResponse = $this->momoHelper->createPaymentRequest(
            $orderId,
            $amount,
            $orderInfo,
            $extraData
        );

        if (isset($momoResponse['resultCode']) && $momoResponse['resultCode'] == 0) {
            return [
                'success' => true,
                'payUrl' => $momoResponse['payUrl'],
                'orderId' => $orderId, // orderId string cho MoMo
                'dbOrderId' => $dbOrderId, // maDonHang INT trong DB
                'qrCodeUrl' => $momoResponse['qrCodeUrl'] ?? null
            ];
        }

        return [
            'success' => false,
            'message' => $momoResponse['message'] ?? 'Lỗi kết nối MoMo!',
            'resultCode' => $momoResponse['resultCode'] ?? 99
        ];
    }

    /**
     * Xử lý callback từ MoMo
     */
    public function handleCallback($data)
    {
        // Lấy dbOrderId từ extraData (đây là INT từ DB)
        $extraData = isset($data['extraData']) ? json_decode(base64_decode($data['extraData']), true) : null;
        $dbOrderId = $extraData['dbOrderId'] ?? null;
        
        // Log giao dịch
        if ($dbOrderId) {
            $this->model->logTransaction($dbOrderId, 'return', $data);
        }
        
        // Debug log
        error_log("Callback data: " . json_encode($data));
        error_log("DB Order ID from extraData: " . $dbOrderId);

        // Skip signature verification in test environment for GET callbacks (return URL)
        // Vì MoMo test environment không gửi đầy đủ parameters cho verify
        $isTestEnv = (strpos(MOMO_ENDPOINT, 'test-payment') !== false);
        $isReturnCallback = !isset($data['orderType']); // Return URL không có orderType
        
        if (!$isTestEnv || !$isReturnCallback) {
            // Xác thực signature cho production hoặc IPN callback
            if (!$this->momoHelper->verifySignature($data)) {
                error_log("Signature verification failed");
                return [
                    'success' => false,
                    'message' => 'Chữ ký không hợp lệ!'
                ];
            }
        } else {
            error_log("Skipping signature verification for test environment return URL");
        }

        $resultCode = $data['resultCode'] ?? $data['errorCode'] ?? 99;
        $transId = $data['transId'] ?? null;

        if (!$dbOrderId) {
            error_log("No dbOrderId in extraData");
            return [
                'success' => false,
                'message' => 'Thiếu thông tin đơn hàng!'
            ];
        }

        // Lấy thông tin đơn hàng từ DB bằng INT maDonHang
        $order = $this->model->getOrderByOrderId($dbOrderId);
        
        error_log("Order found: " . ($order ? "Yes" : "No"));
        if ($order) {
            error_log("Order status: " . $order['trangThai']);
        }

        if (!$order) {
            error_log("Order not found in database: $dbOrderId");
            return [
                'success' => false,
                'message' => 'Đơn hàng không tồn tại!'
            ];
        }

        // Kiểm tra đơn hàng đã xử lý chưa (tránh duplicate)
        if ($order['trangThai'] == 'completed') {
            return [
                'success' => true,
                'message' => 'Đơn hàng đã được xử lý trước đó!',
                'orderId' => $dbOrderId
            ];
        }

        // Kiểm tra trạng thái thanh toán
        if ($this->momoHelper->isPaymentSuccess($resultCode)) {
            // Thanh toán thành công
            $this->model->updateOrderStatus($dbOrderId, 'completed', $transId);

            // Lấy thông tin gói từ config
            require_once(__DIR__ . '/../config/momo.config.php');
            $packages = PREMIUM_PACKAGES;
            $packageId = $order['loaiGoi'];
            $duration = isset($packages[$packageId]) ? $packages[$packageId]['duration'] : 30;

            // Kích hoạt Premium
            $userId = $order['maNguoiDung'];
            $this->model->activatePremium($userId, $dbOrderId, $duration);

            return [
                'success' => true,
                'message' => 'Thanh toán thành công!',
                'orderId' => $dbOrderId
            ];
        } else {
            // Thanh toán thất bại
            $this->model->updateOrderStatus($dbOrderId, 'failed');

            return [
                'success' => false,
                'message' => 'Thanh toán thất bại!',
                'resultCode' => $resultCode
            ];
        }
    }

    /**
     * Kiểm tra trạng thái Premium
     */
    public function checkPremiumStatus($userId)
    {
        return $this->model->isPremiumUser($userId);
    }

    /**
     * Lấy thông tin Premium
     */
    public function getPremiumInfo($userId)
    {
        return $this->model->getPremiumInfo($userId);
    }

    /**
     * Lấy lịch sử thanh toán
     */
    public function getPaymentHistory($userId, $limit = 10)
    {
        return $this->model->getOrderHistory($userId, $limit);
    }
}
