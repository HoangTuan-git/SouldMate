<?php
if(!isset($_SESSION['uid'])){
    echo '<div class="card">';
    echo '<h3>Vui lòng đăng nhập</h3>';
    echo '<p><a href="home.php?page=dangnhap" class="btn-primary">Đăng nhập</a> hoặc <a href="home.php?page=dangky" class="btn-primary">Đăng ký</a></p>';
    echo '</div>';
} else {
    include_once("controller/cNguoiDung.php");
    $p = new controlNguoiDung();
    
    // Check if viewing another user's profile
    $profileUserId = isset($_REQUEST['uid']) ? (string)$_REQUEST['uid'] : $_SESSION['uid'];
    $isOwnProfile = $profileUserId === $_SESSION['uid'];
    
    $user = $p->getUser($profileUserId);
    if ($user && $user->num_rows > 0) {
        $userData = $user->fetch_assoc();
        ?>
        <div class="profile-container">
            <div class="profile-header">
                <?php if (!$isOwnProfile): ?>
                <a href="home.php" class="back-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px;">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                    Quay lại
                </a>
                <?php endif; ?>
                
                <div class="profile-avatar">
                    <div class="avatar-circle-large">
                        <?= strtoupper(substr($userData['email'], 0, 1)) ?>
                    </div>
                    <div class="online-indicator" style="display:block; width:20px; height:20px; border-radius:50%;" data-user-id="<?= $profileUserId ?>"></div>
                </div>
                
                <div class="profile-info">
                    <h2><?= htmlspecialchars($userData['email']) ?></h2>
                    <?php if (!$isOwnProfile): ?>
                    <div class="profile-actions">
                        <a href="home.php?page=tinnhan&uid=<?= $profileUserId ?>" class="btn-primary">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;margin-right:6px;">
                                <path d="M4 4h16v12H7l-3 3V4z"/>
                            </svg>
                            Nhắn tin
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-details">
                <div class="card">
                    <h3><?= $isOwnProfile ? 'Thông tin của bạn' : 'Thông tin cá nhân' ?></h3>
                    <div class="profile-field">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($userData['email']) ?></span>
                    </div>
                    <div class="profile-field">
                        <label>Tham gia:</label>
                        <span><?= date('d/m/Y', strtotime($userData['created_at'] ?? 'now')) ?></span>
                    </div>
                    <?php if ($isOwnProfile): ?>
                    <div class="profile-field">
                        <label>User ID:</label>
                        <span><?= $userData['uid'] ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($isOwnProfile): ?>
                <div class="card">
                    <h3>Cài đặt tài khoản</h3>
                    <div class="settings-options">
                        <a href="home.php?page=dangxuat" class="option-item">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;">
                                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                            </svg>
                            <span>Đăng xuất</span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        
        <?php
    } else {
        echo '<div class="card">';
        echo '<h3>Không tìm thấy người dùng</h3>';
        echo '<p><a href="home.php?page=me" class="btn-primary">Quay lại</a></p>';
        echo '</div>';
    }
}
?> 