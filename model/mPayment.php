<?php
include_once('mKetNoi.php');

/**
 * Model xử lý thanh toán và đơn hàng
 * Cập nhật theo cấu trúc DB thực tế
 */
class modelPayment
{
    /**
     * Tạo đơn hàng mới
     * @param int $userId - maNguoiDung
     * @param string $packageId - Loại gói (premium_1month, v.v.)
     * @param int $amount - Số tiền
     * @param string $orderId - Mã đơn hàng unique (sẽ không dùng, dùng auto_increment)
     * @param string $status - Trạng thái: pending, completed, failed, cancelled
     * @return bool|int - Trả về maDonHang (INT) nếu thành công, false nếu thất bại
     */
    public function createOrder($userId, $packageId, $amount, $orderId, $status = 'pending')
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            try {
                // maDonHang là AUTO_INCREMENT, không cần truyền vào
                $sql = "INSERT INTO donhang (maNguoiDung, loaiGoi, tongTien, phuongThucThanhToan, trangThai, ngayTao) 
                        VALUES (?, ?, ?, 'MoMo', ?, NOW())";
                
                $stmt = $con->prepare($sql);
                if (!$stmt) {
                    error_log("Prepare failed: " . $con->error);
                    $con->close();
                    return false;
                }
                
                // i = integer, s = string
                // maNguoiDung (i), loaiGoi (s), tongTien (i), trangThai (s)
                $stmt->bind_param("isis", $userId, $packageId, $amount, $status);
                $result = $stmt->execute();
                
                if (!$result) {
                    error_log("Execute failed: " . $stmt->error);
                    error_log("UserId: $userId, Package: $packageId, Amount: $amount, Status: $status");
                    $con->close();
                    return false;
                }
                
                // Lấy ID vừa insert (AUTO_INCREMENT)
                $insertedId = $con->insert_id;
                error_log("Order created successfully with ID: $insertedId");
                
                $con->close();
                return $insertedId; // Trả về INT
            } catch (Exception $e) {
                error_log("Create order exception: " . $e->getMessage());
                $con->close();
                return false;
            }
        }
        return false;
    }

    /**
     * Cập nhật trạng thái đơn hàng
     * @param int $orderId - maDonHang (INT)
     * @param string $status - Trạng thái mới
     * @param string $transId - Mã giao dịch từ MoMo (optional)
     * @param string $paymentTime - Thời gian thanh toán (optional)
     * @return bool
     */
    public function updateOrderStatus($orderId, $status, $transId = null, $paymentTime = null)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            if ($transId) {
                $sql = "UPDATE donhang 
                        SET trangThai = ?, maGiaoDich = ?, ngayCapNhat = NOW() 
                        WHERE maDonHang = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ssi", $status, $transId, $orderId); // i = integer
            } else {
                $sql = "UPDATE donhang 
                        SET trangThai = ?, ngayCapNhat = NOW() 
                        WHERE maDonHang = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("si", $status, $orderId); // i = integer
            }
            
            $result = $stmt->execute();
            $con->close();
            return $result;
        }
        return false;
    }

    /**
     * Lấy thông tin đơn hàng theo maDonHang
     * @param int $orderId - maDonHang (INT)
     * @return array|false
     */
    public function getOrderByOrderId($orderId)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT * FROM donhang WHERE maDonHang = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $orderId); // i = integer
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
            
            $con->close();
            return $order;
        }
        return false;
    }

    /**
     * Lấy lịch sử đơn hàng của người dùng
     * @param int $userId - maNguoiDung
     * @param int $limit - Số lượng bản ghi
     * @return mysqli_result|false
     */
    public function getOrderHistory($userId, $limit = 10)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        d.*,
                        p.ngayBatDau,
                        p.ngayKetThuc,
                        p.trangThai as trangThaiPremium
                    FROM donhang d
                    LEFT JOIN premium p ON d.maDonHang = p.maDonHang
                    WHERE d.maNguoiDung = ? 
                    ORDER BY d.ngayTao DESC 
                    LIMIT ?";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $con->close();
            return $result;
        }
        return false;
    }

    /**
     * Kích hoạt Premium cho người dùng
     * @param int $userId - maNguoiDung
     * @param int $orderId - maDonHang (INT)
     * @param int $duration - Số ngày sử dụng
     * @return bool
     */
    public function activatePremium($userId, $orderId, $duration)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            // Kiểm tra đã có premium hay chưa
            $checkSql = "SELECT * FROM premium WHERE maNguoiDung = ? AND trangThai = 1 AND ngayKetThuc > NOW()";
            $checkStmt = $con->prepare($checkSql);
            $checkStmt->bind_param("i", $userId);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();
            
            if ($existing) {
                // Đã có premium đang hoạt động -> Gia hạn thêm
                $startDate = $existing['ngayKetThuc']; // Bắt đầu từ ngày hết hạn cũ
                $expiryDate = date('Y-m-d H:i:s', strtotime($startDate . " +$duration days"));
                
                $sql = "UPDATE premium 
                        SET ngayKetThuc = ?, ngayCapNhat = NOW() 
                        WHERE maNguoiDung = ? AND trangThai = 1";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("si", $expiryDate, $userId);
            } else {
                // Chưa có premium -> Tạo mới
                $startDate = date('Y-m-d H:i:s');
                $expiryDate = date('Y-m-d H:i:s', strtotime("+$duration days"));
                
                $sql = "INSERT INTO premium (maDonHang, maNguoiDung, ngayBatDau, ngayKetThuc, trangThai, ngayTao) 
                        VALUES (?, ?, ?, ?, 1, NOW())";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("iiss", $orderId, $userId, $startDate, $expiryDate); // i = integer
            }
            
            $result = $stmt->execute();
            $con->close();
            return $result;
        }
        return false;
    }

    /**
     * Kiểm tra trạng thái Premium
     * @param int $userId - maNguoiDung
     * @return bool
     */
    public function isPremiumUser($userId)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT * FROM premium 
                    WHERE maNguoiDung = ? 
                    AND trangThai = 1 
                    AND ngayKetThuc > NOW()";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $isPremium = ($result->num_rows > 0);
            
            $con->close();
            return $isPremium;
        }
        return false;
    }

    /**
     * Lấy thông tin Premium
     * @param int $userId - maNguoiDung
     * @return array|false
     */
    public function getPremiumInfo($userId)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        p.*,
                        d.loaiGoi,
                        d.tongTien,
                        DATEDIFF(p.ngayKetThuc, NOW()) as soNgayConLai
                    FROM premium p
                    LEFT JOIN donhang d ON p.maDonHang = d.maDonHang
                    WHERE p.maNguoiDung = ? 
                    AND p.trangThai = 1 
                    AND p.ngayKetThuc > NOW()
                    ORDER BY p.ngayKetThuc DESC
                    LIMIT 1";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $premium = $result->fetch_assoc();
            
            $con->close();
            return $premium;
        }
        return false;
    }

    /**
     * Lưu log giao dịch
     * @param string $orderId - maDonHang
     * @param string $logType - Loại log: request, ipn, return, error
     * @param array $data - Dữ liệu giao dịch
     * @return bool
     */
    public function logTransaction($orderId, $logType, $data)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            try {
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

                $sql = "INSERT INTO log_thanhtoan (maDonHang, loaiLog, duLieu, diaChiIP, userAgent) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $con->prepare($sql);
                $stmt->bind_param("sssss", $orderId, $logType, $jsonData, $ipAddress, $userAgent);
                $result = $stmt->execute();
                
                $con->close();
                return $result;
            } catch (Exception $e) {
                // Bỏ qua lỗi nếu bảng không tồn tại (optional table)
                error_log("Log transaction warning: " . $e->getMessage());
                $con->close();
                return true; // Không fail toàn bộ transaction
            }
        }
        return true; // Không fail nếu không log được
    }

    /**
     * Vô hiệu hóa Premium đã hết hạn (Cron job)
     * @return int Số lượng bản ghi đã cập nhật
     */
    public function deactivateExpiredPremium()
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "UPDATE premium 
                    SET trangThai = 0, ngayCapNhat = NOW() 
                    WHERE ngayKetThuc < NOW() AND trangThai = 1";
            
            $con->query($sql);
            $affected = $con->affected_rows;
            
            $con->close();
            return $affected;
        }
        return 0;
    }
}
