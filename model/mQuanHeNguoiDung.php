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
            $checkSql = "SELECT trangThai FROM quanhenguoidung WHERE maNguoiDung1 = ? AND maNguoiDung2 = ?";
            $stmt = $con->prepare($checkSql);
            $stmt->bind_param("ii", $maNguoiDung1, $maNguoiDung2);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Đã có quan hệ, lưu trạng thái cũ và update sang 'chan'
                $row = $result->fetch_assoc();
                $trangThaiCu = $row['trangThai'];
                
                // Chỉ update nếu chưa bị chặn
                if ($trangThaiCu != 'chan') {
                    $sql = "UPDATE quanhenguoidung 
                            SET trangThai = 'chan', trangThaiTruocChan = ? 
                            WHERE maNguoiDung1 = ? AND maNguoiDung2 = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("sii", $trangThaiCu, $maNguoiDung1, $maNguoiDung2);
                    $kq = $stmt->execute();
                } else {
                    // Đã bị chặn rồi
                    $kq = true;
                }
            } else {
                // Chưa có quan hệ, insert mới với trạng thái 'chan'
                $sql = "INSERT INTO quanhenguoidung (maNguoiDung1, maNguoiDung2, trangThai, trangThaiTruocChan) 
                        VALUES (?, ?, 'chan', NULL)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ii", $maNguoiDung1, $maNguoiDung2);
                $kq = $stmt->execute();
            }

            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Bỏ chặn người dùng - khôi phục trạng thái trước khi chặn
     */
    public function unblockUser($maNguoiDung1, $maNguoiDung2)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();
        if ($con) {
            // Lấy trạng thái trước khi chặn
            $checkSql = "SELECT trangThaiTruocChan FROM quanhenguoidung 
                         WHERE maNguoiDung1 = ? AND maNguoiDung2 = ? AND trangThai = 'chan'";
            $stmt = $con->prepare($checkSql);
            $stmt->bind_param("ii", $maNguoiDung1, $maNguoiDung2);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $trangThaiTruoc = $row['trangThaiTruocChan'];
                
                if ($trangThaiTruoc != null) {
                    // Có trạng thái trước đó, khôi phục lại
                    $sql = "UPDATE quanhenguoidung 
                            SET trangThai = ?, trangThaiTruocChan = NULL 
                            WHERE maNguoiDung1 = ? AND maNguoiDung2 = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("sii", $trangThaiTruoc, $maNguoiDung1, $maNguoiDung2);
                } else {
                    // Không có trạng thái trước đó (tức là lúc chặn chưa có quan hệ), xóa luôn
                    $sql = "DELETE FROM quanhenguoidung 
                            WHERE maNguoiDung1 = ? AND maNguoiDung2 = ? AND trangThai = 'chan'";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("ii", $maNguoiDung1, $maNguoiDung2);
                }
                
                $kq = $stmt->execute();
            } else {
                // Không tìm thấy record bị chặn
                $kq = false;
            }

            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Lấy danh sách người dùng bị chặn
     */
    public function getBlockedUsers($maNguoiDung)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT qh.maNguoiDung2 as maNguoiDung, qh.ngayTao 
                    FROM quanhenguoidung qh 
                    WHERE qh.maNguoiDung1 = ? AND qh.trangThai = 'chan'
                    ORDER BY qh.ngayTao DESC";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maNguoiDung);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $con->close();
            return $result;
        }
        return false;
    }
}
