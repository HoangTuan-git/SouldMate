<?php
include_once("model/mKetNoi.php");
class modelTinNhan
{
    private function execQuery($query)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        if ($conn) {
            // Execute raw query. Do NOT escape the full SQL string with
            // mysqli_real_escape_string — that corrupts the SQL quoting.
            // Use prepared statements for any user-supplied values instead.
            $result = $conn->query($query);
            $p->NgatKetNoi($conn);
            return $result;
        } else {
            $p->NgatKetNoi($conn);
            return false;
        }
    }
    public function mGetAllMessage($uid1, $uid2)
    {
        $strget = "select * from tinnhan where (maNguoiDung1 = $uid1 and maNguoiDung2 = $uid2) or (maNguoiDung1 = $uid2 and maNguoiDung2 = $uid1) order by thoiGianGui";
        $result =  $this->execQuery($strget);
        return $result;
    }
    public function mGetLastMessage($uid1, $uid2)
    {
        $strget = "select * from tinnhan where (maNguoiDung1 = $uid1 and maNguoiDung2 = $uid2) or (maNguoiDung1 = $uid2 and maNguoiDung2 = $uid1) order by thoiGianGui desc limit 1";
        $result =  $this->execQuery($strget);
        return $result;
    }
    public function mGetLastMessageId($uid1, $uid2)
    {
        $strget = "select maTinNhan from tinnhan where (maNguoiDung1 = $uid1 and maNguoiDung2 = $uid2) or (maNguoiDung1 = $uid2 and maNguoiDung2 = $uid1) order by thoiGianGui desc limit 1";
        $result =  $this->execQuery($strget);
        return $result;
    }

    public function mSendMessage($uid1, $uid2, $noidung)
    {
        // Use prepared statement to avoid SQL injection and quoting issues
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        if (!$conn) return false;

        $sql = "INSERT INTO tinnhan (maNguoiDung1, maNguoiDung2, noiDungText, thoiGianGui) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $p->NgatKetNoi($conn);
            return false;
        }
        $stmt->bind_param('iis', $uid1, $uid2, $noidung);
        $ok = $stmt->execute();
        $stmt->close();
        $p->NgatKetNoi($conn);
        return $ok;
    }

    public function mGetAllFriend($uid)
    {
        // Lấy tất cả người dùng:
        // 1. Đã ghép (trangThai = 'ghep') - dù có tin nhắn hay chưa
        // 2. Có tin nhắn (Premium nhắn tin trước) - chưa ghép nhưng có tin nhắn
        // Loại trừ: 'chan', 'thich', 'boqua'
        $strget = "
            SELECT DISTINCT 
                nguoidung.*,
                hosonguoidung.*,
                quanhenguoidung.trangThai,
                quanhenguoidung.maNguoiDung1,
                quanhenguoidung.maNguoiDung2,
                (SELECT MAX(thoiGianGui) FROM tinnhan 
                 WHERE (tinnhan.maNguoiDung1 = $uid AND tinnhan.maNguoiDung2 = nguoidung.maNguoiDung)
                    OR (tinnhan.maNguoiDung2 = $uid AND tinnhan.maNguoiDung1 = nguoidung.maNguoiDung)
                ) as lastMessageTime
            FROM nguoidung
            INNER JOIN hosonguoidung ON nguoidung.maNguoiDung = hosonguoidung.maNguoiDung
            INNER JOIN quanhenguoidung ON 
                (nguoidung.maNguoiDung = quanhenguoidung.maNguoiDung1 AND quanhenguoidung.maNguoiDung2 = $uid)
                OR (nguoidung.maNguoiDung = quanhenguoidung.maNguoiDung2 AND quanhenguoidung.maNguoiDung1 = $uid)
            WHERE nguoidung.maNguoiDung != $uid
                AND quanhenguoidung.trangThai NOT IN ('chan', 'thich', 'boqua')
                AND (
                    quanhenguoidung.trangThai = 'ghep'
                    OR EXISTS (
                        SELECT 1 FROM tinnhan 
                        WHERE (tinnhan.maNguoiDung1 = $uid AND tinnhan.maNguoiDung2 = nguoidung.maNguoiDung)
                           OR (tinnhan.maNguoiDung2 = $uid AND tinnhan.maNguoiDung1 = nguoidung.maNguoiDung)
                    )
                )
            ORDER BY lastMessageTime DESC
        ";
        
        $result = $this->execQuery($strget);
        $friendList = array();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $friendList[] = $row;
            }
        }
        
        return $friendList;
    }

    public function mCheckBlocked($uid1, $uid2)
    {
        $strcheck = "select * from quanhenguoidung where (maNguoiDung1 = $uid1 and maNguoiDung2 = $uid2 and trangThai = 'chan') or (maNguoiDung1 = $uid2 and maNguoiDung2 = $uid1 and trangThai = 'chan')";
        $result =  $this->execQuery($strcheck);
        return $result;
    }

    public function getMessageTime($maTinNhan)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        if (!$conn) return null;

        $sql = "SELECT thoiGianGui FROM tinnhan WHERE maTinNhan = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $p->NgatKetNoi($conn);
            return null;
        }
        $stmt->bind_param('i', $maTinNhan);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $p->NgatKetNoi($conn);
        
        return $row ? $row['thoiGianGui'] : null;
    }
}
