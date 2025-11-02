<?php
include_once('model/mKetNoi.php');
class modelNguoiDung
{
    private function execQuery($query)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        if ($conn) {
            $result = $conn->query($query);
            $p->NgatKetNoi($conn);
            return $result;
        } else {
            $p->NgatKetNoi($conn);
            return false;
        }
    }
    public function mGetUser($uid)
    {
        $query = "select * from nguoidung where maNguoiDung = $uid";
        $result = $this->execQuery($query);
        return $result;
    }
    public function isUserExist($user)
    {
        $chkUser = "select * from nguoidung where email='$user'";
        $result = $this->execQuery($chkUser);
        return $result->num_rows > 0;
    }

    public function mRegis($email, $pass)
    {
        // $pass đã là MD5 hash rồi từ controller
        // Không insert maNguoiDung vì nó AUTO_INCREMENT
        $strRegis = "INSERT INTO nguoidung (email, matKhau) VALUES ('$email', '$pass')";
        $result = $this->execQuery($strRegis);
        return $result;
    }
    public function mLogin($email, $pass)
    {
        // $pass phải là MD5 hash để so sánh với matKhau trong DB
        // SELECT thêm trangThaiViPham và role để kiểm tra
        $strLogin = "SELECT maNguoiDung, email, matKhau, trangThaiViPham, role 
                     FROM nguoidung 
                     WHERE email='$email' AND matKhau='$pass'";
        $result =  $this->execQuery($strLogin);
        return $result;
    }

    public function updatePassword($email, $hashedPassword)
    {
        $strUpdate = "update nguoidung set matKhau='$hashedPassword' where email='$email'";
        $result = $this->execQuery($strUpdate);
        return $result;
    }
}
