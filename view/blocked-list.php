<?php
// Kiểm tra đăng nhập
if (!isset($_SESSION['uid'])) {
    header("Location: home.php?page=dangnhap");
    exit();
}

include_once("controller/cQuanHeNguoiDung.php");
include_once("controller/cHoSo.php");

$controller = new controlQuanHeNguoiDung();
$hoSoController = new controlHoSo();
$currentUserId = $_SESSION['uid'];

// Xử lý bỏ chặn
if (isset($_POST['unblock_uid'])) {
    $unblockUserId = $_POST['unblock_uid'];
    if ($controller->unblockUser($currentUserId, $unblockUserId)) {
        echo "<script>alert('Đã bỏ chặn người dùng thành công');</script>";
        echo "<script>window.location.href='home.php?page=blocked-list';</script>";
        exit();
    } else {
        echo "<script>alert('Có lỗi xảy ra khi bỏ chặn');</script>";
    }
}

// Lấy danh sách người bị chặn
$blockedUsers = $controller->getBlockedUsers($currentUserId);
?>

<div class="blocked-list-container mt-2">
    <div class="blocked-list-header">
        <a href="home.php?page=me" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4>Danh sách người bị chặn</h4>
    </div>

    <div class="blocked-list-content">
        <?php if ($blockedUsers && $blockedUsers->num_rows > 0): ?>
            <?php while ($user = $blockedUsers->fetch_assoc()): ?>
                <?php
                // Lấy thông tin profile của người bị chặn
                $profile = $hoSoController->getProfile($user['maNguoiDung']);
                $profileData = $profile ? $profile->fetch_assoc() : null;
                ?>
                <div class="blocked-user-item">
                    <div class="blocked-user-info">
                        <img src="uploads/avatars/<?= htmlspecialchars($profileData['avatar'] ?? 'default.png') ?>" 
                             alt="Avatar" 
                             class="blocked-user-avatar">
                        <div class="blocked-user-details">
                            <h6><?= htmlspecialchars($profileData['hoTen'] ?? 'Người dùng') ?></h6>
                            <small class="text-muted">
                                Chặn từ: <?= date('d/m/Y H:i', strtotime($user['ngayTao'] ?? 'now')) ?>
                            </small>
                        </div>
                    </div>
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Bạn có chắc muốn bỏ chặn người dùng này?');">
                        <input type="hidden" name="unblock_uid" value="<?= $user['maNguoiDung'] ?>">
                        <button type="submit" class="btn-unblock">
                            <i class="bi bi-unlock"></i> Bỏ chặn
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-blocked-list">
                <i class="bi bi-person-slash" style="font-size: 48px; color: #cbd5e1;"></i>
                <h5>Chưa chặn ai</h5>
                <p class="text-muted">Danh sách người bị chặn của bạn trống</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.blocked-list-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.blocked-list-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e5e7eb;
}

.back-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f3f4f6;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
}

.back-btn:hover {
    background: #e5e7eb;
    color: #111827;
}

.blocked-list-header h4 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #111827;
}

.blocked-list-content {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.blocked-user-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}

.blocked-user-item:last-child {
    border-bottom: none;
}

.blocked-user-item:hover {
    background: #f9fafb;
}

.blocked-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.blocked-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.blocked-user-details h6 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.blocked-user-details small {
    font-size: 13px;
}

.btn-unblock {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-unblock:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

.empty-blocked-list {
    text-align: center;
    padding: 60px 20px;
}

.empty-blocked-list h5 {
    margin: 16px 0 8px;
    font-size: 18px;
    font-weight: 600;
    color: #374151;
}

.empty-blocked-list p {
    margin: 0;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .blocked-list-container {
        padding: 16px;
    }

    .blocked-list-header h4 {
        font-size: 20px;
    }

    .blocked-user-item {
        padding: 12px 16px;
    }

    .blocked-user-avatar {
        width: 40px;
        height: 40px;
    }

    .btn-unblock {
        padding: 6px 12px;
        font-size: 13px;
    }
}
</style>
