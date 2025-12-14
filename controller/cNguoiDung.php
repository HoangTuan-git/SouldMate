<?php
include_once('model/mNguoiDung.php');
include_once('helper/JWTHelper.php');
include_once('cHoSo.php');

class controlNguoiDung
{
    public function Login($TDN, $MK): void
    {
        $p = new modelNguoiDung();
        //hash password argon2id
        
        $tblTaiKhoan = $p->mLogin($TDN, $MK);
        if ($tblTaiKhoan === false) {
            echo "<script>alert('Loi ket noi')</script>";
            header("refresh:0.5;url=home_test.php?page=dangnhap");
        } else {
            $r = $tblTaiKhoan->fetch_assoc();
            if (password_verify($MK, $r['matKhau'])) {
                //dang nhap thanh cong
                $userId = $r['maNguoiDung'];
                
                // 🔥 BƯỚC 1: Tự động mở khóa tài khoản nếu đã hết hạn
                if (isset($r['trangThaiViPham']) && $r['trangThaiViPham'] == 'khoa') {
                    include_once('model/mBaoCaoViPham.php');
                    $modelViPham = new modelBaoCaoViPham();
                    
                    // Kiểm tra và tự động mở khóa
                    $unlockCount = $modelViPham->autoUnlockExpiredAccounts();
                    
                    // Reload thông tin user sau khi mở khóa
                    $tblTaiKhoan = $p->mLogin($TDN, $MK);
                    $r = $tblTaiKhoan->fetch_assoc();
                    
                    // Nếu vẫn bị khóa sau khi check
                    if ($r['trangThaiViPham'] == 'khoa') {
                        // Lấy thông tin chi tiết về khóa
                        $userInfo = $modelViPham->getUserInfo($userId);
                        
                        if ($userInfo && $userInfo['ngayMoKhoa']) {
                            $ngayMoKhoa = date('d/m/Y H:i', strtotime($userInfo['ngayMoKhoa']));
                            $soNgayConLai = ceil((strtotime($userInfo['ngayMoKhoa']) - time()) / 86400);
                            
                            echo "<script>alert('Tài khoản bị khóa đến $ngayMoKhoa (còn $soNgayConLai ngày).\\n\\nLý do: " . addslashes($userInfo['lyDoKhoa']) . "')</script>";
                        } else {
                            echo "<script>alert('Tài khoản của bạn đã bị khóa vĩnh viễn.\\n\\nVui lòng liên hệ quản trị viên.')</script>";
                        }
                        
                        header("refresh:0.5;url=home_test.php?page=dangnhap");
                        return;
                    } else {
                        // Đã được mở khóa tự động
                        echo "<script>alert('Tài khoản của bạn đã được mở khóa tự động!')</script>";
                    }
                }

                // 🔥 BƯỚC 2: Tự động vô hiệu hóa Premium hết hạn
                include_once('model/mPayment.php');
                $modelPayment = new modelPayment();
                $deactivatedCount = $modelPayment->deactivateExpiredPremium();
                
                if ($deactivatedCount > 0) {
                    error_log("Deactivated $deactivatedCount expired premium accounts");
                }

                // Tạo JWT token
                $token = JWTHelper::createToken($userId, $TDN);

                //get avatar save to session
                $hoSoController = new controlHoSo();
                $profileResult = $hoSoController->getProfile($userId);
                if ($profileResult && $profileResult->num_rows > 0) {
                    $profile = $profileResult->fetch_assoc();
                    if (!empty($profile['avatar']) && $profile['avatar'] !== 'default.png' && file_exists(__DIR__ . '/../uploads/avatars/' . $profile['avatar'])) {
                        $_SESSION['avatar'] = 'uploads/avatars/' . $profile['avatar'];
                    } else {
                        $_SESSION['avatar'] = 'img/default.png';
                    }
                } else {
                    $_SESSION['avatar'] = 'img/default.png';
                }
                // Lưu vào session
                $_SESSION['uid'] = $userId;
                $_SESSION['email'] = $TDN;
                $_SESSION['jwt_token'] = $token;
                $_SESSION['role'] = $r['role'] ?? 'user'; // Lưu role vào session

                //nếu admin thì vào trang admin
                if ($_SESSION['role'] == 'admin') {
                    echo " <script>alert('Dang nhap vào trang admin thanh cong')</script>";
                    header("refresh:0.5;url=admin.php");
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
                //lưu mật khẩu thô
                'password' => $MK
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
                $user = $p->mLogin($email);
                if ($user) {
                    $r = $user->fetch_assoc();
                    if (password_verify($password, $r['matKhau'])) {
                        $_SESSION['uid'] = $r['maNguoiDung'];
                        $_SESSION['email'] = $email;
                        // Tạo JWT token
                        $token = JWTHelper::createToken($r['maNguoiDung'], $email);
                        $_SESSION['jwt_token'] = $token;

                        $_SESSION['role'] = $r['role'] ?? 'user'; // Lưu role vào session
                        echo "<script>alert('Đăng ký thành công!');</script>";
                        //redirect to create profile quiz 
                        header("refresh:0.5;url=home_test.php?page=profile_quiz");
                    }else {
                        echo "<script>alert('Lỗi xác thực mật khẩu');</script>";
                    }
                }else{
                    echo "<script>alert('Lỗi đăng nhập tự động sau khi đăng ký');</script>";
                }

                header("refresh:0.5;url=home_test.php?page=dangnhap");

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

        $p = new modelNguoiDung();
        $updated = $p->updatePassword($email, $newPassword);

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
