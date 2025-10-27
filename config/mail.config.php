<?php
// Load environment variables
require_once __DIR__ . '/../helper/EnvLoader.php';
EnvLoader::load();

// Cấu hình email
define('MAIL_HOST', EnvLoader::get('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', EnvLoader::getInt('MAIL_PORT', 587));
define('MAIL_USERNAME', EnvLoader::get('MAIL_USERNAME', 'your_email@gmail.com'));
define('MAIL_PASSWORD', EnvLoader::get('MAIL_PASSWORD', 'your_app_password'));
define('MAIL_FROM_EMAIL', EnvLoader::get('MAIL_FROM_EMAIL', EnvLoader::get('MAIL_USERNAME', 'your_email@gmail.com')));
define('MAIL_FROM_NAME', EnvLoader::get('MAIL_FROM_NAME', 'SoulMatch'));
define('MAIL_ENCRYPTION', EnvLoader::get('MAIL_ENCRYPTION', 'tls'));

// Cấu hình OTP
define('OTP_EXPIRY_MINUTES', EnvLoader::getInt('OTP_EXPIRY_MINUTES', 5));
define('OTP_LENGTH', EnvLoader::getInt('OTP_LENGTH', 6));
?>