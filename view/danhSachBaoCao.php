<?php

include_once(dirname(__DIR__) . '/controller/cAdmin.php');
$adminController = new controlAdmin();

// Lấy filter từ URL
$loaiBaoCao = isset($_GET['loai']) ? $_GET['loai'] : null;
$trangThai = isset($_GET['trangthai']) ? $_GET['trangthai'] : 'dangxuly';

// Xử lý cập nhật trạng thái
if (isset($_POST['updateStatus'])) {
    $maBaoCao = intval($_POST['maBaoCao']);
    $newStatus = $_POST['newStatus'];
    
    $result = $adminController->updateReportStatus($maBaoCao, $newStatus);
    if ($result) {
        $message = 'Cập nhật trạng thái thành công!';
        $messageType = 'success';
    } else {
        $message = 'Cập nhật thất bại!';
        $messageType = 'danger';
    }
}

// Lấy danh sách báo cáo
$reports = $adminController->getAllReports($loaiBaoCao, $trangThai);

// Kiểm tra success message từ session
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $messageType = 'success';
    unset($_SESSION['success_message']);
}
?>


<h1 class="h3 fw-bold mb-4">
    <i class="bi bi-flag"></i> Danh sách báo cáo vi phạm
</h1>

<?php if (isset($message)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Bộ lọc -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Loại báo cáo</label>
                <select name="loai" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="nguoidung" <?= $loaiBaoCao == 'nguoidung' ? 'selected' : '' ?>>Người dùng</option>
                    <option value="baidang" <?= $loaiBaoCao == 'baidang' ? 'selected' : '' ?>>Bài đăng</option>
                    <option value="tinnhan" <?= $loaiBaoCao == 'tinnhan' ? 'selected' : '' ?>>Tin nhắn</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="dangxuly" <?= $trangThai == 'dangxuly' ? 'selected' : '' ?>>Đang xử lý</option>
                    <option value="daxuly" <?= $trangThai == 'daxuly' ? 'selected' : '' ?>>Đã xử lý</option>
                    <option value="tuchoi" <?= $trangThai == 'tuchoi' ? 'selected' : '' ?>>Từ chối</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bảng danh sách báo cáo -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách báo cáo</h5>
        <span class="badge bg-primary">
            <?= $reports ? $reports->num_rows : 0 ?> báo cáo
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Loại</th>
                        <th>Người báo cáo</th>
                        <th>Người bị báo cáo</th>
                        <th>Lý do</th>
                        <th>Ngày báo cáo</th>
                        <th>Trạng thái</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports && $reports->num_rows > 0): ?>
                        <?php while ($report = $reports->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $report['maBaoCao'] ?></strong></td>
                                <td>
                                    <?php
                                    $loaiIcon = [
                                        'nguoidung' => '<i class="bi bi-person-fill text-danger"></i> Người dùng',
                                        'baidang' => '<i class="bi bi-file-text text-info"></i> Bài đăng',
                                        'tinnhan' => '<i class="bi bi-chat-fill text-warning"></i> Tin nhắn'
                                    ];
                                    echo $loaiIcon[$report['loaiBaoCao']] ?? $report['loaiBaoCao'];
                                    ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($report['tenNguoiBaoCao'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($report['tenNguoiBiBaoCao'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($report['lyDo']) ?></small>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($report['thoiGianBaoCao'])) ?></small>
                                </td>
                                <td>
                                    <?php
                                    $statusBadge = [
                                        'dangxuly' => '<span class="badge bg-warning text-dark">Đang xử lý</span>',
                                        'daxuly' => '<span class="badge bg-success">Đã xử lý</span>',
                                        'tuchoi' => '<span class="badge bg-secondary">Từ chối</span>'
                                    ];
                                    echo $statusBadge[$report['trangThai']] ?? $report['trangThai'];
                                    ?>
                                </td>
                                <td>
                                    <?php if ($report['loaiBaoCao'] == 'nguoidung'): ?>
                                        <!-- Link đến profile người dùng bị báo cáo -->
                                        <a href="home.php?page=profile&uid=<?= $report['maNguoiDungBiBaoCao'] ?>" 
                                            class="btn btn-sm btn-outline-primary" 
                                            target="_blank"
                                            title="Xem profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    <?php elseif ($report['loaiBaoCao'] == 'baidang'): ?>
                                        <!-- Link đến trang xem bài viết -->
                                        <?php if (!empty($report['maBaiDang'])): ?>
                                            <a href="home.php?page=viewpost&postId=<?= $report['maBaiDang'] ?>" 
                                                class="btn btn-sm btn-outline-info" 
                                                target="_blank"
                                                title="Xem chi tiết bài viết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">N/A</span>
                                        <?php endif; ?>
                                    <?php elseif ($report['loaiBaoCao'] == 'tinnhan'): ?>
                                        <!-- Modal hiển thị context tin nhắn -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#messageModal<?= $report['maBaoCao'] ?>"
                                                title="Xem context">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Modal for message context -->
                            <?php if ($report['loaiBaoCao'] == 'tinnhan'): ?>
                                <div class="modal fade" id="messageModal<?= $report['maBaoCao'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-chat-text"></i> Context tin nhắn - Báo cáo #<?= $report['maBaoCao'] ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if (!empty($report['contextTinNhan'])): ?>
                                                    <?php 
                                                    $contextMessages = json_decode($report['contextTinNhan'], true);
                                                    $violatedMessageId = $report['maTinNhan'] ?? null;
                                                    ?>
                                                    <?php if ($contextMessages && is_array($contextMessages)): ?>
                                                        <div class="alert alert-info mb-3">
                                                            <i class="bi bi-info-circle"></i> 
                                                            Hiển thị ngữ cảnh 10 tin nhắn trước và sau tin nhắn bị báo cáo
                                                        </div>
                                                        <div class="chat-context">
                                                            <?php foreach ($contextMessages as $msg): ?>
                                                                <div class="message-item mb-3 p-3 border rounded <?= ($msg['maTinNhan'] == $violatedMessageId) ? 'border-danger bg-danger bg-opacity-10' : 'border-light' ?>">
                                                                    <div class="d-flex justify-content-between mb-2">
                                                                        <strong class="text-primary">
                                                                            <?= htmlspecialchars($msg['tenNguoiGui'] ?? 'User ' . $msg['maNguoiGui']) ?>
                                                                        </strong>
                                                                        <small class="text-muted">
                                                                            <?= date('d/m/Y H:i', strtotime($msg['thoiGian'])) ?>
                                                                        </small>
                                                                    </div>
                                                                    <div class="message-content">
                                                                        <?= nl2br(htmlspecialchars($msg['noiDungText'])) ?>
                                                                    </div>
                                                                    <?php if ($msg['maTinNhan'] == $violatedMessageId): ?>
                                                                        <div class="mt-2">
                                                                            <span class="badge bg-danger">
                                                                                <i class="bi bi-exclamation-triangle"></i> Tin nhắn bị báo cáo
                                                                            </span>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-muted">Không thể hiển thị context tin nhắn</p>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle"></i> 
                                                        Không có context tin nhắn được lưu
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Không có báo cáo nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>