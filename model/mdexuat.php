<?php
include_once('mKetNoi.php');
class Mdexuat
{
    /**
     * Lấy danh sách người dùng đề xuất dựa trên thuật toán matching
     * Tham số lọc: $filters = ['thanhpho' => id, 'tuoi_min' => int, 'tuoi_max' => int, 'nghenghiep' => id]
     */
    public function GetAllUserByDeXuat()
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $current_uid = $_SESSION['uid'];
        // 1. Đếm số lượt tương tác hôm nay (ghep + boqua) của user này
        $sqlCount = "
            SELECT COUNT(*) AS cnt
            FROM quanhenguoidung
            WHERE maNguoiDung1 = $current_uid
            AND DATE(ngayTao) = CURDATE()
            AND trangThai IN ('ghep', 'boqua')
        ";
        $rsCount = $conn->query($sqlCount);
        $rowCount = $rsCount ? $rsCount->fetch_assoc() : ['cnt' => 0];
        $countToday = (int)$rowCount['cnt'];

        // Nếu đã đủ 5 lần thì không đề xuất nữa
        if ($countToday >= 5) {
            $p->NgatKetNoi($conn);
            return false; // hoặc trả về một result rỗng tuỳ bạn xử lý
        }

        // Số người còn lại được đề xuất hôm nay
        $limit = 5 - $countToday;

        $query = "
        SELECT 
            nd.maNguoiDung,
            nd.email,
            hs.hoTen as name,
            hs.avatar,
            hs.moTa,
            hs.gioiTinh,
            YEAR(CURDATE()) - YEAR(hs.ngaySinh) as age,
            tp.tenThanhPho,
            tp.maThanhPho as region_id,
            nn.tenNgheNghiep as nghenghiep,
            nn.maNgheNghiep as job_id,
            nganh.tenNganh as nganh,
            
            -- TÍNH ĐIỂM TƯƠNG THÍCH
            (
                -- 1. ĐIỂM SỞ THÍCH (35%) - Tính % sở thích chung
                COALESCE(
                    (
                        SELECT 
                            -- Số sở thích chung / Tổng số sở thích unique của cả 2 người * 35
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
                
                -- 2. ĐIỂM NGHỀ NGHIỆP (25%)
                (CASE 
                    WHEN hs.maNgheNghiep = cur_hs.maNgheNghiep THEN 25
                    ELSE 0 
                END) +
                
                -- 3. ĐIỂM VỊ TRÍ (25%)
                (CASE 
                    WHEN hs.maThanhPho = cur_hs.maThanhPho THEN 25
                    ELSE 0 
                END) +
                
                -- 4. ĐIỂM ĐỘ TUỔI (15%) - Khoảng cách tuổi hợp lý
                (CASE 
                    WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) = 0 THEN 15
                    WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 2 THEN 12
                    WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 5 THEN 8
                    WHEN ABS(YEAR(CURDATE()) - YEAR(hs.ngaySinh) - (YEAR(CURDATE()) - YEAR(cur_hs.ngaySinh))) <= 10 THEN 4
                    ELSE 0 
                END)
                
            ) AS compatibility_score,
            -- Số sở thích chung để hiển thị
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
        LEFT JOIN nganhnghe nganh ON nn.maNganh = nganh.maNganh
        
        -- Join để lấy thông tin user hiện tại
        CROSS JOIN hosonguoidung cur_hs
        
        -- Loại trừ những người đã có quan hệ (liked, matched, blocked)
        LEFT JOIN quanhenguoidung qh ON (
            (qh.maNguoiDung1 = $current_uid AND qh.maNguoiDung2 = nd.maNguoiDung) OR
            (qh.maNguoiDung2 = $current_uid AND qh.maNguoiDung1 = nd.maNguoiDung)
        )
        
        WHERE cur_hs.maNguoiDung = $current_uid 
            AND nd.maNguoiDung != $current_uid
            AND qh.maNguoiDung1 IS NULL  -- Chưa có quan hệ
            AND hs.gioiTinh != cur_hs.gioiTinh  -- Chỉ ghép nam-nữ hoặc nữ-nam
            AND cur_hs.trangThaiHenHo = 'nghiêm túc'  -- User hiện tại phải là nghiêm túc
            AND hs.trangThaiHenHo = 'nghiêm túc'  -- Người được đề xuất cũng phải là nghiêm túc
        ORDER BY compatibility_score DESC, RAND()
        LIMIT $limit
        ";

        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
    public function InsertUser($uid1, $uid2, $status, $first_like = null)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "INSERT INTO quanhenguoidung (maNguoiDung1, maNguoiDung2, trangThai) VALUES ($uid1, $uid2, '$status')";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
}
