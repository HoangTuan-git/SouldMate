-- ============================================
-- TEST QUERY - Kiểm tra chức năng đề xuất
-- Database: db_dating_final_v1
-- ============================================

-- Bước 1: Kiểm tra dữ liệu user và hồ sơ
SELECT 
    nd.maNguoiDung,
    nd.email,
    hs.hoTen,
    YEAR(CURDATE()) - YEAR(hs.ngaySinh) as tuoi,
    hs.gioiTinh,
    tp.tenThanhPho,
    nn.tenNgheNghiep
FROM nguoidung nd
INNER JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung
LEFT JOIN thanhpho tp ON hs.maThanhPho = tp.maThanhPho
LEFT JOIN nghenghiep nn ON hs.maNgheNghiep = nn.maNgheNghiep
ORDER BY nd.maNguoiDung;

-- Bước 2: Kiểm tra sở thích của từng user
SELECT 
    nd.maNguoiDung,
    hs.hoTen,
    GROUP_CONCAT(st.tenSoThich SEPARATOR ', ') as sothich
FROM nguoidung nd
INNER JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung
LEFT JOIN hoso_sothich hst ON hs.maHoSo = hst.maHoSo
LEFT JOIN sothich st ON hst.maSoThich = st.maSoThich
GROUP BY nd.maNguoiDung, hs.hoTen
ORDER BY nd.maNguoiDung;

-- Bước 3: Test thuật toán đề xuất cho user ID = 3
-- (Thay 3 bằng ID user của bạn)
SET @current_uid = 3;

SELECT 
    nd.maNguoiDung,
    nd.email,
    hs.hoTen as name,
    hs.avatar,
    hs.gioiTinh,
    YEAR(CURDATE()) - YEAR(hs.ngaySinh) as age,
    tp.tenThanhPho,
    nn.tenNgheNghiep as nghenghiep,
    
    -- TÍNH ĐIỂM TƯƠNG THÍCH
    (
        -- 1. ĐIỂM SỞ THÍCH (35%)
        COALESCE(
            (
                SELECT 
                    (COUNT(DISTINCT hst2.maSoThich) * 35.0) / 
                    GREATEST(1, (
                        SELECT COUNT(DISTINCT maSoThich) 
                        FROM hoso_sothich 
                        WHERE maHoSo IN (cur_hs.maHoSo, hs.maHoSo)
                    ))
                FROM hoso_sothich hst1 
                INNER JOIN hoso_sothich hst2 
                    ON hst1.maSoThich = hst2.maSoThich
                WHERE hst1.maHoSo = cur_hs.maHoSo 
                  AND hst2.maHoSo = hs.maHoSo
            ), 0
        ) AS diem_sothich,
        
        -- 2. ĐIỂM NGHỀ NGHIỆP (25%)
        (CASE 
            WHEN hs.maNgheNghiep IS NOT NULL 
                 AND cur_hs.maNgheNghiep IS NOT NULL
                 AND hs.maNgheNghiep = cur_hs.maNgheNghiep THEN 25
            ELSE 0 
        END) AS diem_nghe,
        
        -- 3. ĐIỂM VỊ TRÍ (25%)
        (CASE 
            WHEN hs.maThanhPho IS NOT NULL 
                 AND cur_hs.maThanhPho IS NOT NULL
                 AND hs.maThanhPho = cur_hs.maThanhPho THEN 25
            ELSE 0 
        END) AS diem_vitri,
        
        -- 4. ĐIỂM ĐỘ TUỔI (15%)
        (CASE 
            WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) = 0 THEN 15
            WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 2 THEN 12
            WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 5 THEN 8
            WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 10 THEN 4
            ELSE 0 
        END) AS diem_tuoi,
        
        -- TỔNG ĐIỂM
        (
            COALESCE(
                (
                    SELECT 
                        (COUNT(DISTINCT hst2.maSoThich) * 35.0) / 
                        GREATEST(1, (
                            SELECT COUNT(DISTINCT maSoThich) 
                            FROM hoso_sothich 
                            WHERE maHoSo IN (cur_hs.maHoSo, hs.maHoSo)
                        ))
                    FROM hoso_sothich hst1 
                    INNER JOIN hoso_sothich hst2 
                        ON hst1.maSoThich = hst2.maSoThich
                    WHERE hst1.maHoSo = cur_hs.maHoSo 
                      AND hst2.maHoSo = hs.maHoSo
                ), 0
            ) +
            (CASE 
                WHEN hs.maNgheNghiep IS NOT NULL 
                     AND cur_hs.maNgheNghiep IS NOT NULL
                     AND hs.maNgheNghiep = cur_hs.maNgheNghiep THEN 25
                ELSE 0 
            END) +
            (CASE 
                WHEN hs.maThanhPho IS NOT NULL 
                     AND cur_hs.maThanhPho IS NOT NULL
                     AND hs.maThanhPho = cur_hs.maThanhPho THEN 25
                ELSE 0 
            END) +
            (CASE 
                WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) = 0 THEN 15
                WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 2 THEN 12
                WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 5 THEN 8
                WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 10 THEN 4
                ELSE 0 
            END)
        ) AS compatibility_score,
        
        -- Số sở thích chung
        COALESCE(
            (
                SELECT COUNT(DISTINCT hst2.maSoThich)
                FROM hoso_sothich hst1 
                INNER JOIN hoso_sothich hst2 
                    ON hst1.maSoThich = hst2.maSoThich
                WHERE hst1.maHoSo = cur_hs.maHoSo 
                  AND hst2.maHoSo = hs.maHoSo
            ), 0
        ) as common_hobbies_count
        
FROM nguoidung nd
INNER JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung
LEFT JOIN thanhpho tp ON hs.maThanhPho = tp.maThanhPho
LEFT JOIN nghenghiep nn ON hs.maNgheNghiep = nn.maNgheNghiep

-- Join để lấy thông tin user hiện tại
CROSS JOIN hosonguoidung cur_hs

-- Loại trừ những người đã có quan hệ
LEFT JOIN quanhenguoidung qh ON (
    (qh.maNguoiDung1 = @current_uid AND qh.maNguoiDung2 = nd.maNguoiDung) OR
    (qh.maNguoiDung2 = @current_uid AND qh.maNguoiDung1 = nd.maNguoiDung)
)

WHERE cur_hs.maNguoiDung = @current_uid 
    AND nd.maNguoiDung != @current_uid
    AND qh.maNguoiDung1 IS NULL
    
ORDER BY compatibility_score DESC, RAND()
LIMIT 20;

-- ============================================
-- Bước 4: Test với bộ lọc
-- ============================================

-- Test lọc theo thành phố (TPHCM = ID ???)
-- Kiểm tra ID thành phố trước
SELECT maThanhPho, tenThanhPho FROM thanhpho WHERE tenThanhPho LIKE '%HCM%' OR tenThanhPho LIKE '%Hồ Chí Minh%';

-- Test lọc theo nghề nghiệp
SELECT maNgheNghiep, tenNgheNghiep FROM nghenghiep WHERE tenNgheNghiep LIKE '%Developer%' OR tenNgheNghiep LIKE '%Lập trình%';

-- ============================================
-- Bước 5: Thêm dữ liệu test (nếu cần)
-- ============================================

-- Thêm sở thích cho user (ví dụ: user ID=4, hồ sơ ID=1)
-- Kiểm tra maHoSo trước
SELECT nd.maNguoiDung, hs.maHoSo, hs.hoTen 
FROM nguoidung nd
INNER JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung;

-- Thêm sở thích
-- INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES
-- (1, 11),  -- Bơi lội
-- (1, 16),  -- Bóng rổ
-- (1, 46);  -- Bán hàng

-- Kiểm tra danh sách sở thích có sẵn
SELECT maSoThich, tenSoThich FROM sothich ORDER BY tenSoThich;

-- ============================================
-- Kết quả mong đợi:
-- ============================================
-- Sẽ hiển thị danh sách người dùng với:
-- - Thông tin cơ bản (tên, tuổi, địa chỉ, nghề)
-- - Điểm từng tiêu chí (sở thích, nghề, vị trí, tuổi)
-- - Tổng điểm tương thích (0-100)
-- - Số sở thích chung
-- Sắp xếp theo điểm cao xuống thấp
