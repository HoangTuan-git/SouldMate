<?php
// Load environment variables
include_once("helper/EnvLoader.php");
EnvLoader::load();

// JWT Configuration
define('JWT_SECRET_KEY', EnvLoader::get('JWT_SECRET_KEY', 'my_secret_key_12345'));
define('JWT_ALGORITHM', EnvLoader::get('JWT_ALGORITHM', 'HS256'));
define('JWT_EXPIRATION', EnvLoader::getInt('JWT_EXPIRATION', 24 * 60 * 60)); // 24 hours
?>
