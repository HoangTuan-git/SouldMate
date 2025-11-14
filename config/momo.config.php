<?php
/**
 * Cấu hình MoMo Payment Gateway (Test Environment)
 * Đăng ký tài khoản test tại: https://developers.momo.vn
 */

// Thông tin Partner - Lấy từ MoMo Developer Portal
define('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'); // Partner Code (test)
define('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j'); // Access Key (test)
define('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'); // Secret Key (test)

// Endpoint API
define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'); // Test endpoint
define('MOMO_CALLBACK_PORT', 8000); // Port for the API
// URL Callback
define('MOMO_RETURN_URL', 'http://localhost:'.MOMO_CALLBACK_PORT.'/SouldMate/payment/momo-return.php'); // URL trả về sau khi thanh toán
define('MOMO_NOTIFY_URL', 'http://localhost:'.MOMO_CALLBACK_PORT.'/SouldMate/payment/momo-notify.php'); // URL nhận thông báo từ MoMo (IPN)

// Cấu hình gói Premium
define('PREMIUM_PACKAGES', [
    'premium_1month' => [
        'name' => 'Premium 1 tháng',
        'price' => 50000, // 50,000 VNĐ
        'duration' => 30, // 30 ngày
        'description' => 'Gói Premium 1 tháng - Truy cập đầy đủ tính năng'
    ],
    'premium_3months' => [
        'name' => 'Premium 3 tháng',
        'price' => 120000, // 120,000 VNĐ (giảm 20%)
        'duration' => 90, // 90 ngày
        'description' => 'Gói Premium 3 tháng - Tiết kiệm 20%'
    ],
    'premium_6months' => [
        'name' => 'Premium 6 tháng',
        'price' => 200000, // 200,000 VNĐ (giảm 33%)
        'duration' => 180, // 180 ngày
        'description' => 'Gói Premium 6 tháng - Tiết kiệm 33%'
    ],
    'premium_1year' => [
        'name' => 'Premium 1 năm',
        'price' => 350000, // 350,000 VNĐ (giảm 42%)
        'duration' => 365, // 365 ngày
        'description' => 'Gói Premium 1 năm - Tiết kiệm 42%'
    ]
]);

// Cấu hình khác
define('MOMO_REQUEST_TYPE', 'captureWallet'); // Loại thanh toán
define('MOMO_ORDER_INFO', 'Thanh toán gói Premium SoulMatch');
define('MOMO_LANG', 'vi'); // Ngôn ngữ
