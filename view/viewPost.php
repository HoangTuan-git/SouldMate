<?php

include_once(dirname(__DIR__) . '/controller/cAdmin.php');
include_once(dirname(__DIR__) . '/model/mBaoCaoViPham.php');

$controller = new controlAdmin();
$modelBaoCao = new modelBaoCaoViPham();

// Lấy mã bài viết từ URL
$maBaiDang = isset($_GET['postId']) ? intval($_GET['postId']) : 0;

if ($maBaiDang == 0) {
    header("Location: danhSachBaoCao.php");
    exit();
}

// Lấy thông tin bài viết từ DB
$post = $controller->getPostForAdmin($maBaiDang);

// Lấy snapshot từ báo cáo (nếu bài viết đã bị xóa)
$snapshot = null;
$isDeleted = false;

if (!$post) {
    $isDeleted = true;
    // Lấy snapshot từ báo cáo đầu tiên
    $reports = $modelBaoCao->getReportsByType('baidang', null);
    while ($row = $reports->fetch_assoc()) {
        if ($row['maBaiDang'] == $maBaiDang && !empty($row['snapshotBaiViet'])) {
            $snapshot = json_decode($row['snapshotBaiViet'], true);
            break;
        }
    }
    
    // Nếu không có snapshot, hiển thị thông báo
    if (!$snapshot) {
        $errorMessage = "Không tìm thấy bài viết và không có bản sao lưu.";
    }
}

// Xử lý xóa bài viết
if (isset($_POST['delete_post'])) {
    $lyDo = isset($_POST['lyDo']) ? trim($_POST['lyDo']) : 'Vi phạm nội dung';
    
    if ($controller->deleteViolatingPost($maBaiDang, $lyDo)) {
        $_SESSION['success_message'] = "Đã xóa bài viết thành công và cập nhật trạng thái tất cả báo cáo liên quan!";
        header("Location: admin.php?page=danhSachBaoCao");
        exit();
    } else {
        $errorMessage = "Có lỗi khi xóa bài viết!";
    }
}
?>
<div class="container post-container">
    <!-- Nút quay lại -->

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <?= $errorMessage ?>
        </div>
    <?php endif; ?>

    <?php if ($isDeleted && $snapshot): ?>
        <!-- Banner cảnh báo bài viết đã xóa -->
        <div class="deleted-banner">
            <i class="bi bi-exclamation-circle"></i>
            <strong>Bài viết đã bị xóa</strong> - Đây là bản sao lưu từ lúc báo cáo được tạo
            (<?= date('d/m/Y H:i', strtotime($snapshot['snapshotTime'])) ?>)
        </div>
    <?php endif; ?>

    <!-- Card hiển thị bài viết -->
    <div class="post-card">
        <?php if ($post || $snapshot): 
            $data = $post ?: $snapshot;
            $avatar ='uploads/avatars/' . ($data['avatar'] ?? '../img/default-avatar.png');
            $images = !empty($data['noiDungAnh']) ? explode(',', $data['noiDungAnh']) : [];
            $imageCount = count($images);
            $gridClass = $imageCount == 1 ? 'single' : ($imageCount == 2 ? 'double' : 'multiple');
        ?>
            <!-- Header -->
            <div class="post-header">
                <img src="<?= $avatar ?>" 
                        alt="Avatar" class="post-avatar">
                <div class="post-author-info">
                    <h5><?= htmlspecialchars($data['tenNguoiDang'] ?? 'Người dùng') ?></h5>
                    <div class="post-time">
                        <?php 
                        $time = $data['thoiGianDang'] ?? '';
                        if ($time) {
                            $diff = time() - strtotime($time);
                            if ($diff < 60) echo "Vừa xong";
                            elseif ($diff < 3600) echo floor($diff/60) . " phút trước";
                            elseif ($diff < 86400) echo floor($diff/3600) . " giờ trước";
                            else echo date('d/m/Y H:i', strtotime($time));
                        }
                        ?>
                        <?php if (isset($data['quyenRiengTu'])): ?>
                            • <i class="bi bi-<?= $data['quyenRiengTu'] == 'congthai' ? 'globe' : ($data['quyenRiengTu'] == 'banbe' ? 'people-fill' : 'lock-fill') ?>"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Nội dung -->
            <div class="post-content">
                <?= nl2br(htmlspecialchars($data['noiDungText'] ?? '')) ?>
            </div>

            <!-- Hình ảnh -->
            <?php if ($imageCount > 0): ?>
                <div class="post-images <?= $gridClass ?>">
                    <?php foreach ($images as $img): 
                        $imgPath = "img/" . trim($img);
                        if (!empty($imgPath)):
                    ?>
                        <img src="<?= $imgPath ?>" alt="Post image" class="post-image">
                    <?php 
                        endif;
                    endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Thông tin snapshot -->
            <?php if ($isDeleted && $snapshot): ?>
                <div class="snapshot-info">
                    <i class="bi bi-info-circle"></i>
                    Bản sao lưu này được tạo tự động khi báo cáo được gửi vào 
                    <?= date('d/m/Y lúc H:i', strtotime($snapshot['snapshotTime'])) ?>
                </div>
            <?php endif; ?>

            <!-- Action buttons -->
            <div class="action-buttons">
                <?php if (!$isDeleted): ?>
                    <!-- Nút xóa bài viết -->
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash"></i> Xóa bài viết
                    </button>
                <?php endif; ?>
                
                <a href="home.php?page=profile&uid=<?= $data['maNguoiDung'] ?>" class="btn btn-info" target="_blank">
                    <i class="bi bi-person"></i> Xem hồ sơ
                </a>
            </div>

        <?php endif; ?>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<?php if (!$isDeleted): ?>
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Bạn có chắc chắn muốn xóa bài viết này? Hành động này không thể hoàn tác!
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do xóa:</label>
                        <textarea name="lyDo" class="form-control" rows="3" required 
                                    placeholder="Nhập lý do xóa bài viết..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="delete_post" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Xác nhận xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

