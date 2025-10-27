<?php
include_once('mKetNoi.php');

class modelQuanHeNguoiDung
{

    /**
     * Chặn người dùng
     */
    public function blockUser($maNguoiDung1, $maNguoiDung2)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            // Kiểm tra đã có quan hệ chưa
            $checkSql = "SELECT * FROM quanhenguoidung WHERE maNguoiDung1 = ? AND maNguoiDung2 = ?";
            $stmt = $con->prepare($checkSql);
            $stmt->bind_param("ii", $maNguoiDung1, $maNguoiDung2);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Đã có quan hệ, update trạng thái
                $sql = "UPDATE quanhenguoidung SET trangThai = 'chan' WHERE maNguoiDung1 = ? AND maNguoiDung2 = ?";
            } else {
                // Chưa có, insert mới
                $sql = "INSERT INTO quanhenguoidung (maNguoiDung1, maNguoiDung2, trangThai) VALUES (?, ?, 'chan')";
            }

            $stmt = $con->prepare($sql);
            $stmt->bind_param("ii", $maNguoiDung1, $maNguoiDung2);
            $kq = $stmt->execute();

            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Bỏ chặn người dùng
     */
    public function unblockUser($maNguoiDung1, $maNguoiDung2)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();
        if ($con) {
            $sql = "DELETE FROM quanhenguoidung WHERE maNguoiDung1 = ? AND maNguoiDung2 = ? AND trangThai = 'chan'";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ii", $maNguoiDung1, $maNguoiDung2);
            $kq = $stmt->execute();

            $con->close();
            return $kq;
        }
        return false;
    }
}
