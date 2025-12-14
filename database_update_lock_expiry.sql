-- Thêm cột ngayMoKhoa vào bảng nguoidung để lưu thời hạn mở khóa
-- Chạy script này trong phpMyAdmin hoặc MySQL Workbench

ALTER TABLE `nguoidung` 
ADD COLUMN `ngayMoKhoa` DATETIME NULL DEFAULT NULL COMMENT 'Ngày tự động mở khóa (NULL = vĩnh viễn)' 
AFTER `trangThaiViPham`;

-- Index để tăng tốc query tự động mở khóa
CREATE INDEX idx_ngayMoKhoa ON nguoidung(trangThaiViPham, ngayMoKhoa);

-- Kiểm tra kết quả
SELECT 
    maNguoiDung, 
    trangThaiViPham, 
    ngayMoKhoa 
FROM nguoidung 
WHERE trangThaiViPham = 'khoa'
LIMIT 5;
