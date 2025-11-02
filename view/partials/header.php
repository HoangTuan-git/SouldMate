<?php
// Determine active page for highlighting
$activePage = isset($page) && $page ? $page : ($_GET['page'] ?? 'bantin');

// Kiểm tra trạng thái Premium
$isPremiumUser = false;
if (isset($_SESSION['uid'])) {
    include_once(__DIR__ . '/../../controller/cPayment.php');
    $cPayment = new controlPayment();
    $isPremiumUser = $cPayment->checkPremiumStatus($_SESSION['uid']);
}
?>

<nav class="navbar navbar-expand-lg header-navbar">
    <div class="container-fluid px-3 px-md-4 position-relative">
        <a class="navbar-brand d-flex align-items-center" href="home.php">
            <span class="app-badge me-2 d-inline-flex align-items-center justify-content-center">
                <i class="bi bi-heart-fill"></i>
            </span>
            <span class="brand-text">SoulMatch</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage === 'bantin') ? 'active' : ''; ?>" href="home.php?page=bantin">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage === 'me') ? 'active' : ''; ?>" href="home.php?page=me">Hồ sơ của tôi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage === 'tinnhan') ? 'active' : ''; ?>" href="home.php?page=tinnhan">Tin nhắn</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage === 'timkiem') ? 'active' : ''; ?>" href="home.php?page=timkiem">Tìm kiếm</a>
                </li>
                <?php if ($isPremiumUser): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage === 'dexuat') ? 'active' : ''; ?>" href="home.php?page=dexuat">
                        <i class="bi bi-stars text-warning"></i> Đề xuất
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage === 'premium') ? 'active' : ''; ?>" href="home.php?page=premium">
                        <?php if ($isPremiumUser): ?>
                            <i class="bi bi-crown-fill text-warning"></i> Premium
                        <?php else: ?>
                            Premium
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="dropdown">
                    <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php
                            $avatarHeader = 'img/default.png';
                            $debugAvatarPath = '';
                            $debugFileExists = false;
                            if (isset($_SESSION['uid'])) {
                                include_once(__DIR__ . '/../../controller/cHoSo.php');
                                $cHoSo = new controlHoSo();
                                $profileResult = $cHoSo->getProfile($_SESSION['uid']);
                                if ($profileResult && $profileResult->num_rows > 0) {
                                    $profile = $profileResult->fetch_assoc();
                                    // Đường dẫn vật lý tuyệt đối tới file avatar
                                    $debugAvatarPath = realpath(__DIR__ . '/../../uploads/avatars/' . $profile['avatar']);
                                    $debugFileExists = !empty($profile['avatar']) && $profile['avatar'] !== 'default.png' && $debugAvatarPath && file_exists($debugAvatarPath);
                                    if ($debugFileExists) {
                                        $avatarHeader = 'uploads/avatars/' . $profile['avatar'];
                                    }
                                }
                            }
                            ?>
                            <img src="<?= htmlspecialchars($avatarHeader) ?>" class="avatar" alt="" onerror="this.onerror=null;this.src='img/default.png';">

                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="home.php?page=me">Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="home.php?page=DSDuocTim">Ai thích tôi</a></li>
                        <li><a class="dropdown-item" href="home.php?page=DSTim">Tôi thích ai</a></li>
                        <li><a class="dropdown-item" href="home.php?page=dangxuat">Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="header-divider"></div>
</nav>