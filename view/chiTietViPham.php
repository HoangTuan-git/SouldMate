<?php

// Lấy mã người dùng từ URL
$maNguoiDung = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($maNguoiDung <= 0) {
    header('Location: quanLyViPham.php');
    exit();
}

include_once(dirname(__DIR__) . '/controller/cAdmin.php');
include_once(dirname(__DIR__) . '/model/mBaoCaoViPham.php');

$adminController = new controlAdmin();
$model = new modelBaoCaoViPham();

// Lấy thông tin người dùng
$userInfo = $model->getUserInfo($maNguoiDung);
if (!$userInfo) {
    header('Location: quanLyViPham.php');
    exit();
}

// Lấy chi tiết báo cáo
$reports = $adminController->getChiTietBaoCao($maNguoiDung);
$reportStats = $model->getReportStatsByUser($maNguoiDung);
$violationHistory = $model->getViolationHistory($maNguoiDung);

// Xử lý khóa tài khoản
if (isset($_POST['lockAccount'])) {
    $lyDo = "Vi phạm nhiều lần (>15 báo cáo)";
    $result = $adminController->khoaTaiKhoan($maNguoiDung, $lyDo);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        // Refresh thông tin người dùng
        $userInfo = $model->getUserInfo($maNguoiDung);
    }
}
?>
<div class="d-flex align-items-center mb-4">
    <a href="quanLyViPham.php" class="btn btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
    <h1 class="h3 fw-bold mb-0">Chi tiết vi phạm</h1>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Thông tin người dùng -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="card-title mb-3">Thông tin người dùng</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Mã người dùng:</strong> MSU<?= str_pad($userInfo['maNguoiDung'], 3, '0', STR_PAD_LEFT) ?></p>
                        <p class="mb-2"><strong>Tên:</strong> <?= htmlspecialchars($userInfo['hoTen'] ?? 'Chưa cập nhật') ?></p>
                        <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($userInfo['email'] ?? 'Chưa cập nhật') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Trạng thái:</strong> 
                            <?php if ($userInfo['trangThaiViPham'] == 'khoa'): ?>
                                <span class="badge bg-danger">Đã khóa</span>
                            <?php else: ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php endif; ?>
                        </p>
                        <?php if ($userInfo['trangThaiViPham'] == 'khoa'): ?>
                            <p class="mb-2"><strong>Ngày khóa:</strong> <?= date('d/m/Y H:i', strtotime($userInfo['ngayBiKhoa'])) ?></p>
                            <p class="mb-2"><strong>Lý do:</strong> <?= htmlspecialchars($userInfo['lyDoKhoa']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($userInfo['trangThaiViPham'] != 'khoa'): ?>
                    <form method="POST" onsubmit="return confirm('Bạn có chắc muốn khóa tài khoản này?');">
                        <button type="submit" name="lockAccount" class="btn btn-danger">
                            <i class="bi bi-lock"></i> Khóa tài khoản
                        </button>
                    </form>
                <?php else: ?>
                    <a href="moKhoaTaiKhoan.php" class="btn btn-success">
                        <i class="bi bi-unlock"></i> Đi tới trang mở khóa
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Thống kê báo cáo -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= $reportStats['tongBaoCao'] ?? 0 ?></h3>
                <p class="text-muted mb-0">Tổng số báo cáo</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-person-x text-danger" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= $reportStats['baoCaoNguoiDung'] ?? 0 ?></h3>
                <p class="text-muted mb-0">Báo cáo người dùng</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-file-text text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= $reportStats['baoCaoBaiDang'] ?? 0 ?></h3>
                <p class="text-muted mb-0">Báo cáo bài đăng</p>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách báo cáo -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Lịch sử báo cáo</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mã báo cáo</th>
                        <th>Loại</th>
                        <th>Người báo cáo</th>
                        <th>Lý do</th>
                        <th>Nội dung vi phạm</th>
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
                                <td><?= htmlspecialchars($report['tenNguoiBaoCao'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($report['lyDo']) ?></td>
                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(mb_substr($report['noiDungViPham'], 0, 50)) ?>
                                        <?= mb_strlen($report['noiDungViPham']) > 50 ? '...' : '' ?>
                                    </small>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($report['ngayBaoCao'])) ?></td>
                                <td>
                                    <?php
                                    $statusBadge = [
                                        'dangxuly' => '<span class="badge bg-warning">Đang xử lý</span>',
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
                                            <i class="bi bi-eye"></i> Xem chi tiết
                                        </a>
                                    <?php elseif ($report['loaiBaoCao'] == 'baidang'): ?>
                                        <!-- Link đến trang xem bài viết -->
                                        <?php if (!empty($report['maBaiDang'])): ?>
                                            <a href="home.php?page=viewpost&postId=<?= $report['maBaiDang'] ?>" 
                                                class="btn btn-sm btn-outline-info" 
                                                target="_blank"
                                                title="Xem chi tiết bài viết">
                                                <i class="bi bi-eye"></i> Xem chi tiết
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Không có</span>
                                        <?php endif; ?>
                                    <?php elseif ($report['loaiBaoCao'] == 'tinnhan'): ?>
                                        <!-- Modal hiển thị context tin nhắn -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#messageModal<?= $report['maBaoCao'] ?>"
                                                title="Xem context tin nhắn">
                                            <i class="bi bi-eye"></i> Xem chi tiết
                                        </button>
                                        
                                        <!-- Modal -->
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
                                                                                <?= nl2br(htmlspecialchars($msg['noiDung'])) ?>
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
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Không có báo cáo nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Lịch sử vi phạm -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Lịch sử hành động</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mã lịch sử</th>
                        <th>Ngày thực hiện</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($violationHistory && $violationHistory->num_rows > 0): ?>
                        <?php while ($history = $violationHistory->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $history['maLichSu'] ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($history['ngayThucHien'])) ?></td>
                                <td>
                                    <?php 
                                    $hanhDong = $history['hanhDong'];
                                    if (strpos($hanhDong, 'Khóa') !== false) {
                                        echo '<span class="badge bg-danger me-2"><i class="bi bi-lock"></i></span>';
                                    } elseif (strpos($hanhDong, 'Mở khóa') !== false) {
                                        echo '<span class="badge bg-success me-2"><i class="bi bi-unlock"></i></span>';
                                    } else {
                                        echo '<span class="badge bg-warning me-2"><i class="bi bi-exclamation-triangle"></i></span>';
                                    }
                                    echo htmlspecialchars($hanhDong);
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">
                                Không có lịch sử hành động
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

