<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="assets/css/datLaiMatKhau.css">
    
</head>
<body>
    <?php
    $_SESSION['reset_password_email'] = $_SESSION['email'];
    ?>
    <form method="post" id="resetPasswordForm">
        <h2>Đặt lại mật khẩu</h2>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px;">
            Email: <strong><?= $_SESSION['reset_password_email'] ?? 'N/A' ?></strong>
        </p>
        
        <div class="form-group">
            <label for="txtNewPassword">Mật khẩu mới:</label>
            <input type="password" name="txtNewPassword" id="txtNewPassword" required minlength="6">
            <div id="passwordStrength" class="password-strength" style="display: none;"></div>
        </div>
        
        <div class="form-group">
            <label for="txtConfirmPassword">Xác nhận mật khẩu:</label>
            <input type="password" name="txtConfirmPassword" id="txtConfirmPassword" required minlength="6">
            <div id="errorMessage" class="error-message" style="display: none;"></div>
        </div>
        
        <input type="submit" class="btn" value="Đặt lại mật khẩu" name="sbtn">
    </form>

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
</body>
</html>

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

// Kiểm tra session
if(!isset($_SESSION['reset_password_email'])) {
    echo "<script>
        alert('Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thử lại.');
        window.location.href = 'home.php?page=me';
    </script>";
}
?>
