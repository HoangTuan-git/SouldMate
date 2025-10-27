<?php
// filepath: d:\PTUD\codePTUD - Copy\helper\MailService.php

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../config/mail.config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    
    /**
     * Tạo OTP ngẫu nhiên
     */
    public static function generateOTP() {
        return str_pad(random_int(0, pow(10, OTP_LENGTH) - 1), OTP_LENGTH, '0', STR_PAD_LEFT);
    }
    
    /**
     * Gửi email
     */
    private static function sendEmail($to, $toName, $subject, $body) {
        $mail = new PHPMailer(true);
        
        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';
            
            // Người gửi
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            
            // Người nhận
            $mail->addAddress($to, $toName);
            
            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Mail Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Gửi OTP đăng ký
     */
    public static function sendRegisterOTP($email, $username, $otp) {
        $subject = 'Xác thực tài khoản - Dating App';
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
                .header { background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
                .otp-box { background: #f0f0f0; padding: 20px; margin: 20px 0; text-align: center; border-radius: 8px; }
                .otp-code { font-size: 32px; font-weight: bold; color: #4CAF50; letter-spacing: 8px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                .warning { color: #ff5722; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Xác thực tài khoản</h1>
                </div>
                <div class='content'>
                    <p>Xin chào <strong>{$username}</strong>,</p>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại Dating App!</p>
                    <p>Vui lòng sử dụng mã OTP dưới đây để xác thực email của bạn:</p>
                    
                    <div class='otp-box'>
                        <div class='otp-code'>{$otp}</div>
                    </div>
                    
                    <p class='warning'>⚠️ Mã OTP có hiệu lực trong ".OTP_EXPIRY_MINUTES." phút.</p>
                    <p>Nếu bạn không yêu cầu đăng ký, vui lòng bỏ qua email này.</p>
                    
                    <div class='footer'>
                        <p>© 2025 Dating App. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($email, $username, $subject, $body);
    }
    
    /**
     * Gửi OTP quên mật khẩu
     */
    public static function sendResetPasswordOTP($email, $username, $otp) {
        $subject = 'Đặt lại mật khẩu - Dating App';
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
                .header { background: #FF9800; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
                .otp-box { background: #f0f0f0; padding: 20px; margin: 20px 0; text-align: center; border-radius: 8px; }
                .otp-code { font-size: 32px; font-weight: bold; color: #FF9800; letter-spacing: 8px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                .warning { color: #ff5722; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Đặt lại mật khẩu</h1>
                </div>
                <div class='content'>
                    <p>Xin chào <strong>{$username}</strong>,</p>
                    <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                    <p>Vui lòng sử dụng mã OTP dưới đây để tiếp tục:</p>
                    
                    <div class='otp-box'>
                        <div class='otp-code'>{$otp}</div>
                    </div>
                    
                    <p class='warning'>⚠️ Mã OTP có hiệu lực trong ".OTP_EXPIRY_MINUTES." phút.</p>
                    <p class='warning'>⚠️ Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này và đổi mật khẩu ngay!</p>
                    
                    <div class='footer'>
                        <p>© 2025 Dating App. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($email, $username, $subject, $body);
    }
    
    /**
     * Lưu OTP vào session
     */
    public static function saveOTPToSession($email, $otp, $type = 'register') {
        $_SESSION['otp_data'] = [
            'email' => $email,
            'otp' => $otp,
            'type' => $type,
            'created_at' => time(),
            'expires_at' => time() + (OTP_EXPIRY_MINUTES * 60)
        ];
    }
    
    /**
     * Verify OTP
     */
    public static function verifyOTP($email, $otp, $type = 'register') {
        if (!isset($_SESSION['otp_data'])) {
            return ['success' => false, 'message' => 'Không tìm thấy OTP'];
        }
        
        $otpData = $_SESSION['otp_data'];
        
        // Kiểm tra email
        if ($otpData['email'] !== $email) {
            return ['success' => false, 'message' => 'Email không khớp'];
        }
        
        // Kiểm tra type
        if ($otpData['type'] !== $type) {
            return ['success' => false, 'message' => 'Loại OTP không đúng'];
        }
        
        // Kiểm tra hết hạn
        if (time() > $otpData['expires_at']) {
            unset($_SESSION['otp_data']);
            return ['success' => false, 'message' => 'OTP đã hết hạn'];
        }
        
        // Kiểm tra mã OTP
        if ($otpData['otp'] !== $otp) {
            return ['success' => false, 'message' => 'Mã OTP không đúng'];
        }
        
        // Xác thực thành công
        unset($_SESSION['otp_data']);
        return ['success' => true, 'message' => 'Xác thực thành công'];
    }
    
    /**
     * Xóa OTP
     */
    public static function clearOTP() {
        unset($_SESSION['otp_data']);
    }
}
?>