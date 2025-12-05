<?php
session_start();
ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - SoulMatch</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom site CSS -->
    <link rel="stylesheet" href="view/assets/css/site.css">
    <link rel="stylesheet" href="view/assets/css/common.css">
    <link rel="stylesheet" href="view/assets/css/bantin.css">
    <link rel="stylesheet" href="view/assets/css/feed.css">
    <?php
    // Load page-specific CSS based on current page
    $page = $_GET['page'] ?? 'home_test';
    $cssMap = [
        'home_test'   => ['bantin.css', 'feed.css'],
        'bantin' => ['bantin.css', 'feed.css'],
        'dangky' => ['dangky.css'],
        'dangnhap' => ['dangnhap.css'],
        'otpDangKy' => ['otpDangKy.css'],
        'otpQuenMatKhau' => ['otpQuenMatKhau.css'],
        'datLaiMatKhau' => ['datLaiMatKhau.css'],
        'profile_quiz' => ['profile_quiz.css'],
        'taohosonghiemtuc' => ['taohoso_nghiemtuc.css'],
        'taohosotrainghiem' => ['taohoso_trainghiem.css'],
        'quenMatKhau' => ['quenMatKhau.css'],
        'timkiem' => ['timkiem.css'],
    ];

    if (!empty($cssMap[$page])) {
        foreach ($cssMap[$page] as $cssFile) {
            echo '<link rel="stylesheet" href="view/assets/css/' . $cssFile . '">';
        }
    }
    ?>

</head>

<body>
    <header>
        <?php include_once __DIR__ . '/view/partials/header_test.php'; ?>
    </header>
    <main>
        <?php
        // Ensure $page has a default value
        $page = $_GET['page'] ?? 'home_test';
        switch ($page) {
            case 'dangky':
                include_once 'view/dangky.php';
                break;
            case 'dangnhap':
                include_once 'view/dangnhap.php';
                break;
            case 'timkiem':
                include_once 'view/timkiem.php';
                break;
            case 'otpDangKy':
                include_once 'view/otpDangKy.php';
                break;
            case 'otpQuenMatKhau':
                include_once 'view/otpQuenMatKhau.php';
                break;
            case 'datLaiMatKhau':
                include_once 'view/datLaiMatKhau.php';
                break;
            case 'quenmatkhau':
                include_once 'view/quenMatKhau.php';
                break;
            case 'profile_quiz':
                include_once 'view/profile_quiz.php';
                break;
            case 'taohosonghiemtuc':
                include_once 'view/taohoso_nghiemtuc.php';
                break;
            case 'taohosotrainghiem':
                include_once 'view/taohoso_trainghiem.php';
                break;
            case 'dangxuat':
                include_once 'view/dangxuat.php';
                break;
            default:
                include_once 'view/bantin.php';
                break;
        }
        ?>
    </main>
    <!-- Bootstrap 5 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>