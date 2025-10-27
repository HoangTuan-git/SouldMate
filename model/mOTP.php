<?php
// filepath: d:\PTUD\codePTUD - Copy\model\mOTP.php

include_once('model/mKetNoi.php');

class modelOTP
{

    /**
     * Lưu OTP vào database (backup)
     */
    public function saveOTP($email, $otp, $type)
    {
        $conn = new mKetNoi();
        $conn = $conn->KetNoi();

        if ($conn) {
            // Xóa OTP cũ của email này
            $deleteQuery = "DELETE FROM otp_verification WHERE email = ? AND type = ?";
            $stmtDelete = $conn->prepare($deleteQuery);
            $stmtDelete->bind_param('ss', $email, $type);
            $stmtDelete->execute();

            // Thêm OTP mới
            $expiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));
            $query = "INSERT INTO otp_verification (email, otp, type, expires_at) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssss', $email, $otp, $type, $expiresAt);

            return $stmt->execute();
        }
        return false;
    }

    /**
     * Verify OTP từ database
     */
    public function verifyOTP($email, $otp, $type)
    {
        $conn = new mKetNoi();
        $conn = $conn->KetNoi();

        if ($conn) {
            $query = "SELECT * FROM otp_verification 
                     WHERE email = ? AND otp = ? AND type = ? 
                     AND expires_at > NOW() 
                     LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sss', $email, $otp, $type);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Xóa OTP sau khi verify
                $deleteQuery = "DELETE FROM otp_verification WHERE email = ? AND type = ?";
                $stmtDelete = $conn->prepare($deleteQuery);
                $stmtDelete->bind_param('ss', $email, $type);
                $stmtDelete->execute();

                return true;
            }
        }
        return false;
    }
}
