<?php
include_once('mKetNoi.php');
class TimKiem
{
    public function TimKiemNguoiDung($tuKhoa, $khuVuc, $doTuoiMin, $doTuoiMax, $ngheNghiep, $currentUserId = null)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $conditions = [];

        // Loại trừ bản thân người dùng
        if (!empty($currentUserId)) {
            $currentUserId = intval($currentUserId);
            $conditions[] = "nd.maNguoiDung != $currentUserId";
        }

        // Tìm kiếm theo tên
        if (!empty($tuKhoa)) {
            $escapedTuKhoa = $conn->real_escape_string($tuKhoa);
            $conditions[] = "hs.hoTen LIKE '%$escapedTuKhoa%'";
        }

        // Lọc theo khu vực (thành phố)
        if (!empty($khuVuc) && $khuVuc !== 'Tất cả') {
            $escapedKhuVuc = (int)$conn->real_escape_string($khuVuc);
            $conditions[] = "hs.maThanhPho = $escapedKhuVuc";
        }

        // Lọc theo độ tuổi
        if (!empty($doTuoiMin)) {
            $escapedDoTuoiMin = (int)$doTuoiMin;
            $conditions[] = "YEAR(CURDATE()) - YEAR(hs.ngaysinh) >= $escapedDoTuoiMin";
        }
        if (!empty($doTuoiMax)) {
            $escapedDoTuoiMax = (int)$doTuoiMax;
            $conditions[] = "YEAR(CURDATE()) - YEAR(hs.ngaysinh) <= $escapedDoTuoiMax";
        }

        // Lọc theo nghề nghiệp
        if (!empty($ngheNghiep) && $ngheNghiep !== 'Tất cả') {
            $escapedNgheNghiep = (int)$conn->real_escape_string($ngheNghiep);
            $conditions[] = "hs.maNgheNghiep = $escapedNgheNghiep";
        }

        // Loại trừ những người đã ghép đôi với user hiện tại
        if (!empty($currentUserId)) {
            $conditions[] = "nd.maNguoiDung NOT IN (
                SELECT maNguoiDung1 FROM quanhenguoidung 
                WHERE maNguoiDung2 = $currentUserId AND trangthai = 'ghep'
                UNION
                SELECT maNguoiDung2 FROM quanhenguoidung 
                WHERE maNguoiDung1 = $currentUserId AND trangthai = 'ghep'
            )";
        }

        $whereClause = '';
        if (count($conditions) > 0) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $query = "
            SELECT 
                nd.maNguoiDung,
                nd.email,
                hs.maHoSo,
                hs.hoTen,
                hs.ngaysinh,
                hs.gioiTinh,
                hs.avatar,
                hs.moTa,
                hs.trangThaiHenHo,
                tp.tenThanhPho,
                nn.tenNgheNghiep,
                YEAR(CURDATE()) - YEAR(hs.ngaysinh) AS tuoi
            FROM nguoidung nd
            INNER JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung
            LEFT JOIN thanhpho tp ON hs.maThanhPho = tp.maThanhPho
            LEFT JOIN nghenghiep nn ON hs.maNgheNghiep = nn.maNgheNghiep
            $whereClause
            ORDER BY hs.maHoSo DESC
        ";

        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
}
