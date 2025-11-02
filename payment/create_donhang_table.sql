-- Kiểm tra và tạo bảng donhang nếu chưa có
CREATE TABLE IF NOT EXISTS donhang (
    maDonHang VARCHAR(100) PRIMARY KEY,
    maNguoiDung INT NOT NULL,
    ngayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    phuongThucThanhToan VARCHAR(50) DEFAULT 'MoMo',
    maGiaoDich VARCHAR(100) DEFAULT NULL,
    loaiGoi VARCHAR(50) DEFAULT NULL,
    trangThai ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    tongTien INT NOT NULL,
    ngayCapNhat DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nguoi_dung (maNguoiDung),
    INDEX idx_ma_giao_dich (maGiaoDich),
    INDEX idx_trang_thai (trangThai),
    FOREIGN KEY (maNguoiDung) REFERENCES nguoidung(maNguoiDung) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kiểm tra cấu trúc
DESCRIBE donhang;

-- Test insert
-- INSERT INTO donhang (maDonHang, maNguoiDung, loaiGoi, tongTien, phuongThucThanhToan, trangThai) 
-- VALUES ('TEST_001', 1, 'premium_1month', 50000, 'MoMo', 'pending');

-- Xem dữ liệu
SELECT * FROM donhang ORDER BY ngayTao DESC LIMIT 5;
