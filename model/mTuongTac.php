<?php
include_once('mKetNoi.php');

class mTuongTac
{
    // Kiểm tra user đã like bài viết chưa
    public function CheckUserLiked($maNguoiDung, $maBaiDang)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $sql = "SELECT * FROM tuongtac 
                WHERE maNguoiDung = '$maNguoiDung' AND maBaiDang = '$maBaiDang'";

        $result = $conn->query($sql);
        $p->NgatKetNoi($conn);
        $p->NgatKetNoi($conn);
        return $result->num_rows > 0;
    }
    // Thêm like
    public function AddLike($maNguoiDung, $maBaiDang)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO tuongtac (maNguoiDung, maBaiDang, thoiGian) 
                VALUES ('$maNguoiDung', '$maBaiDang', '$date')";

        $kq = $conn->query($sql);
        $p->NgatKetNoi($conn);
        return $kq;
    }

    // Xóa like (unlike)
    public function RemoveLike($maNguoiDung, $maBaiDang)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $sql = "DELETE FROM tuongtac 
                WHERE maNguoiDung = '$maNguoiDung' AND maBaiDang = '$maBaiDang'";

        $kq = $conn->query($sql);
        $p->NgatKetNoi($conn);
        return $kq;
    }

    // Đếm số lượt like của bài viết
    public function CountLikes($maBaiDang)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $sql = "SELECT COUNT(*) as total FROM tuongtac 
                WHERE maBaiDang = '$maBaiDang'";

        $kq = $conn->query($sql);
        $p->NgatKetNoi($conn);
        return $kq['total'] ?? 0;
    }
}
