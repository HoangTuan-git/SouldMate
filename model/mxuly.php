<?php
include_once('mKetNoi.php');
class MxuLy
{
    public function GetAllUserLike()
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "
            SELECT 
                h.maHoSo,
                h.hoTen,
                YEAR(CURDATE()) - YEAR(h.ngaysinh) AS tuoi,
                h.maThanhPho,
                h.maNgheNghiep,
                h.avatar,
                tp.tenThanhPho,
                nn.tenNghe,
                h.maNguoiDung,
                GROUP_CONCAT(st.tenSoThich ORDER BY st.tenSoThich SEPARATOR ', ') AS sothich
            FROM hosonguoidung h
            JOIN thanhpho tp ON h.maThanhPho = tp.maThanhPho
            JOIN nghenghiep nn ON h.maNgheNghiep = nn.maNghe
            LEFT JOIN hoso_sothich hst ON h.maHoSo = hst.maHoSo
            LEFT JOIN sothich st ON hst.maSoThich = st.maSoThich
            WHERE h.maNguoiDung IN (
                SELECT maNguoiDung1 
                FROM quanhenguoidung 
                WHERE maNguoiDung2 = {$_SESSION['uid']} 
                AND trangThai = 'thich'
            )
            GROUP BY h.maHoSo, h.hoTen, h.ngaysinh, h.maThanhPho, h.maNgheNghiep, h.avatar, tp.tenThanhPho, nn.tenNghe, h.maNguoiDung
        ";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
}
