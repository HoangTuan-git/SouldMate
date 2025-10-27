<?php
if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Bạn cần đăng nhập để xem danh sách này.')</script>";
    header("refresh:0;url=home.php?page=dangnhap");
    exit;
}

include_once("controller/cxuly.php");
$p = new Cxuly();
$people_who_liked_me = $p->GetAllUserLike();
?>

<div class="likes-container container">

    <?php if ($people_who_liked_me && $people_who_liked_me->num_rows > 0): ?>
        <div class="people-list">
            <?php while ($person = $people_who_liked_me->fetch_assoc()): ?>
                <div class="person-item">
                    <?php
                    $src = "img/" . $person['avatar'];
                    ?>
                    <img src="<?= htmlspecialchars($src ?? 'img/default.png') ?>"
                        alt="Avatar" class="avatar">

                    <div class="person-info">
                        <h3 class="person-name"><?= htmlspecialchars($person['name']) ?></h3>
                        <p class="person-age"><?= htmlspecialchars($person['age']) ?> tuổi</p>
                    </div>

                    <div class="actions">
                        <button class="btn btn-skip" title="Bỏ qua">
                            ✕
                        </button>
                        <button class="btn btn-like" title="Thích lại">
                            ♥
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <div class="empty-icon">💔</div>
            <h3>Chưa có ai có tâm hồn đồng điệu với bạn</h3>
            <p>Hãy cập nhật profile để thu hút nhiều người hơn nhé!</p>
        </div>
    <?php endif; ?>
</div>