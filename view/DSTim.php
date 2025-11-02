<?php
if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Bạn cần đăng nhập để xem danh sách này.');</script>";
    header("refresh:0;url=home.php?page=dangnhap");
    exit;
}

include_once("controller/cTim.php");
include_once("controller/cHoSo.php");
$timController = new cTim();

// XỬ LÝ XÓA (BỎ THÍCH)
if (isset($_POST['removeUser']) && isset($_SESSION['uid'])) {
    $targetUserId = intval($_POST['targetUserId']);
    $result = $timController->unlikeUser($_SESSION['uid'], $targetUserId);
    $_SESSION['removeResult'] = $result;

    // Redirect để tránh resubmit
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Hiển thị kết quả nếu có
if (isset($_SESSION['removeResult'])) {
    $result = $_SESSION['removeResult'];
    $alertType = $result['success'] ? 'success' : 'danger';
    echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 300px;">';
    echo htmlspecialchars($result['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['removeResult']);
}

// Lấy danh sách người mình đã thích
$me_liked_people = $timController->GetMyLikedUsers($_SESSION['uid']);
$totalLikes = $me_liked_people ? $me_liked_people->num_rows : 0;
?>

<!-- Form hidden để xóa -->
<form id="removeForm" method="POST" style="display: none;">
    <input type="hidden" name="targetUserId" id="targetUserIdRemove">
    <input type="hidden" name="removeUser" value="1">
</form>

<!-- Header -->
<div class="likes-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="bi bi-heart-fill"></i> Danh sách người đã thích</h1>
                <p class="mb-0">Quản lý những người bạn đã bày tỏ sự quan tâm</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="stats-badge d-inline-block">
                    <h3><?= $totalLikes ?></h3>
                    <small>Người đã thích</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container pb-5">
    <?php if ($me_liked_people && $me_liked_people->num_rows > 0): ?>
        <div class="row">
            <?php while ($person = $me_liked_people->fetch_assoc()):
                // Tính tuổi
                $birthDate = new DateTime($person['ngaySinh']);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;
            ?>
                <div class="col-12">
                    <div class="person-card">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Avatar -->
                            <div class="avatar-wrapper">
                                <img src="uploads/avatars/<?= htmlspecialchars($person['avatar'] ?? 'default.png') ?>"
                                    alt="Avatar"
                                    class="avatar"
                                    onerror="this.src='uploads/avatars/default.png'">
                            </div>

                            <!-- Thông tin -->
                            <div class="person-info flex-grow-1">
                                <h5>
                                    <?= htmlspecialchars($person['hoTen']) ?>, <?= $age ?> tuổi
                                </h5>

                                <?php if (!empty($person['tenThanhPho'])): ?>
                                    <div class="person-detail">
                                        <i class="bi bi-geo-alt-fill text-danger"></i>
                                        <?= htmlspecialchars($person['tenThanhPho']) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($person['tenNghe'])): ?>
                                    <div class="person-detail">
                                        <i class="bi bi-briefcase-fill text-primary"></i>
                                        <?= htmlspecialchars($person['tenNghe']) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($person['moTa'])): ?>
                                    <div class="person-detail mt-2">
                                        <small class="text-muted">
                                            <?= htmlspecialchars(mb_substr($person['moTa'], 0, 100)) ?>
                                            <?= mb_strlen($person['moTa']) > 100 ? '...' : '' ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column gap-2 align-items-end">
                                <a href="home.php?page=profile&uid=<?= $person['maNguoiDung'] ?>"
                                    class="btn btn-profile">
                                    <i class="bi bi-person"></i> Xem hồ sơ
                                </a>
                                <button onclick="removeUser(<?= $person['maNguoiDung'] ?>)"
                                    class="btn-remove"
                                    title="Bỏ thích">
                                    <i class="bi bi-x-lg"></i> Bỏ thích
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">💔</div>
            <h3>Bạn chưa thích ai</h3>
            <p class="text-muted">Hãy tiếp tục khám phá và kết nối với những người mới!</p>
            <a href="home.php?page=timkiem" class="btn btn-profile mt-3">
                <i class="bi bi-search"></i> Khám phá ngay
            </a>
        </div>
    <?php endif; ?>
</div>
<script>
    function removeUser(userId) {
        if (confirm('Bạn có chắc muốn bỏ thích người này?')) {
            document.getElementById('targetUserIdRemove').value = userId;
            document.getElementById('removeForm').submit();
        }
    }

    // Auto hide alert sau 3 giây
    document.addEventListener('DOMContentLoaded', function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 3000);
        }
    });
</script>