<?php
include_once("model/mKetNoi.php");
class modelTinNhan
{
    private function execQuery($query)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        if ($conn) {
            //repair sql injection
            $_query = mysqli_real_escape_string($conn, $query);
            $result = $conn->query($_query);
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
        $strsend = "insert into tinnhan values('',$uid1,$uid2,'$noidung',now())";
        $result =  $this->execQuery($strsend);
        return $result;
    }

    public function mGetAllFriend($uid)
    {
        $strget1 = "select * from nguoidung join quanhenguoidung on nguoidung.maNguoiDung = quanhenguoidung.maNguoiDung1 join hosonguoidung on nguoidung.maNguoiDung = hosonguoidung.maNguoiDung where maNguoiDung2 = $uid";
        $strget2 = "select * from nguoidung join quanhenguoidung on nguoidung.maNguoiDung = quanhenguoidung.maNguoiDung2 join hosonguoidung on nguoidung.maNguoiDung = hosonguoidung.maNguoiDung where maNguoiDung1 = $uid";
        $result1 =  $this->execQuery($strget1);
        $result2 =  $this->execQuery($strget2);
        $result = array();
        if ($result1) {
            while ($row = $result1->fetch_assoc()) {
                $result[] = $row;
            }
        }
        if ($result2) {
            while ($row = $result2->fetch_assoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function mCheckBlocked($uid1, $uid2)
    {
        $strcheck = "select * from quanhenguoidung where (maNguoiDung1 = $uid1 and maNguoiDung2 = $uid2 and trangThai = 'chan') or (maNguoiDung1 = $uid2 and maNguoiDung2 = $uid1 and trangThai = 'chan')";
        $result =  $this->execQuery($strcheck);
        return $result;
    }
}
