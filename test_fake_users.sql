-- ==========================================
-- FILE SQL TẠO 10 NGƯỜI DÙNG NỮ ẢO ĐỂ TEST
-- ==========================================
-- Chạy file này trong phpMyAdmin để tạo dữ liệu test
-- Database: db_dating_final_v1

USE db_dating_final_v1;

-- Xóa dữ liệu test cũ nếu có (optional)
-- DELETE FROM nguoidung WHERE email LIKE 'test.nu%@example.com';

-- ==========================================
-- 1. THÊM 10 TÀI KHOẢN NGƯỜI DÙNG NỮ
-- ==========================================
INSERT INTO nguoidung (email, matKhau, vaiTro, trangThai) VALUES
('test.nu1@example.com', MD5('123456'), 'user', 'active'),
('test.nu2@example.com', MD5('123456'), 'user', 'active'),
('test.nu3@example.com', MD5('123456'), 'user', 'active'),
('test.nu4@example.com', MD5('123456'), 'user', 'active'),
('test.nu5@example.com', MD5('123456'), 'user', 'active'),
('test.nu6@example.com', MD5('123456'), 'user', 'active'),
('test.nu7@example.com', MD5('123456'), 'user', 'active'),
('test.nu8@example.com', MD5('123456'), 'user', 'active'),
('test.nu9@example.com', MD5('123456'), 'user', 'active'),
('test.nu10@example.com', MD5('123456'), 'user', 'active');

-- ==========================================
-- 2. LẤY ID CỦA CÁC USER VỪA TẠO
-- ==========================================
SET @uid1 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu1@example.com');
SET @uid2 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu2@example.com');
SET @uid3 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu3@example.com');
SET @uid4 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu4@example.com');
SET @uid5 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu5@example.com');
SET @uid6 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu6@example.com');
SET @uid7 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu7@example.com');
SET @uid8 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu8@example.com');
SET @uid9 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu9@example.com');
SET @uid10 = (SELECT maNguoiDung FROM nguoidung WHERE email = 'test.nu10@example.com');

-- ==========================================
-- 3. TẠO HỒ SƠ CHO 10 NGƯỜI DÙNG NỮ
-- ==========================================
-- Lấy một số mã thành phố (giả sử có trong DB)
SET @tpHCM = 1;      -- TP.HCM
SET @haNoi = 2;      -- Hà Nội
SET @daNang = 3;     -- Đà Nẵng
SET @canTho = 4;     -- Cần Thơ

-- Lấy một số mã nghề nghiệp (giả sử có trong DB)
SET @ngheIT = 1;          -- IT
SET @ngheYte = 2;         -- Y tế
SET @ngheGiaoDuc = 3;     -- Giáo dục
SET @ngheKinhDoanh = 4;   -- Kinh doanh
SET @ngheThuKy = 5;       -- Thư ký

INSERT INTO hosonguoidung (maNguoiDung, hoTen, gioiTinh, ngaySinh, maThanhPho, maNgheNghiep, moTa, avatar) VALUES
(@uid1, 'Nguyễn Thị Lan Anh', 1, '1998-03-15', @tpHCM, @ngheIT, 'Mình là một cô gái yêu thích công nghệ và du lịch. Thích đọc sách và uống cà phê vào cuối tuần.', 'default.png'),
(@uid2, 'Trần Thị Minh Châu', 1, '1997-07-22', @tpHCM, @ngheYte, 'Là một bác sĩ trẻ, mình yêu thích chăm sóc sức khỏe cộng đồng. Thích nấu ăn và yoga.', 'default.png'),
(@uid3, 'Lê Thị Hương Giang', 1, '1999-11-08', @haNoi, @ngheGiaoDuc, 'Giáo viên tiểu học nhiệt huyết. Yêu trẻ con và thích làm đồ handmade.', 'default.png'),
(@uid4, 'Phạm Thị Thu Hà', 1, '1996-05-30', @haNoi, @ngheKinhDoanh, 'Sales Manager năng động. Thích shopping, du lịch và gặp gỡ bạn bè.', 'default.png'),
(@uid5, 'Hoàng Thị Bích Ngọc', 1, '2000-01-12', @daNang, @ngheIT, 'Developer trẻ tuổi. Đam mê code và chơi game. Cũng thích xem phim anime.', 'default.png'),
(@uid6, 'Võ Thị Kim Liên', 1, '1995-09-18', @daNang, @ngheThuKy, 'Thư ký văn phòng chuyên nghiệp. Yêu thích âm nhạc và vẽ tranh.', 'default.png'),
(@uid7, 'Đặng Thị Phương Thảo', 1, '1998-12-25', @canTho, @ngheYte, 'Điều dưỡng viên tận tâm. Thích chạy bộ buổi sáng và làm vườn.', 'default.png'),
(@uid8, 'Bùi Thị Mai Anh', 1, '1997-04-07', @tpHCM, @ngheGiaoDuc, 'Giảng viên đại học. Đam mê nghiên cứu và viết lách. Thích uống trà và đọc sách.', 'default.png'),
(@uid9, 'Ngô Thị Quỳnh Như', 1, '1999-08-14', @haNoi, @ngheKinhDoanh, 'Nhân viên marketing sáng tạo. Yêu thích nhiếp ảnh và khám phá quán cafe mới.', 'default.png'),
(@uid10, 'Đinh Thị Thanh Trúc', 1, '1996-02-28', @daNang, @ngheIT, 'UI/UX Designer. Đam mê nghệ thuật và thiết kế. Thích đi xem triển lãm.', 'default.png');

-- ==========================================
-- 4. THÊM SỞ THÍCH CHO TỪNG NGƯỜI (Đa dạng)
-- ==========================================
-- Giả sử các mã sở thích:
-- 1=Du lịch, 2=Ẩm thực, 3=Thể thao, 4=Âm nhạc, 5=Điện ảnh
-- 6=Đọc sách, 7=Nấu ăn, 8=Nhiếp ảnh, 9=Game, 10=Yoga
-- 11=Vẽ tranh, 12=Shopping, 13=Cafe, 14=Chạy bộ, 15=Làm vườn

-- Lấy maHoSo của từng người
SET @hs1 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid1);
SET @hs2 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid2);
SET @hs3 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid3);
SET @hs4 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid4);
SET @hs5 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid5);
SET @hs6 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid6);
SET @hs7 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid7);
SET @hs8 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid8);
SET @hs9 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid9);
SET @hs10 = (SELECT maHoSo FROM hosonguoidung WHERE maNguoiDung = @uid10);

-- User 1: Lan Anh - Du lịch, Công nghệ, Cafe, Đọc sách
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs1, 1), (@hs1, 9), (@hs1, 13), (@hs1, 6);

-- User 2: Minh Châu - Nấu ăn, Yoga, Ẩm thực, Đọc sách
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs2, 7), (@hs2, 10), (@hs2, 2), (@hs2, 6);

-- User 3: Hương Giang - Vẽ tranh, Âm nhạc, Nấu ăn, Đọc sách
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs3, 11), (@hs3, 4), (@hs3, 7), (@hs3, 6);

-- User 4: Thu Hà - Shopping, Du lịch, Ẩm thực, Cafe
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs4, 12), (@hs4, 1), (@hs4, 2), (@hs4, 13);

-- User 5: Bích Ngọc - Game, Công nghệ, Điện ảnh, Anime
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs5, 9), (@hs5, 9), (@hs5, 5), (@hs5, 5);

-- User 6: Kim Liên - Âm nhạc, Vẽ tranh, Cafe, Đọc sách
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs6, 4), (@hs6, 11), (@hs6, 13), (@hs6, 6);

-- User 7: Phương Thảo - Chạy bộ, Làm vườn, Yoga, Ẩm thực
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs7, 14), (@hs7, 15), (@hs7, 10), (@hs7, 2);

-- User 8: Mai Anh - Đọc sách, Cafe, Âm nhạc, Du lịch
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs8, 6), (@hs8, 13), (@hs8, 4), (@hs8, 1);

-- User 9: Quỳnh Như - Nhiếp ảnh, Cafe, Du lịch, Shopping
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs9, 8), (@hs9, 13), (@hs9, 1), (@hs9, 12);

-- User 10: Thanh Trúc - Vẽ tranh, Nhiếp ảnh, Âm nhạc, Cafe
INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
(@hs10, 11), (@hs10, 8), (@hs10, 4), (@hs10, 13);

-- ==========================================
-- 5. KIỂM TRA DỮ LIỆU VỪA TẠO
-- ==========================================
SELECT 
    nd.maNguoiDung,
    nd.email,
    hs.hoTen,
    hs.gioiTinh,
    YEAR(CURDATE()) - YEAR(hs.ngaySinh) as tuoi,
    tp.tenThanhPho,
    nn.tenNgheNghiep
FROM nguoidung nd
INNER JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung
LEFT JOIN thanhpho tp ON hs.maThanhPho = tp.maThanhPho
LEFT JOIN nghenghiep nn ON hs.maNgheNghiep = nn.maNgheNghiep
WHERE nd.email LIKE 'test.nu%@example.com'
ORDER BY nd.maNguoiDung;

-- ==========================================
-- HƯỚNG DẪN SỬ DỤNG
-- ==========================================
-- 1. Mở phpMyAdmin
-- 2. Chọn database: db_dating_final_v1
-- 3. Click tab "SQL"
-- 4. Copy toàn bộ nội dung file này và paste vào
-- 5. Click "Go" để chạy
-- 
-- Thông tin đăng nhập test:
-- Email: test.nu1@example.com đến test.nu10@example.com
-- Password: 123456 (cho tất cả)
--
-- Để xóa dữ liệu test:
-- DELETE hs FROM hoso_sothich hs
-- INNER JOIN hosonguoidung h ON hs.maHoSo = h.maHoSo
-- INNER JOIN nguoidung nd ON h.maNguoiDung = nd.maNguoiDung
-- WHERE nd.email LIKE 'test.nu%@example.com';
-- 
-- DELETE FROM hosonguoidung WHERE maNguoiDung IN 
-- (SELECT maNguoiDung FROM nguoidung WHERE email LIKE 'test.nu%@example.com');
-- 
-- DELETE FROM nguoidung WHERE email LIKE 'test.nu%@example.com';
