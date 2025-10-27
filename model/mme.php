<?php
include_once('mKetNoi.php');
class mMe
{
    public function GetUserById($uid)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT * FROM hosonguoidung WHERE maNguoiDung = $uid";
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
}
