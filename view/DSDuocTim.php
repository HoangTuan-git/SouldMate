<?php
if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Bạn cần đăng nhập để xem danh sách này.');</script>";
    header("refresh:0;url=home.php?page=dangnhap");
    exit;
}

include_once("controller/cTim.php");
include_once("controller/cHoSo.php");
$timController = new cTim();

// XỬ LÝ LIKE NGƯỢC LẠI (Thích lại người đã thích mình)
if (isset($_POST['likeBackUser']) && isset($_SESSION['uid'])) {
    $targetUserId = intval($_POST['targetUserId']);
    $result = $timController->likeUser($_SESSION['uid'], $targetUserId);
    $_SESSION['likeResult'] = $result;

    // Redirect để tránh resubmit
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// XỬ LÝ BỎ THÍCH
if (isset($_POST['removeUser']) && isset($_SESSION['uid'])) {
    $targetUserId = intval($_POST['targetUserId']);
    // Sử dụng removeRelation thay vì unlikeUser
    // Vì đây là người ĐƯỢC THÍCH bấm bỏ thích người đã thích mình
    $result = $timController->removeRelation($_SESSION['uid'], $targetUserId);
    $_SESSION['likeResult'] = $result;

    // Redirect để tránh resubmit
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// XỬ LÝ TỪ CHỐI (Không quan tâm)
if (isset($_POST['rejectUser']) && isset($_SESSION['uid'])) {
    $targetUserId = intval($_POST['targetUserId']);
    // Có thể thêm logic từ chối vào database nếu cần
    $_SESSION['likeResult'] = ['success' => true, 'message' => 'Đã từ chối!'];

    // Redirect để tránh resubmit
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Hiển thị kết quả nếu có
if (isset($_SESSION['likeResult'])) {
    $result = $_SESSION['likeResult'];
    $alertType = $result['success'] ? 'success' : 'danger';
    echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 300px;">';
    echo htmlspecialchars($result['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['likeResult']);
}

// Lấy danh sách người đã thích mình
$people_liked_me = $timController->GetAllUserLike($_SESSION['uid']);
$totalLikes = $people_liked_me ? $people_liked_me->num_rows : 0;
?>

<!-- Form hidden để thích lại -->
<form id="likeBackForm" method="POST" style="display: none;">
    <input type="hidden" name="targetUserId" id="targetUserIdLikeBack">
    <input type="hidden" name="likeBackUser" value="1">
</form>

<!-- Form hidden để bỏ thích -->
<form id="removeForm" method="POST" style="display: none;">
    <input type="hidden" name="targetUserId" id="targetUserIdRemove">
    <input type="hidden" name="removeUser" value="1">
</form>

<!-- Form hidden để từ chối -->
<form id="rejectForm" method="POST" style="display: none;">
    <input type="hidden" name="targetUserId" id="targetUserIdReject">
    <input type="hidden" name="rejectUser" value="1">
</form>

<!-- Header -->
<div class="likes-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="bi bi-heart-fill text-danger"></i> Người đã thích bạn</h1>
                <p class="mb-0">Những người đã bày tỏ sự quan tâm đến bạn</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="stats-badge d-inline-block">
                    <h3><?= $totalLikes ?></h3>
                    <small>Người thích bạn</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container pb-5">
    <?php if ($people_liked_me && $people_liked_me->num_rows > 0): ?>
        <div class="row">
            <?php while ($person = $people_liked_me->fetch_assoc()):
                // Tính tuổi
                $birthDate = new DateTime($person['ngaySinh']);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;

                // Kiểm tra xem mình đã thích lại người này chưa
                $alreadyLikedBack = $timController->checkLiked($_SESSION['uid'], $person['maNguoiDung']);
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
                                    <?php if ($alreadyLikedBack): ?>
                                        <span class="status-badge matched">
                                            💑 Đã ghép đôi
                                        </span>
                                    <?php endif; ?>
                                </h5>

                                <div class="person-detail">
                                    <i class="bi bi-gender-<?= strtolower($person['gioiTinh']) == 'nam' ? 'male' : 'female' ?>"></i>
                                    <?= htmlspecialchars($person['gioiTinh']) ?>
                                </div>

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

                                <div class="d-flex gap-2">
                                    <?php if (!$alreadyLikedBack): ?>
                                        <!-- Chưa thích lại - hiển thị nút bỏ thích và thích lại -->
                                        <button onclick="removeUser(<?= $person['maNguoiDung'] ?>)"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Bỏ thích">
                                            <i class="bi bi-x-lg"></i> Bỏ thích
                                        </button>
                                        <button onclick="likeBackUser(<?= $person['maNguoiDung'] ?>)"
                                            class="btn btn-sm"
                                            style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none;">
                                            <i class="bi bi-heart-fill"></i> Thích lại
                                        </button>
                                    <?php else: ?>
                                        <!-- Đã thích lại - chỉ hiển thị nút bỏ thích -->
                                        <button onclick="removeUser(<?= $person['maNguoiDung'] ?>)"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Bỏ thích">
                                            <i class="bi bi-x-lg"></i> Bỏ thích
                                        </button>
                                        <button class="btn btn-sm" disabled
                                            style="background: #48bb78; color: white; border: none;">
                                            <i class="bi bi-check2-circle"></i> Đã thích lại
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">💔</div>
            <h3>Chưa có ai thích bạn</h3>
            <p class="text-muted">Hãy hoàn thiện hồ sơ của bạn để thu hút nhiều người hơn!</p>
            <a href="home.php?page=me" class="btn btn-profile mt-3">
                <i class="bi bi-person-fill"></i> Chỉnh sửa hồ sơ
            </a>
        </div>
    <?php endif; ?>
</div>
<script>
    function likeBackUser(userId) {
        if (confirm('Bạn có muốn thích lại người này?')) {
            document.getElementById('targetUserIdLikeBack').value = userId;
            document.getElementById('likeBackForm').submit();
        }
    }

    function removeUser(userId) {
        if (confirm('Bạn có chắc muốn bỏ thích người này?')) {
            document.getElementById('targetUserIdRemove').value = userId;
            document.getElementById('removeForm').submit();
        }
    }

    function rejectUser(userId) {
        if (confirm('Bạn có chắc muốn từ chối người này?')) {
            document.getElementById('targetUserIdReject').value = userId;
            document.getElementById('rejectForm').submit();
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