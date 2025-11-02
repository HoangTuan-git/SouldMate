<?php
/**
 * Cron Job - Vô hiệu hóa Premium đã hết hạn
 * Chạy file này định kỳ mỗi ngày (hoặc mỗi giờ)
 * 
 * Cách setup:
 * 1. Linux/Mac: crontab -e
 *    0 0 * * * /usr/bin/php /path/to/SouldMate/payment/cron-check-premium.php
 * 
 * 2. Windows Task Scheduler:
 *    php "d:\PTUD\SouldMate\payment\cron-check-premium.php"
 */

require_once(__DIR__ . '/../model/mPayment.php');

$model = new modelPayment();

// Vô hiệu hóa Premium đã hết hạn
$affected = $model->deactivateExpiredPremium();

// Log kết quả
$logFile = __DIR__ . '/cron.log';
$timestamp = date('Y-m-d H:i:s');
$message = "[$timestamp] Deactivated $affected expired premium subscriptions\n";

file_put_contents($logFile, $message, FILE_APPEND);

echo $message;
