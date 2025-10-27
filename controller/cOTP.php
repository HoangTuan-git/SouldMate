<?php
// filepath: d:\PTUD\codePTUD - Copy\controller\cOTP.php

include_once(__DIR__.'/../helper/MailService.php');

class controlOTP {
    
    /**
     * Gửi OTP đăng ký
     */
    public function sendRegisterOTP($email, $username) {
        // Tạo OTP
        $otp = MailService::generateOTP();
        
        // Lưu vào session
        MailService::saveOTPToSession($email, $otp, 'register');
        
        // Gửi email
        $sent = MailService::sendRegisterOTP($email, $username, $otp);
        
        if ($sent) {
            return ['success' => true, 'message' => 'OTP đã được gửi đến email của bạn'];
        } else {
            return ['success' => false, 'message' => 'Không thể gửi email. Vui lòng thử lại'];
        }
    }
    
    /**
     * Gửi OTP quên mật khẩu
     */
    public function sendResetPasswordOTP($email, $username) {
        // Tạo OTP
        $otp = MailService::generateOTP();
        
        // Lưu vào session
        MailService::saveOTPToSession($email, $otp, 'reset_password');
        
        // Gửi email
        $sent = MailService::sendResetPasswordOTP($email, $username, $otp);
        
        if ($sent) {
            return ['success' => true, 'message' => 'OTP đã được gửi đến email của bạn'];
        } else {
            return ['success' => false, 'message' => 'Không thể gửi email. Vui lòng thử lại'];
        }
    }
    
    /**
     * Verify OTP
     */
    public function verifyOTP($email, $otp, $type) {
        // Verify từ session
        return MailService::verifyOTP($email, $otp, $type);
    }
}
?>