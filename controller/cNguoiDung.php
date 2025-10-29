<?php
include_once('model/mNguoiDung.php');
include_once('helper/JWTHelper.php');
include_once('cHoSo.php');

class controlNguoiDung
{
    public function Login($TDN, $MK): void
    {
        $p = new modelNguoiDung();
        $MK = md5($MK);
        $tblTaiKhoan = $p->mLogin($TDN, $MK);
        if (!$tblTaiKhoan) {
            echo "<script>alert('Loi ket noi')</script>";
            header("refresh:0.5;url=home_test.php?page=dangnhap");
        } else {
            if ($tblTaiKhoan->num_rows > 0) {
                //dang nhap thanh cong
                $r = $tblTaiKhoan->fetch_assoc();
                $userId = $r['maNguoiDung'];

                // Tạo JWT token
                $token = JWTHelper::createToken($userId, $TDN);

                // DEBUG: Log token
                error_log("🔍 DEBUG Login - User ID: $userId");
                error_log("🔍 DEBUG Login - JWT Token: $token");
                error_log("🔍 DEBUG Login - Token length: " . strlen($token));
                //get avatar save to session
                $hoSoController = new controlHoSo();
                $profileResult = $hoSoController->getProfile($userId);
                if ($profileResult && $profileResult->num_rows > 0) {
                    $profile = $profileResult->fetch_assoc();
                    $_SESSION['avatar'] = $profile['avatar'];
                } else {
                    $_SESSION['avatar'] = null; // Hoặc giá trị mặc định nếu không có hồ sơ
                }
                // Lưu vào session
                $_SESSION['uid'] = $userId;
                $_SESSION['email'] = $TDN;
                $_SESSION['jwt_token'] = $token;

                //nếu admin thì vào trang admin
                if ($_SESSION['uid'] == 5) {
                    echo " <script>alert('Dang nhap vào trang admin thanh cong')</script>";
                    header("refresh:0.5;url=view/quanLyViPham.php");
                    return;
                }
                // Kiểm tra nếu người dùng đã có hồ sơ chưa
                $hoSoController = new controlHoSo();
                $hasProfile = $hoSoController->checkHoSoExists($_SESSION['uid']);
                if (!$hasProfile) {
                    echo " <script>alert('Dang nhap thanh cong')</script>";
                    header("refresh:0.5;url=home_test.php?page=profile_quiz");
                } else {
                    echo " <script>alert('Dang nhap thanh cong')</script>";
                    header("refresh:0.5;url=home.php");
                }
            } else {
                //sai thong tin dang nhap
                echo "<script>alert('Dang nhap that bai')</script>";
                header("refresh:0.5;url=home_test.php?page=dangnhap");
            }
        }
    }
    public function Regis($TDN, $MK): void
    {
        $p = new modelNguoiDung();

        if (!$p->isUserExist($TDN)) {
            // Email chưa tồn tại, lưu thông tin tạm vào session
            $_SESSION['temp_register'] = [
                'email' => $TDN,
                'password' => md5($MK)
            ];

            // Gửi OTP
            include_once('controller/cOTP.php');
            $otpController = new controlOTP();
            $result = $otpController->sendRegisterOTP($TDN, $TDN);

            if ($result['success']) {
                echo "<script>alert('Mã OTP đã được gửi đến email của bạn');</script>";
                header("refresh:0.5;url=home_test.php?page=otpDangKy");
            } else {
                echo "<script>alert('Lỗi gửi email: {$result['message']}');</script>";
                header("refresh:0.5;url=home_test.php?page=dangky");
            }
        } else {
            echo '<script>alert("Tên đăng nhập hoặc email đã tồn tại.")</script>';
            header("refresh:0;url=home_test.php?page=dangky");
        }
    }
    public function isUserExist($user)
    {
        $p = new modelNguoiDung();
        return $p->isUserExist($user);
    }

    public function getUser($uid)
    {
        $p = new modelNguoiDung();
        $tblTaiKhoan = $p->mGetUser($uid);
        return $tblTaiKhoan;
    }

    /**
     * Xác nhận OTP đăng ký và hoàn tất đăng ký
     */
    public function verifyRegisterOTP($otp)
    {
        if (!isset($_SESSION['temp_register'])) {
            echo "<script>alert('Phiên đăng ký đã hết hạn');</script>";
            header("refresh:0;url=home_test.php?page=dangky");
            return;
        }

        $email = $_SESSION['temp_register']['email'];
        $password = $_SESSION['temp_register']['password'];

        // Verify OTP
        include_once('controller/cOTP.php');
        $otpController = new controlOTP();
        $result = $otpController->verifyOTP($email, $otp, 'register');

        if ($result['success']) {
            // OTP đúng, insert user vào database
            $p = new modelNguoiDung();
            $inserted = $p->mRegis($email, $password);

            if ($inserted) {
                // Xóa temp data
                unset($_SESSION['temp_register']);

                // Tự động đăng nhập - lấy thông tin user mới
                $user = $p->mLogin($email, $password);
                if ($user && $user->num_rows > 0) {
                    $r = $user->fetch_assoc();
                    $_SESSION['uid'] = $r['maNguoiDung'];
                    $_SESSION['email'] = $email;

                    // Tạo JWT token
                    $token = JWTHelper::createToken($r['maNguoiDung'], $email);
                    $_SESSION['jwt_token'] = $token;

                    error_log("🔍 DEBUG Register - User ID: " . $r['maNguoiDung']);
                    error_log("🔍 DEBUG Register - JWT Token: $token");
                }

                echo "<script>alert('Đăng ký thành công!');</script>";
                //redirect to create profile quiz 
                header("refresh:0.5;url=home_test.php?page=profile_quiz");
            } else {
                echo "<script>alert('Lỗi khi tạo tài khoản');</script>";
                header("refresh:0.5;url=home_test.php?page=dangky");
            }
        } else {
            echo "<script>alert('{$result['message']}');</script>";
            header("refresh:0.5;url=home_test.php?page=otpDangKy");
        }
    }

    /**
     * Gửi OTP quên mật khẩu
     */
    public function sendResetPasswordOTP($email)
    {
        $p = new modelNguoiDung();

        if ($p->isUserExist($email)) {
            // Email tồn tại, lưu vào session
            $_SESSION['reset_password_email'] = $email;

            // Gửi OTP
            include_once('controller/cOTP.php');
            $otpController = new controlOTP();
            $result = $otpController->sendResetPasswordOTP($email, $email);

            if ($result['success']) {
                echo "<script>alert('Mã OTP đã được gửi đến email của bạn');</script>";
                header("refresh:0.5;url=home_test.php?page=otpQuenMatKhau");
            } else {
                echo "<script>alert('Lỗi gửi email: {$result['message']}');</script>";
                header("refresh:0.5;url=home_test.php?page=quenMatKhau");
            }
        } else {
            echo "<script>alert('Email không tồn tại trong hệ thống');</script>";
            header("refresh:0.5;url=home_test.php?page=quenMatKhau");
        }
    }

    /**
     * Verify OTP quên mật khẩu
     */
    public function verifyResetPasswordOTP($otp)
    {
        if (!isset($_SESSION['reset_password_email'])) {
            echo "<script>alert('Phiên đặt lại mật khẩu đã hết hạn');</script>";
            header("refresh:0;url=home_test.php?page=quenMatKhau");
            return;
        }

        $email = $_SESSION['reset_password_email'];

        // Verify OTP
        include_once('controller/cOTP.php');
        $otpController = new controlOTP();
        $result = $otpController->verifyOTP($email, $otp, 'reset_password');

        if ($result['success']) {
            // OTP đúng, chuyển đến trang đặt lại mật khẩu
            echo "<script>alert('Xác thực thành công!');</script>";
            header("refresh:0.5;url=home_test.php?page=datLaiMatKhau");
        } else {
            echo "<script>alert('{$result['message']}');</script>";
            header("refresh:0.5;url=home_test.php?page=otpQuenMatKhau");
        }
    }

    /**
     * Đặt lại mật khẩu
     */
    public function resetPassword($newPassword)
    {
        if (!isset($_SESSION['reset_password_email'])) {
            echo "<script>alert('Phiên đặt lại mật khẩu đã hết hạn');</script>";
            header("refresh:0;url=home_test.php?page=quenMatKhau");
            return;
        }

        $email = $_SESSION['reset_password_email'];
        $hashedPassword = md5($newPassword);

        $p = new modelNguoiDung();
        $updated = $p->updatePassword($email, $hashedPassword);

        if ($updated) {
            unset($_SESSION['reset_password_email']);
            echo "<script>alert('Đặt lại mật khẩu thành công!');</script>";
            header("refresh:0.5;url=home_test.php?page=dangnhap");
        } else {
            echo "<script>alert('Lỗi khi đặt lại mật khẩu');</script>";
            header("refresh:0.5;url=home_test.php?page=datLaiMatKhau");
        }
    }
}
