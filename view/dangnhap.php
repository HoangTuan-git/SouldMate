<?php
if (isset($_REQUEST['sbtn'])) {
    include_once("controller/cNguoiDung.php");
    $p = new controlNguoiDung();
    $p->Login($_REQUEST["txtEmail"], $_REQUEST["txtPass"]);
}
?>

<div class="login-page">
    <div class="login-box">
        <!-- Logo & Brand -->
        <div class="login-header">
            <div class="brand-logo">
                <i class="bi bi-heart-fill"></i>
            </div>
            <h1 class="brand-name">SoulMatch</h1>
        </div>

        <!-- Login Title -->
        <h2 class="login-title">Đăng nhập</h2>

        <!-- Login Form -->
        <form method="post" action="" class="login-form">
            <!-- Email Field -->
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

            <!-- Password Field -->
            <div class="form-group">
                <label for="txtPass" class="form-label">Mật khẩu</label>
                <input
                    type="password"
                    name="txtPass"
                    id="txtPass"
                    class="form-input"
                    placeholder="Nhập mật khẩu của bạn"
                    required>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="sbtn" class="btn-login">
                Đăng nhập
            </button>

            <!-- Forgot Password Link -->
            <div class="forgot-password">
                <a href="home_test.php?page=quenmatkhau">Quên mật khẩu?</a>
            </div>

            <!-- Divider -->
            <div class="divider">   
                <span>hoặc</span>
            </div>

            <!-- Register Link -->
            <div class="register-link">
                Chưa có tài khoản? <a href="home_test.php?page=dangky">Đăng ký ngay</a>
            </div>
        </form>
    </div>
</div>