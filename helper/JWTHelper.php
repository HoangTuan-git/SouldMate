<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/jwt.config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper {
    
    /**
     * Tạo JWT token
     */
    public static function createToken($userId, $email) {
        $issuedAt = time();
        $expirationTime = $issuedAt + JWT_EXPIRATION;
        
        $payload = [
            'uid' => $userId,
            'email' => $email,
            'iat' => $issuedAt,
            'exp' => $expirationTime
        ];
        
        return JWT::encode($payload, JWT_SECRET_KEY, JWT_ALGORITHM);
    }
    
    /**
     * Verify và decode JWT token
     */
    public static function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));
            return [
                'valid' => true,
                'data' => $decoded
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
