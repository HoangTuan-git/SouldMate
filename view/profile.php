<?php
if (!isset($_SESSION['uid'])) {
    echo '<div class="card">';
    echo '<h3>Vui lòng đăng nhập</h3>';
    echo '<p><a href="home.php?page=dangnhap" class="btn-primary">Đăng nhập</a> hoặc <a href="home.php?page=dangky" class="btn-primary">Đăng ký</a></p>';
    echo '</div>';
    return;
}

include_once("controller/cHoSo.php");
$p = new controlHoSo();

// Kiểm tra trạng thái Premium của người dùng hiện tại
include_once("controller/cPayment.php");
$cPayment = new controlPayment();
$isPremiumUser = $cPayment->checkPremiumStatus($_SESSION['uid']);

// Get profile user ID from URL parameter
$profileUserId = isset($_REQUEST['uid']) ? (string)$_REQUEST['uid'] : null;

if (!$profileUserId) {
    echo '<div class="card">';
    echo '<h3>Không tìm thấy profile</h3>';
    echo '<p><a href="home.php" class="btn-primary">Quay lại trang chủ</a></p>';
    echo '</div>';
    return;
}

// If viewing own profile, redirect to settings page
if ($profileUserId === $_SESSION['uid']) {
    echo '<script>window.location.href = "home.php?page=me";</script>';
    return;
}

$user = $p->getProfile($profileUserId);
if (!$user || $user->num_rows === 0) {
    echo '<div class="card">';
    echo '<h3>Không tìm thấy người dùng</h3>';
    echo '<p><a href="home.php" class="btn-primary">Quay lại</a></p>';
    echo '</div>';
    return;
}

$userData = $user->fetch_assoc();

// Lấy thêm thông tin nghề nghiệp, thành phố, sở thích
$ngheNghiep = $userData['tenNgheNghiep'] ?? '';
$thanhPho = $userData['tenThanhPho'] ?? '';
$soThich = $userData['soThich'] ?? '';
$soThichArray = !empty($soThich) ? explode(',', $soThich) : [];

// Tính tuổi
$tuoi = '';
if (!empty($userData['ngaySinh'])) {
    $ngaySinh = new DateTime($userData['ngaySinh']);
    $hienTai = new DateTime();
    $tuoi = $hienTai->diff($ngaySinh)->y;
}
?>

<div class="profile-container">
    <!-- Cover Image -->
    <div class="profile-cover"></div>

    <!-- Profile Card -->
    <div class="profile-card">
        <!-- Avatar Section -->
        <div class="profile-header">
            <div class="profile-avatar-wrapper">
                <img src="uploads/avatars/<?= htmlspecialchars($userData['avatar']) ?>"
                    alt="<?= htmlspecialchars($userData['hoTen']) ?>"
                    class="profile-avatar-img">
            </div>

            <div class="profile-info">
                <h2 class="profile-name">
                    <?= htmlspecialchars($userData['hoTen']) ?><?php if ($tuoi): ?>, <?= $tuoi ?><?php endif; ?>
                </h2>

                <div class="profile-meta">
                    <?php if ($thanhPho): ?>
                        <span class="profile-location">
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($thanhPho) ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($userData['trangThaiHenHo']): ?>
                        <span class="profile-dating-status">
                            <i class="bi bi-heart"></i>
                            <?= $userData['trangThaiHenHo'] == 'nghiêm túc' ? 'Tìm kiếm nghiêm túc' : 'Tìm kiếm trải nghiệm' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons (Heart & More) -->
            <div class="profile-actions">
                <div class="dropdown">
                    <button class="profile-action-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <?php if ($isPremiumUser): ?>
                                <a class="dropdown-item" href="home.php?page=tinnhan&uid=<?= $profileUserId ?>">
                                    <i class="bi bi-chat-dots me-2"></i>Nhắn tin ngay
                                </a>
                            <?php else: ?>
                                <a class="dropdown-item" href="#" onclick="showPremiumRequired(); return false;">
                                    <i class="bi bi-chat-dots me-2"></i>Nhắn tin ngay
                                </a>
                            <?php endif; ?>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="reportUser('<?= $profileUserId ?>'); return false;">
                                <i class="bi bi-exclamation-triangle me-2"></i>Báo cáo
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="profile-section">
        <!-- Ngành nghề Section -->
        <?php if ($userData['tenNganh']): ?>
            <h3 class="profile-section-title">Ngành nghề</h3>
            <p class="profile-section-text"><?= htmlspecialchars($userData['tenNganh']) ?></p>
            <br>
        <?php endif; ?>
        <!-- Nghề nghiệp Section -->
        <?php if ($ngheNghiep): ?>
                <h3 class="profile-section-title">Nghề nghiệp</h3>
                <p class="profile-section-text"><?= htmlspecialchars($ngheNghiep) ?></p>
            </div>
        <?php endif; ?>


        <!-- Sở thích Section -->
        <?php if (!empty($soThichArray)): ?>
            <div class="profile-section">
                <h3 class="profile-section-title">Sở thích</h3>
                <div class="profile-hobbies">
                    <?php foreach ($soThichArray as $hobby): ?>
                        <?php if (trim($hobby)): ?>
                            <span class="profile-hobby-tag"><?= htmlspecialchars(trim($hobby)) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Giới thiệu về tôi Section -->
        <?php if (!empty($userData['moTa'])): ?>
            <div class="profile-section">
                <h3 class="profile-section-title">Giới thiệu về tôi</h3>
                <p class="profile-section-text"><?= nl2br(htmlspecialchars($userData['moTa'])) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>



<script>
    function showPremiumRequired() {
        if (confirm('Bạn cần mua Premium để nhắn tin trước.\n\nBạn có muốn chuyển đến trang mua Premium không?')) {
            window.location.href = 'home.php?page=premium';
        }
    }

    function reportUser(userId) {
        const reason = prompt('Vui lòng nhập lý do báo cáo:');
        if (reason && reason.trim()) {
            submitReport(userId, reason.trim());
        }
    }

    function submitReport(userId, reason) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'home.php?page=report-user';

        const uidInput = document.createElement('input');
        uidInput.type = 'hidden';
        uidInput.name = 'uid';
        uidInput.value = userId;

        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;

        form.appendChild(uidInput);
        form.appendChild(reasonInput);
        document.body.appendChild(form);
        form.submit();
    }
</script>