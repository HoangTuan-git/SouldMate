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

$user = $p->checkHoSoExists($profileUserId);
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
                            <?= $userData['trangThaiHenHo'] == 'nghiemtuc' ? 'Tìm kiếm nghiêm túc' : 'Tìm kiếm trải nghiệm' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons (Heart & More) -->
            <div class="profile-actions">
                <button class="profile-action-btn profile-like-btn" onclick="toggleLike('<?= $profileUserId ?>')">
                    <i class="bi bi-heart"></i>
                    <span>Thích</span>
                </button>

                <div class="dropdown">
                    <button class="profile-action-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="home.php?page=tinnhan&uid=<?= $profileUserId ?>">
                                <i class="bi bi-chat-dots me-2"></i>Nhắn tin ngay
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="blockUser('<?= $profileUserId ?>'); return false;">
                                <i class="bi bi-slash-circle me-2"></i>Chặn người dùng
                            </a>
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

        <!-- Nghề nghiệp Section -->
        <?php if ($ngheNghiep): ?>
            <div class="profile-section">
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
    function toggleLike(userId) {
        const btn = event.currentTarget;
        const icon = btn.querySelector('i');
        const isLiked = icon.classList.contains('bi-heart-fill');

        // Toggle icon
        if (isLiked) {
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
            btn.classList.remove('liked');
        } else {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            btn.classList.add('liked');
        }

        // TODO: Call API to save like status
        fetch('api/like-user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    target_user_id: userId,
                    action: isLiked ? 'unlike' : 'like'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert on error
                    if (isLiked) {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                        btn.classList.add('liked');
                    } else {
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                        btn.classList.remove('liked');
                    }
                    alert('Có lỗi xảy ra: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function blockUser(userId) {
        if (!confirm('Bạn có chắc chắn muốn chặn người dùng này?')) return;

        fetch('api/block-user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    blocked_user_id: userId,
                    action: 'block'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã chặn người dùng thành công');
                    window.location.href = 'home.php';
                } else {
                    alert('Có lỗi xảy ra: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi chặn người dùng');
            });
    }

    function reportUser(userId) {
        const reason = prompt('Lý do báo cáo (tùy chọn):');
        if (reason === null) return; // User cancelled

        fetch('api/report-user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reported_user_id: userId,
                    reason: reason || 'Không có lý do cụ thể'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã báo cáo người dùng thành công');
                } else {
                    alert('Có lỗi xảy ra: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi báo cáo người dùng');
            });
    }
</script>