<?php
include_once('mKetNoi.php');
class mMe
{
    public function GetUserById($uid)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT 
            h.*, 
            -- gộp ID sở thích, tránh trùng
            GROUP_CONCAT(DISTINCT st.maSoThich ORDER BY st.tenSoThich SEPARATOR ',')        AS soThichIds,
            -- gộp tên sở thích để hiển thị
            GROUP_CONCAT(DISTINCT st.tenSoThich ORDER BY st.tenSoThich SEPARATOR ', ')     AS soThichText
        FROM hosonguoidung h
        LEFT JOIN hoso_sothich hs ON hs.maHoSo = h.maHoSo
        LEFT JOIN sothich       st ON st.maSoThich = hs.maSoThich
        WHERE h.maNguoiDung = {$uid}
        GROUP BY h.maHoSo";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
    public function GetUserByIdRegion($maThanhPho)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT * FROM thanhpho WHERE maThanhPho = '$maThanhPho'";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
    public function GetUserByIdJob($maNgheNghiep)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT * FROM nghenghiep WHERE maNgheNghiep = '$maNgheNghiep'";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
}
