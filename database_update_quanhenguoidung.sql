-- Thêm cột trangThaiTruocChan để lưu trạng thái trước khi chặn
-- Chạy lệnh này để cập nhật database

ALTER TABLE quanhenguoidung 
ADD COLUMN trangThaiTruocChan VARCHAR(50) NULL AFTER trangThai;

-- Giải thích:
-- Khi chặn người dùng: hệ thống sẽ lưu trạng thái hiện tại vào trangThaiTruocChan
-- Khi bỏ chặn: hệ thống sẽ khôi phục trạng thái từ trangThaiTruocChan
-- Nếu trangThaiTruocChan = NULL (tức là lúc chặn chưa có quan hệ) thì sẽ xóa record khi bỏ chặn
