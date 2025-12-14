<?php
include_once('mKetNoi.php');
class mTim
{
    public function InsertUser($uid1, $uid2, $status)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        // Escape dữ liệu
        $uid1 = intval($uid1);
        $uid2 = intval($uid2);
        $status = $conn->real_escape_string($status);

        // Kiểm tra đã tim chưa
        $checkQuery = "SELECT * FROM quanhenguoidung WHERE maNguoiDung1 = '$uid1' AND maNguoiDung2 = '$uid2'";
        $checkResult = $conn->query($checkQuery);

        if ($checkResult && $checkResult->num_rows > 0) {
            // Đã tồn tại -> Không insert nữa
            $p->NgatKetNoi($conn);
            return false;
        }

        // Chưa tồn tại -> Insert mới
        $query = "INSERT INTO quanhenguoidung (maNguoiDung1, maNguoiDung2, trangthai) 
                  VALUES ($uid1, $uid2, '$status')";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }

    /**
     * Kiểm tra đã tim người này chưa
     */
    public function checkLiked($uid1, $uid2)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $uid1 = intval($uid1);
        $uid2 = intval($uid2);

        $query = "SELECT * FROM quanhenguoidung 
                  WHERE maNguoiDung1 = $uid1 AND maNguoiDung2 = $uid2";
        $result = $conn->query($query);

        $liked = ($result && $result->num_rows > 0);
        $p->NgatKetNoi($conn);

        return $liked;
    }

    /**
     * Xóa lượt thích (Unlike)
     */
    public function DeleteLike($uid1, $uid2)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $uid1 = intval($uid1);
        $uid2 = intval($uid2);

        $query = "DELETE FROM quanhenguoidung 
                  WHERE maNguoiDung1 = $uid1 AND maNguoiDung2 = $uid2";
        $result = $conn->query($query);
        $p->NgatKetNoi($conn);

        return $result;
    }

    /**
     * Xóa quan hệ giữa 2 người (cả 2 chiều)
     * Dùng cho trường hợp người được thích muốn xóa bản ghi của người thích mình
     */
    public function DeleteRelation($uid1, $uid2)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $uid1 = intval($uid1);
        $uid2 = intval($uid2);

        // Xóa bất kỳ bản ghi nào giữa 2 người (cả 2 chiều)
        $query = "DELETE FROM quanhenguoidung 
                  WHERE (maNguoiDung1 = $uid1 AND maNguoiDung2 = $uid2)
                     OR (maNguoiDung1 = $uid2 AND maNguoiDung2 = $uid1)";
        $result = $conn->query($query);
        $p->NgatKetNoi($conn);

        return $result;
    }

    /**
     * Cập nhật trạng thái thành 'ghép' nếu cả 2 đều thích nhau
     */
    public function UpdateToMatch($uid1, $uid2)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $uid1 = intval($uid1);
        $uid2 = intval($uid2);

        // Chỉ cập nhật 1 bản ghi duy nhất (bản ghi cũ có sẵn)
        // Tìm bản ghi nào tồn tại (uid2->uid1 hoặc uid1->uid2)
        $query = "UPDATE quanhenguoidung SET trangthai = 'ghep_search' 
                  WHERE (maNguoiDung1 = $uid1 AND maNguoiDung2 = $uid2)
                     OR (maNguoiDung1 = $uid2 AND maNguoiDung2 = $uid1)";

        $result = $conn->query($query);
        $p->NgatKetNoi($conn);

        return $result;
    }
    /**
     * Lấy danh sách người mà user đã thích
     */
    public function GetMyLikedUsers($userId)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $userId = intval($userId);

        $query = "SELECT u.maNguoiDung,
                         h.hoTen, h.ngaySinh, h.gioiTinh, h.avatar, h.moTa
                  FROM quanhenguoidung q
                  JOIN nguoidung u ON q.maNguoiDung2 = u.maNguoiDung
                  JOIN hosonguoidung h ON u.maNguoiDung = h.maNguoiDung
                  WHERE q.maNguoiDung1 = $userId
                    AND q.trangThai = 'thich'";

        $result = $conn->query($query);
        $p->NgatKetNoi($conn);

        return $result;
    }
    /**
     * Lấy danh sách người dùng đã thích mình
     */
    public function GetAllUserLike($userId)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $userId = intval($userId);

        $query = "SELECT u.maNguoiDung,
                         h.hoTen, h.ngaySinh, h.gioiTinh, h.avatar, h.moTa
                  FROM quanhenguoidung q
                  JOIN nguoidung u ON q.maNguoiDung1 = u.maNguoiDung
                  JOIN hosonguoidung h ON u.maNguoiDung = h.maNguoiDung
                  WHERE q.maNguoiDung2 = $userId
                    AND q.trangThai = 'thich'";

        $result = $conn->query($query);
        $p->NgatKetNoi($conn);

        return $result;
    }
}
