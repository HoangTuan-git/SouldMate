<?php
include_once('mKetNoi.php');
class mBanTin
{
    public function mGetAllTinTuc()
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT 
                    bd.*,
                    hs.hoTen,
                    hs.avatar
                  FROM baidang bd
                  LEFT JOIN nguoidung nd ON bd.maNguoiDung = nd.maNguoiDung
                  LEFT JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung
                  ORDER BY bd.ngayTao DESC";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }

    public function mAddTinTuc($user_id, $text, $image, $quyenRiengTu)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $text = $conn->real_escape_string($text);
        $image = $conn->real_escape_string($image);
        $quyenRiengTu = $conn->real_escape_string($quyenRiengTu);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $date = date('Y-m-d H:i:s');
        $query = "INSERT INTO baidang (maNguoiDung ,noidungText, noidungAnh, phamVi, ngayTao) VALUES ('$user_id', '$text', '$image','$quyenRiengTu', '$date')";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
}
