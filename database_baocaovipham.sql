-- SQL Script để tạo/cập nhật bảng baocaovipham
-- Cấu trúc mới hỗ trợ báo cáo: người dùng, bài viết, tin nhắn

-- Xóa bảng cũ nếu tồn tại (CHÚ Ý: Sẽ mất dữ liệu cũ)
-- DROP TABLE IF EXISTS baocaovipham;

-- Tạo bảng mới với cấu trúc đầy đủ (khớp với DB hiện tại)
CREATE TABLE IF NOT EXISTS baocaovipham (
    maBaoCao INT(11) AUTO_INCREMENT PRIMARY KEY,
    maNguoiBaoCao INT(10) UNSIGNED NOT NULL,
    loaiBaoCao ENUM('baidang', 'nguoidung', 'tinnhan') NOT NULL,
    
    -- Thông tin đối tượng bị báo cáo
    maBaiDang INT(10) UNSIGNED NULL,       -- NULL nếu không báo cáo bài viết
    maTinNhan INT(10) UNSIGNED NULL,       -- NULL nếu không báo cáo tin nhắn
    maNguoiDungBiBaoCao INT(10) UNSIGNED NOT NULL, -- Người dùng bị báo cáo
    
    -- Thông tin báo cáo
    lyDo TEXT NOT NULL COLLATE utf8mb4_general_ci, -- Lý do báo cáo
    thoiGianBaoCao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Thời gian báo cáo
    trangThai ENUM('dangxuly', 'daxuly', 'tuchoi') DEFAULT 'dangxuly',
    
    -- Snapshot nội dung vi phạm
    noiDungViPham TEXT NULL COLLATE utf8mb4_general_ci, -- Nội dung bài viết/tin nhắn bị báo cáo
    
    -- Context cho tin nhắn (10-20 tin nhắn xung quanh)
    contextTinNhan LONGTEXT NULL COLLATE utf8mb4_bin, -- JSON array chứa context tin nhắn
    thoiGianTinNhan DATETIME NULL,         -- Thời điểm tin nhắn được gửi
    
    -- Indexes để tăng tốc truy vấn
    INDEX idx_nguoi_bao_cao (maNguoiBaoCao),
    INDEX idx_nguoi_bi_bao_cao (maNguoiDungBiBaoCao),
    INDEX idx_loai_bao_cao (loaiBaoCao),
    INDEX idx_trang_thai (trangThai),
    INDEX idx_thoi_gian (thoiGianBaoCao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Nếu bảng đã tồn tại và cần cập nhật cấu trúc, sử dụng ALTER TABLE
-- CHÚ Ý: Kiểm tra kỹ trước khi chạy để không mất dữ liệu

/*
-- Thêm cột mới nếu chưa có
ALTER TABLE baocaovipham ADD COLUMN IF NOT EXISTS loaiBaoCao ENUM('nguoi dung', 'baiviet', 'tinnhan') NOT NULL AFTER maNguoiBaoCao;
ALTER TABLE baocaovipham ADD COLUMN IF NOT EXISTS maBaiDang INT NULL AFTER loaiBaoCao;
ALTER TABLE baocaovipham ADD COLUMN IF NOT EXISTS maTinNhan INT NULL AFTER maBaiDang;
ALTER TABLE baocaovipham ADD COLUMN IF NOT EXISTS noiDungViPham TEXT NULL AFTER trangThai;
ALTER TABLE baocaovipham ADD COLUMN IF NOT EXISTS contextTinNhan LONGTEXT NULL AFTER noiDungViPham;
ALTER TABLE baocaovipham ADD COLUMN IF NOT EXISTS thoiGianTinNhan DATETIME NULL AFTER contextTinNhan;

-- Đổi tên cột nếu cần
ALTER TABLE baocaovipham CHANGE maNguoiDung1 maNguoiBaoCao INT NOT NULL;
ALTER TABLE baocaovipham CHANGE maNguoiDung2 maNguoiDungBiBaoCao INT NOT NULL;
ALTER TABLE baocaovipham CHANGE ngayTao thoiGianBaoCao DATETIME NOT NULL;

-- Thêm cột trạng thái nếu chưa có
ALTER TABLE baocaovipham ADD COLUMN IF NOT EXISTS trangThai ENUM('cho_xu_ly', 'dang_xu_ly', 'da_xu_ly', 'tu_choi') DEFAULT 'cho_xu_ly' AFTER thoiGianBaoCao;

-- Xóa cột không cần thiết
-- ALTER TABLE baocaovipham DROP COLUMN IF EXISTS urlNoiDung;

-- Thêm foreign keys
ALTER TABLE baocaovipham ADD CONSTRAINT fk_bao_cao_nguoi_bao_cao 
    FOREIGN KEY (maNguoiBaoCao) REFERENCES nguoidung(maNguoiDung) ON DELETE CASCADE;
    
ALTER TABLE baocaovipham ADD CONSTRAINT fk_bao_cao_nguoi_bi_bao_cao 
    FOREIGN KEY (maNguoiDungBiBaoCao) REFERENCES nguoidung(maNguoiDung) ON DELETE CASCADE;
    
ALTER TABLE baocaovipham ADD CONSTRAINT fk_bao_cao_bai_dang 
    FOREIGN KEY (maBaiDang) REFERENCES baidang(maBaiDang) ON DELETE CASCADE;
    
ALTER TABLE baocaovipham ADD CONSTRAINT fk_bao_cao_tin_nhan 
    FOREIGN KEY (maTinNhan) REFERENCES tinnhan(maTinNhan) ON DELETE CASCADE;

-- Thêm indexes
CREATE INDEX IF NOT EXISTS idx_nguoi_bao_cao ON baocaovipham(maNguoiBaoCao);
CREATE INDEX IF NOT EXISTS idx_nguoi_bi_bao_cao ON baocaovipham(maNguoiDungBiBaoCao);
CREATE INDEX IF NOT EXISTS idx_loai_bao_cao ON baocaovipham(loaiBaoCao);
CREATE INDEX IF NOT EXISTS idx_trang_thai ON baocaovipham(trangThai);
CREATE INDEX IF NOT EXISTS idx_thoi_gian ON baocaovipham(thoiGianBaoCao);
*/
