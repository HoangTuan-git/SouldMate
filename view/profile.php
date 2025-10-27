<?php
if(!isset($_SESSION['uid'])){
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
?>

<div class="container-fluid px-2 mt-2">

    <!-- Profile Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="uploads/avatars/<?= htmlspecialchars($userData['avatar']) ?>" alt="Avatar" class="avatar-xlarge">
                        <div class="online-indicator" data-user-id="<?= $profileUserId ?>">
                        </div>
                        
                    </div>
                    <h4 class="card-title mb-1"><?= htmlspecialchars($userData['hoTen']) ?></h4>
                    <small class="text-muted"><?= htmlspecialchars($userData['moTa']) ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-6">
            <a href="home.php?page=tinnhan&uid=<?= $profileUserId ?>" class="btn btn-primary w-100">
                💬 Nhắn tin
            </a>
        </div>
        <div class="col-6">
            <div class="dropdown w-100">
                <button class="btn btn-outline-secondary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    ⋯ Khác
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="viewFullProfile('<?= $profileUserId ?>')">👤 Xem đầy đủ</a></li>
                    <li><a class="dropdown-item text-warning" href="#" onclick="blockUser('<?= $profileUserId ?>')">🚫 Chặn người dùng</a></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="reportUser('<?= $profileUserId ?>')">⚠️ Báo cáo</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Profile Info Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Thông tin cá nhân</h6>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Giới tính:</span>
                        <span class="info-value"><?= htmlspecialchars($userData['gioiTinh']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tham gia:</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($userData['created_at'] ?? 'now')) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Trạng thái:</span>
                        <span class="info-value">
                            <span class="status" data-user-id="<?= $profileUserId ?>">
                                <span class="badge bg-secondary">Đang kiểm tra...</span>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity (placeholder) -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Hoạt động gần đây</h6>
                </div>
                <div class="card-body text-center py-4">
                    <div class="text-muted">
                        <span style="font-size: 48px; opacity: 0.3;">📊</span>
                        <p class="mt-2 mb-0">Thông tin hoạt động sẽ được cập nhật sau</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
// Initialize user status when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (window.chatClient) {
        // Update status for this specific user
        const userId = '<?= $profileUserId ?>';
        updateUserProfileStatus(userId);
        
        // Listen for status updates
        if (window.chatClient.socket) {
            window.chatClient.socket.on('user_status_update', function(data) {
                if (data.userId === userId) {
                    updateUserProfileStatus(userId, data.status);
                }
            });
        }
    }
});


function viewFullProfile(userId) {
    alert('Chức năng xem profile đầy đủ đang được phát triển');
}

function blockUser(userId) {
    if (!confirm('Bạn có chắc chắn muốn chặn người dùng này?')) return;
    
    fetch('api/block-user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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
        headers: { 'Content-Type': 'application/json' },
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
