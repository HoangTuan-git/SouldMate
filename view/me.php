<?php
if (!isset($_SESSION['uid'])) {
    header("Location: home_test.php?page=dangnhap");
    exit();
}

include_once("controller/cHoSo.php");
$cHoSo = new controlHoSo();
$profileResult = $cHoSo->getProfile($_SESSION['uid']);

// Lấy dữ liệu từ hồ sơ
$u = [];
if ($profileResult && $profileResult->num_rows > 0) {
    $u = $profileResult->fetch_assoc();
}

// Gán giá trị mặc định nếu không có hồ sơ
$hoTen = $u['hoTen'] ?? 'Người dùng';
$ngaySinh = $u['ngaySinh'] ?? null;
$gioiTinh = $u['gioiTinh'] ?? '';
$moTa = $u['moTa'] ?? '';
$avatar = $u['avatar'] ?? 'default.png';
$trangThaiHenHo = $u['trangThaiHenHo'] ?? '';
$isTraiNghiem = (strtolower(trim($trangThaiHenHo)) === 'trải nghiệm');

// Avatar source
$avatarSrc = (isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'uploads/avatars/'. $avatar);

// Tên nghề nghiệp và thành phố (đã được JOIN trong query)
$nganhNgheText = $u['tenNganh'] ?? '';
$nghenghiepText = $u['tenNgheNghiep'] ?? '';
$thanhPhoText = $u['tenThanhPho'] ?? '';
$soThichText = $u['soThich'] ?? '';
?>

<div class="profile-container mt-2">
    <!-- Header với background gradient -->
    <div class="profile-header">
        <div class="profile-header-bg"></div>

        <!-- Avatar -->
        <div class="profile-avatar-wrapper">
            <img src="<?= htmlspecialchars($avatarSrc) ?>"
                alt="Avatar"
                class="profile-avatar"
               >
        </div>
    </div>

    <!-- Thông tin cơ bản -->
    <div class="profile-section">
        <h6 class="profile-section-title">Thông tin cơ bản</h6>

        <div class="profile-basic-info">
            <div class="profile-info-row">
                <i class="bi bi-person"></i>
                <span><?= htmlspecialchars($hoTen) ?></span>
            </div>

            <?php if ($ngaySinh != ""): ?>
                <div class="profile-info-row">
                    <i class="bi bi-calendar3"></i>
                    <span><?= $ngaySinh ?></span>
                </div>
            <?php endif; ?>

            <?php if ($gioiTinh): ?>
                <div class="profile-info-row">
                    <i class="bi bi-gender-ambiguous"></i>
                    <span><?= $gioiTinh ?></span>
                </div>
            <?php endif; ?>

            <?php if ($thanhPhoText): ?>
                <div class="profile-info-row">
                    <i class="bi bi-geo-alt"></i>
                    <span><?= htmlspecialchars($thanhPhoText) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Thông tin chi tiết (chỉ hiện khi Nghiêm túc) -->
    <?php if (!$isTraiNghiem): ?>
        <div class="profile-section">
            <h6 class="profile-section-title">Thông tin chi tiết</h6>
            <?php if ($nganhNgheText): ?>
                <div class="profile-detail-item">
                    <i class="bi bi-briefcase"></i>
                    <span><?= htmlspecialchars($nganhNgheText) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($nghenghiepText): ?>
                <div class="profile-detail-item">
                    <i class="bi bi-briefcase"></i>
                    <span><?= htmlspecialchars($nghenghiepText) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($soThichText): ?>
                <div class="profile-detail-item">
                    <i class="bi bi-stars"></i>
                    <span><?php echo $soThichText ?></span>
                </div>
            <?php endif; ?>
            <?php if ($moTa): ?>
                <div class="profile-about-section">
                    <strong>Về tôi:</strong>
                    <p><?= nl2br(htmlspecialchars($moTa)) ?></p>
                </div>
            <?php else: ?>
                <p class="text-muted">Chưa có mô tả về bản thân.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Tình trạng hẹn hò -->
    <div class="profile-section">
        <h6 class="profile-section-title">Tình trạng hẹn hò</h6>
        <div class="profile-status-badge">
            <i class="bi bi-heart-fill"></i>
            <span><?= htmlspecialchars($trangThaiHenHo) ?></span>
        </div>
    </div>

    <!-- Hành động tài khoản -->
    <div class="profile-section">
        <h6 class="profile-section-title">Hành động tài khoản</h6>

        <div class="profile-actions">
            <a href="home.php?page=ChinhSuaHS" class="profile-action-btn btn-primary">
                <i class="bi bi-pencil-square"></i>
                <span>Chỉnh sửa hồ sơ</span>
            </a>

            <a href="home.php?page=blocked-list" class="profile-action-btn btn-secondary">
                <i class="bi bi-person-x"></i>
                <span>Danh sách người bị chặn</span>
            </a>

            <a href="home_test.php?page=dangxuat" class="profile-action-btn btn-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Đăng xuất</span>
            </a>
        </div>
    </div>
</div>

<script>
    // Toggle dropdown menu
    function toggleProfileMenu() {
        const dropdown = document.getElementById('profileMenuDropdown');
        dropdown.classList.toggle('show');
    }

    // Đóng dropdown khi click bên ngoài
    window.addEventListener('click', function(e) {
        if (!e.target.matches('.profile-menu-btn') && !e.target.closest('.profile-menu-btn')) {
            const dropdown = document.getElementById('profileMenuDropdown');
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    });
</script>