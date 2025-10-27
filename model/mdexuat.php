<?php
include_once('mKetNoi.php');
class Mdexuat
{
    public function GetAllUserByDeXuat()
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $current_uid = $_SESSION['uid'];

        $query = "
        SELECT u.*, 
               (
                   -- Điểm sở thích (30%) - số sở thích chung
                   COALESCE((
                       SELECT COUNT(*) * 5 
                       FROM nguoidung_sothich nst1 
                       JOIN nguoidung_sothich nst2 ON nst1.maSoThich = nst2.maSoThich
                       WHERE nst1.maNguoiDung = $current_uid 
                         AND nst2.maNguoiDung = u.maNguoiDung
                   ), 0) +
                   -- Điểm nghề nghiệp (25%) 
                   (CASE WHEN u.nghenghiep = cu.nghenghiep THEN 25 ELSE 0 END) +
                   -- Điểm vị trí (25%)
                   (CASE WHEN u.diachi = cu.diachi THEN 25 ELSE 0 END) +
                   -- Điểm độ tuổi (20%)
                   (CASE 
                       WHEN ABS(YEAR(CURDATE()) - YEAR(u.ngaysinh) - (YEAR(CURDATE()) - YEAR(cu.ngaysinh))) = 0 THEN 20
                       WHEN ABS(YEAR(CURDATE()) - YEAR(u.ngaysinh) - (YEAR(CURDATE()) - YEAR(cu.ngaysinh))) <= 2 THEN 15
                       WHEN ABS(YEAR(CURDATE()) - YEAR(u.ngaysinh) - (YEAR(CURDATE()) - YEAR(cu.ngaysinh))) <= 5 THEN 10
                       WHEN ABS(YEAR(CURDATE()) - YEAR(u.ngaysinh) - (YEAR(CURDATE()) - YEAR(cu.ngaysinh))) <= 10 THEN 5
                       ELSE 0 
                   END)
               ) AS compatibility_score
        FROM nguoidung u
        CROSS JOIN nguoidung cu 
        LEFT JOIN quanhenguoidung qh ON (
            (qh.maNguoiDung1 = $current_uid AND qh.maNguoiDung2 = u.maNguoiDung) OR
            (qh.maNguoiDung2 = $current_uid AND qh.maNguoiDung1 = u.maNguoiDung)
        )
        WHERE cu.maNguoiDung = $current_uid 
            AND u.maNguoiDung != $current_uid
            AND qh.maNguoiDung1 IS NULL
        ORDER BY compatibility_score DESC, RAND()
        LIMIT 5
    ";

        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
    public function GetAllKhuVuc()
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT * FROM thanhpho";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
    public function GetAllSoThich()
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT * FROM sothich";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
    public function GetAllNgheNghiep()
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT * FROM nghenghiep";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
    public function InsertUser($uid1, $uid2, $status)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "INSERT INTO quanhenguoidung (maNguoiDung1, maNguoiDung2, trangthai) VALUES ($uid1, $uid2, '$status')";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
    public function HasLiked($uid1, $uid2)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT * FROM quanhenguoidung WHERE maNguoiDung1 = $uid1 AND maNguoiDung2 = $uid2 AND trangthai = 'like'";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return ($kq && $kq->num_rows > 0);
    }
}
