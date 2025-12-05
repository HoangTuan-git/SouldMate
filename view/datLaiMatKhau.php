<div class="reset-password-page">
    <div class="reset-password-box">
        <!-- Logo & Brand -->
        <div class="reset-password-header">
            <div class="brand-logo">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="brand-name">SoulMatch</h1>
        </div>

        <!-- Title -->
        <h2 class="reset-password-title">Đặt lại mật khẩu</h2>
        
        <!-- Email Display -->
        <p class="email-display">
            <i class="bi bi-envelope"></i>
            <span><?= $_SESSION['reset_password_email'] ?? 'N/A' ?></span>
        </p>
        
        <!-- Form -->
        <form method="post" id="resetPasswordForm" class="reset-password-form">
            <div class="form-group">
                <label for="txtNewPassword" class="form-label">Mật khẩu mới</label>
                <input type="password" name="txtNewPassword" id="txtNewPassword" class="form-input" placeholder="Nhập mật khẩu mới" required minlength="6">
                <div id="passwordStrength" class="password-strength" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label for="txtConfirmPassword" class="form-label">Xác nhận mật khẩu</label>
                <input type="password" name="txtConfirmPassword" id="txtConfirmPassword" class="form-input" placeholder="Nhập lại mật khẩu" required minlength="6">
                <div id="errorMessage" class="error-message" style="display: none;"></div>
            </div>
            
            <button type="submit" class="btn-reset" name="sbtn">
                <i class="bi bi-check-circle me-2"></i>Đặt lại mật khẩu
            </button>
        </form>

        <!-- Back to Login -->
        <div class="back-to-login">
            <a href="home_test.php?page=dangnhap">
                <i class="bi bi-arrow-left"></i> Quay lại đăng nhập
            </a>
        </div>
    </div>
</div>

<script>
    const newPasswordInput = document.getElementById('txtNewPassword');
    const confirmPasswordInput = document.getElementById('txtConfirmPassword');
    const passwordStrengthDiv = document.getElementById('passwordStrength');
    const errorMessageDiv = document.getElementById('errorMessage');
    const form = document.getElementById('resetPasswordForm');
    
    // Kiểm tra độ mạnh của mật khẩu
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        if(password.length === 0) {
            passwordStrengthDiv.style.display = 'none';
            return;
        }
        
        passwordStrengthDiv.style.display = 'block';
        
        if(password.length < 6) {
            passwordStrengthDiv.className = 'password-strength weak';
            passwordStrengthDiv.textContent = 'Mật khẩu yếu (cần ít nhất 6 ký tự)';
        } else if(password.length < 10) {
            passwordStrengthDiv.className = 'password-strength medium';
            passwordStrengthDiv.textContent = 'Mật khẩu trung bình';
        } else {
            passwordStrengthDiv.className = 'password-strength strong';
            passwordStrengthDiv.textContent = 'Mật khẩu mạnh';
        }
    });
    
    // Kiểm tra mật khẩu khớp
    confirmPasswordInput.addEventListener('input', function() {
        if(this.value !== newPasswordInput.value) {
            errorMessageDiv.style.display = 'block';
            errorMessageDiv.textContent = 'Mật khẩu xác nhận không khớp';
        } else {
            errorMessageDiv.style.display = 'none';
        }
    });
    
    // Validate khi submit
    form.addEventListener('submit', function(e) {
        if(newPasswordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
            return false;
        }
        
        if(newPasswordInput.value.length < 6) {
            e.preventDefault();
            alert('Mật khẩu phải có ít nhất 6 ký tự!');
            return false;
        }
    });
</script>
<?php
if(isset($_POST['sbtn'])) {
    $newPassword = $_POST['txtNewPassword'];
    $confirmPassword = $_POST['txtConfirmPassword'];
    
    if($newPassword === $confirmPassword) {
        include_once('controller/cNguoiDung.php');
        $userController = new controlNguoiDung();
        $userController->resetPassword($newPassword);
    } else {
        echo "<script>alert('Mật khẩu xác nhận không khớp!');</script>";
    }
}

?>
