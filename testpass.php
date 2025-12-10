<h1>Test mã hóa mật khẩu</h1>
<?php

$password = "123456";
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
echo "Original password: " . $password . "<br>";
echo "Hashed password: " . $hashedPassword;