<?php
include_once(__DIR__ . "/../controller/cNguoiDung.php");
$p = new controlNguoiDung();

if (!isset($_REQUEST['page'])) {
    header("refresh:0.5;url=../home_test.php?page=dangky");
}

if (isset($_REQUEST['sbtn'])) {
    $email = $_POST["txtEmail"];
    $pass = $_POST["txtPass"];
    $terms = isset($_POST["terms"]) ? $_POST["terms"] : false;

    if (!$terms) {
        echo "<script>alert('Bạn phải đồng ý với điều khoản sử dụng để đăng ký!');</script>";
    } else {
        $p->Regis($email, $pass);
    }
}
?>



<div class="signup-container">
    <div class="signup-header">
        <div class="signup-header">
            <div class="brand-logo">
                <i class="bi bi-heart-fill"></i>
            </div>
            <h1 class="brand-name">SoulMatch</h1>
        </div>
        <h1 class="signup-title">Đăng ký</h1>
        <p class="signup-subtitle">Tạo tài khoản để bắt đầu kết nối</p>
    </div>

    <form method="post" id="signupForm">
        <!-- Email Field -->
        <div class="form-group">
            <label for="txtEmail" class="form-label">
                <i class="bi bi-envelope me-2"></i>Email
            </label>
            <input type="email"
                class="form-control"
                id="txtEmail"
                name="txtEmail"
                placeholder="Nhập địa chỉ email của bạn"
                required>
        </div>

        <!-- Password Field -->
        <div class="form-group">
            <label for="txtPass" class="form-label">
                <i class="bi bi-lock me-2"></i>Mật khẩu
            </label>
            <div class="password-field">
                <input type="password"
                    class="form-control"
                    id="txtPass"
                    name="txtPass"
                    placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
                    required
                    minlength="6">
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="confirmPass" class="form-label">
                <i class="bi bi-lock-fill me-2"></i>Xác nhận mật khẩu
            </label>
            <input type="password"
                class="form-control"
                id="confirmPass"
                name="confirmPass"
                placeholder="Nhập lại mật khẩu"
                required>
        </div>

        <!-- Terms Checkbox -->
        <div class="terms-section">
            <div class="form-check">
                <input class="form-check-input"
                    type="checkbox"
                    id="terms"
                    name="terms"
                    required>
                <label class="form-check-label" for="terms">
                    Tôi đồng ý với <a href="#" onclick="showTerms()">Điều khoản sử dụng</a>
                    và <a href="#" onclick="showPrivacy()">Chính sách bảo mật</a> của ứng dụng
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" name="sbtn" class="btn-signup">
            <i class="bi bi-person-plus me-2"></i>
            Tạo tài khoản
        </button>

        <!-- Login Link -->
        <div class="login-link">
            Đã có tài khoản?
            <a href="home_test.php?page=dangnhap">Đăng nhập ngay</a>
        </div>
    </form>
</div>

<script src="view/assets/js/dangky.js"></script>