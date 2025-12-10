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
        // hash password argon2id
        $hashedPass = password_hash($pass, PASSWORD_ARGON2ID);
        $strRegis = "INSERT INTO nguoidung (email, matKhau) VALUES ('$email', '$hashedPass')";
        $result = $this->execQuery($strRegis);
        return $result;
    }
    public function mLogin($email)
    {
        // $pass phải là MD5 hash để so sánh với matKhau trong DB
        // SELECT thêm trangThaiViPham và role để kiểm tra
        $strLogin = "SELECT maNguoiDung, email, matKhau, trangThaiViPham, role 
                     FROM nguoidung 
                     WHERE email='$email'";
        $result =  $this->execQuery($strLogin);
        return $result -> num_rows > 0 ? $result: false;
    }

    public function updatePassword($email, $newPassword)
    {
        // Mã hóa mật khẩu mới trước khi lưu
        $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);
        $strUpdate = "update nguoidung set matKhau='$hashedPassword' where email='$email'";
        $result = $this->execQuery($strUpdate);
        return $result;
    }
}
