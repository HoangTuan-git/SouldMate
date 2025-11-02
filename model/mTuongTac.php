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
        return $result->num_rows > 0;
    }
    // Thêm like
    public function AddLike($maNguoiDung, $maBaiDang)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $date = date('Y-m-d H:i:s');
        
        // Kiểm tra xem đã like chưa trước khi insert
        $checkSql = "SELECT * FROM tuongtac WHERE maNguoiDung = '$maNguoiDung' AND maBaiDang = '$maBaiDang'";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            // Đã like rồi, không làm gì cả
            error_log("AddLike: Already liked - User: $maNguoiDung, Post: $maBaiDang");
            $p->NgatKetNoi($conn);
            return false;
        }
        
        $sql = "INSERT INTO tuongtac (maNguoiDung, maBaiDang, thoiGian) 
                VALUES ('$maNguoiDung', '$maBaiDang', '$date')";

        error_log("AddLike SQL: $sql");
        $kq = $conn->query($sql);
        
        // Log lỗi nếu có
        if (!$kq) {
            error_log("AddLike Insert Error: " . $conn->error);
        } else {
            error_log("AddLike Insert Success - User: $maNguoiDung, Post: $maBaiDang");
        }
        
        // Cập nhật số lượt thích trong bảng baidang
        if ($kq) {
            $sqlUpdate = "UPDATE baidang SET soLuotThich = soLuotThich + 1 WHERE maBaiDang = '$maBaiDang'";
            error_log("Update SQL: $sqlUpdate");
            $updateResult = $conn->query($sqlUpdate);
            if (!$updateResult) {
                error_log("Update soLuotThich Error: " . $conn->error);
            } else {
                error_log("Update soLuotThich Success for Post: $maBaiDang");
            }
        }
        
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
        
        // Cập nhật số lượt thích trong bảng baidang
        if ($kq) {
            $sqlUpdate = "UPDATE baidang SET soLuotThich = GREATEST(soLuotThich - 1, 0) WHERE maBaiDang = '$maBaiDang'";
            $conn->query($sqlUpdate);
        }
        
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

        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $p->NgatKetNoi($conn);
        return $row['total'] ?? 0;
    }
}
