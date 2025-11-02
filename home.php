<?php
session_start();
ob_start();
// Tiêu đề hiển thị
$titles = [
    'home'              => 'Trang chủ',
    'timkiem'           => 'Tìm kiếm',
    'dexuat'            => 'Đề xuất',
    'tinnhan'           => 'Tin nhắn',
    'me'                => 'Cài đặt',
    'profile'           => 'Profile',
    'dangky'            => 'Đăng ký',
    'dangnhap'          => 'Đăng nhập',
    'dangxuat'          => 'Đăng xuất',
    'block-user'        => 'Chặn người dùng',
    'report-user'       => 'Báo cáo vi phạm',
    'otpDangKy'         => 'Xác thực OTP',
    'otpQuenMatKhau'    => 'Xác thực OTP',
    'datLaiMatKhau'     => 'Đặt lại mật khẩu',
    'quenMatKhau'       => 'Quên mật khẩu',
    'profile_quiz'      => 'Tạo hồ sơ cá nhân',
    "xuly"              => 'Xử lý'
];
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
    <?php
    // Load page-specific CSS based on current page
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';

    $cssMap = [
        'home'   => ['bantin.css', 'feed.css'],
        'bantin' => ['bantin.css', 'feed.css'],
        'dexuat' => ['dexuat.css'],
        'me'     => ['me.css'],
        'DSTim' => ['DSTim.css'],
        'DSDuocTim' => ['DSTim.css'],
        'chat-content' => ['chat-content.css'],
        'profile' => ['profile.css']
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
        <?php include_once __DIR__ . '/view/partials/header.php'; ?>
    </header>
    <main>
        <?php
        // Ensure $page has a default value
        $page = $_GET['page'] ?? 'home';
        switch ($page) {
            case 'dexuat':
                include_once 'view/dexuat.php';
                break;
            case 'timkiem':
                include_once 'view/timkiem.php';
                break;
            case 'tinnhan':
                include_once 'view/tinnhan.php';
                break;
            case 'me':
                include_once 'view/me.php';
                break;
            case 'profile':
                include_once 'view/profile.php';
                break;
            case 'ChinhSuaHS':
                include_once 'view/ChinhSuaHS.php';
                break;
            case 'block-user':
                include_once 'view/block-user.php';
                break;
            case 'report-user':
                include_once 'view/report-user.php';
                break;
            case 'dangxuat':
                include_once 'view/dangxuat.php';
                break;
            case 'DSTim':
                include_once 'view/DSTim.php';
                break;
            case 'DSDuocTim':
                include_once 'view/DSDuocTim.php';
                break;
            case 'report-post':
                include_once 'view/report-post.php';
                break;
            case 'report-message':
                include_once 'view/report-message.php';
                break;
            case 'blocked-list':
                include_once 'view/blocked-list.php';
                break;
            case 'premium':
                include_once 'view/premium.php';
                break;
            default:
                include_once 'view/bantin.php';
                break;
        }
        ?>
    </main>
    <!-- Bootstrap 5 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Set current user ID for JavaScript -->
    <script>
        window.currentUserId = <?php echo isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0; ?>;
    </script>
    <!-- Comment system -->
    <script src="view/assets/js/comment-loadmore.js"></script>
    <?php
    $jsMap = [
        'home'   => 'bantin.js',
        'bantin' => 'bantin.js',
        'dexuat' => 'dexuat.js',
        'dangky' => 'dangky.js'
    ];
    if (isset($jsMap[$page])) {
        echo '<script src="view/assets/js/' . $jsMap[$page] . '"></script>';
    }
    ?>
    <script>
  // Set user IDs and JWT token for JavaScript
    <?php if(isset($_SESSION['uid'])): ?>
    window.currentUserId = <?= $_SESSION['uid'] ?>;
    window.jwtToken = '<?= $_SESSION['jwt_token'] ?? '' ?>';
    <?php else: ?>
    window.currentUserId = null;
    window.jwtToken = null;
    <?php endif; ?>
    <?php if(isset($uid)): ?>
    window.currentReceiverId = <?= $uid ?>;
    <?php endif; ?>
</script>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script src="chat-client.js"></script>
</body>

</html>