<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP</title>
    <link rel="stylesheet" href="css/form.css">
    
</head>

<body>
    <div class="otp-container">
        <h2 style="text-align: center;">Xác thực đăng ký</h2>
        <p class="info-text">
            Mã OTP đã được gửi đến email:<br>
            <strong><?= $_SESSION['temp_register']['email'] ?? 'N/A' ?></strong>
        </p>

        <form method="POST" action="">
            <input
                type="text"
                name="otp"
                class="otp-input"
                placeholder="000000"
                maxlength="6"
                pattern="[0-9]{6}"
                required
                autofocus>

            <div class="otp-timer" id="timer">
                Mã có hiệu lực trong: <span id="countdown">5:00</span>
            </div>

            <button type="submit" name="verify_otp" class="submit-btn">Xác nhận</button>
        </form>

        <button type="button" id="resendBtn" class="resend-btn" onclick="resendOTP()" disabled>
            Gửi lại mã OTP (<span id="resendTimer">60</span>s)
        </button>

        <p class="info-text" style="margin-top: 20px;">
            <a href="home.php?page=dangky">← Quay lại đăng ký</a>
        </p>
    </div>

    <script>
        // Đếm ngược thời gian hết hạn OTP (5 phút)
        let timeLeft = 300; // 5 phút = 300 giây
        const countdownElement = document.getElementById('countdown');

        const mainTimer = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                clearInterval(mainTimer);
                alert('Mã OTP đã hết hiệu lực. Vui lòng đăng ký lại.');
                window.location.href = 'home_test.php?page=dangky';
            }
        }, 1000);

        // Đếm ngược nút gửi lại (60 giây)
        let resendTimeLeft = 60;
        const resendTimerElement = document.getElementById('resendTimer');
        const resendBtn = document.getElementById('resendBtn');

        const resendTimer = setInterval(() => {
            resendTimeLeft--;
            resendTimerElement.textContent = resendTimeLeft;

            if (resendTimeLeft <= 0) {
                clearInterval(resendTimer);
                resendBtn.disabled = false;
                resendBtn.textContent = 'Gửi lại mã OTP';
            }
        }, 1000);

        // Chỉ cho phép nhập số
        document.querySelector('.otp-input').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        function resendOTP() {
            if (confirm('Bạn có muốn gửi lại mã OTP?')) {
                window.location.href = 'home_test.php?page=dangky&resend=1';
            }
        }
    </script>
</body>

</html>

<?php
if (isset($_POST['verify_otp'])) {
    $otp = $_POST['otp'];
    include_once('controller/cNguoiDung.php');
    $userController = new controlNguoiDung();
    $userController->verifyRegisterOTP($otp);
}
?>