<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="view/assets/css/quenMatKhau.css">
</head>
<body>
    <div class="forgot-password-page">
        <div class="forgot-password-box">
            <!-- Logo & Brand -->
            <div class="forgot-password-header">
                <div class="brand-logo">
                    <i class="bi bi-heart-fill"></i>
                </div>
                <h1 class="brand-name">SoulMatch</h1>
            </div>

            <!-- Page Title -->
            <h2 class="forgot-password-title">Quên mật khẩu</h2>
            
            <!-- Description -->
            <p class="forgot-password-description">
                Nhập địa chỉ email của bạn và chúng tôi sẽ gửi mã OTP để đặt lại mật khẩu.
            </p>

            <!-- Form -->
            <form method="post" class="forgot-password-form">
                <div class="form-group">
                    <label for="txtEmail" class="form-label">Email</label>
                    <input 
                        type="email" 
                        name="txtEmail" 
                        id="txtEmail" 
                        class="form-input"
                        placeholder="Nhập địa chỉ email của bạn"
                        required>
                </div>
                
                <button type="submit" name="sbtn" class="btn-submit">
                    Gửi mã OTP
                </button>

                <!-- Back to Login -->
                <div class="back-to-login">
                    <a href="home_test.php?page=dangnhap">← Quay lại đăng nhập</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
if(isset($_POST['sbtn'])) {
    $email = $_POST['txtEmail'];
    include_once('controller/cNguoiDung.php');
    $userController = new controlNguoiDung();
    $userController->sendResetPasswordOTP($email);
}

// Xử lý resend OTP
if(isset($_GET['resend']) && isset($_GET['email'])) {
    $email = $_GET['email'];
    include_once('controller/cNguoiDung.php');
    $userController = new controlNguoiDung();
    $userController->sendResetPasswordOTP($email);
}
?>
