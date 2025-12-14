<?php
require_once(dirname(__DIR__) . '/controller/cAdmin.php');
$adminController = new controlAdmin();

// Xử lý tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$violations = $keyword ? $adminController->timKiemViPham($keyword) : $adminController->getDanhSachViPham();

// Xử lý khóa tài khoản 
if (isset($_POST['khoaTaiKhoan'])) {
    $maNguoiDung = $_POST['maNguoiDung'];
    $lyDo = $_POST['lyDo'] ?? "Vi phạm nhiều lần (>3 báo cáo)";
    $loaiKhoa = $_POST['loaiKhoa'] ?? 'vinhvien';
    $soNgayKhoa = ($loaiKhoa === 'thoihan') ? intval($_POST['soNgayKhoa']) : null;
    
    $result = $adminController->khoaTaiKhoan($maNguoiDung, $lyDo, $soNgayKhoa);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}

// Tự động mở khóa tài khoản hết hạn
$adminController->autoUnlockExpiredAccounts();
?>
<h1 class="h3 fw-bold mb-1">Quản lý vi phạm</h1>
<p class="text-muted mb-4">Danh sách tài khoản có từ 3 báo cáo trở lên</p>

<?php if (isset($message)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form class="mb-4" method="GET">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" name="keyword" class="form-control border-start-0" 
                placeholder="Tìm kiếm theo ID hoặc tên người dùng..." 
                value="<?= htmlspecialchars($keyword) ?>">
        <button class="btn btn-primary" type="submit">Tìm kiếm</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover bg-white border rounded">
        <thead>
            <tr>
                <th scope="col">ID Người dùng</th>
                <th scope="col">Tên</th>
                <th scope="col">Số lần báo cáo</th>
                <th scope="col">Trạng thái</th>
                <th scope="col">Chi tiết</th>
                <th scope="col">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($violations && $violations->num_rows > 0): ?>
                <?php while ($row = $violations->fetch_assoc()): ?>
                    <tr>
                        <td><strong>MS<?= str_pad($row['maNguoiDung'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= htmlspecialchars($row['hoTen'] ?? 'Chưa cập nhật') ?></td>
                        <td>
                            <span class="badge text-bg-danger rounded-pill">
                                <?= $row['soLanBaoCao'] ?> báo cáo
                            </span>
                        </td>
                        <td>
                            <?php if ($row['trangThaiViPham'] == 'khoa'): ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-lock-fill"></i> Đã khóa
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle-fill"></i> Hoạt động
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-info btn-sm" 
                                    onclick="viewDetails(<?= $row['maNguoiDung'] ?>)">
                                Xem chi tiết
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#lockModal<?= $row['maNguoiDung'] ?>">
                                <i class="bi bi-lock-fill"></i> Khóa tài khoản
                            </button>
                        </td>
                    </tr>

                    <!-- Modal khóa tài khoản -->
                    <div class="modal fade" id="lockModal<?= $row['maNguoiDung'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-lock-fill text-danger"></i> Khóa tài khoản
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="maNguoiDung" value="<?= $row['maNguoiDung'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Người dùng:</label>
                                            <p class="text-muted"><?= htmlspecialchars($row['hoTen'] ?? 'N/A') ?> (MS<?= str_pad($row['maNguoiDung'], 3, '0', STR_PAD_LEFT) ?>)</p>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Loại khóa: <span class="text-danger">*</span></label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="loaiKhoa" 
                                                       id="thoihan<?= $row['maNguoiDung'] ?>" value="thoihan" 
                                                       onchange="toggleSoNgay(<?= $row['maNguoiDung'] ?>, true)" checked>
                                                <label class="form-check-label" for="thoihan<?= $row['maNguoiDung'] ?>">
                                                    Khóa có thời hạn
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="loaiKhoa" 
                                                       id="vinhvien<?= $row['maNguoiDung'] ?>" value="vinhvien" 
                                                       onchange="toggleSoNgay(<?= $row['maNguoiDung'] ?>, false)">
                                                <label class="form-check-label" for="vinhvien<?= $row['maNguoiDung'] ?>">
                                                    Khóa vĩnh viễn
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3" id="soNgayContainer<?= $row['maNguoiDung'] ?>">
                                            <label class="form-label fw-bold">Số ngày khóa: <span class="text-danger">*</span></label>
                                            <select name="soNgayKhoa" class="form-select">
                                                <option value="3">3 ngày</option>
                                                <option value="7" selected>7 ngày (1 tuần)</option>
                                                <option value="14">14 ngày (2 tuần)</option>
                                                <option value="30">30 ngày (1 tháng)</option>
                                                <option value="90">90 ngày (3 tháng)</option>
                                                <option value="180">180 ngày (6 tháng)</option>
                                                <option value="365">365 ngày (1 năm)</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Lý do khóa: <span class="text-danger">*</span></label>
                                            <textarea name="lyDo" class="form-control" rows="3" 
                                                      placeholder="Nhập lý do khóa tài khoản..." required>Vi phạm nhiều lần (<?= $row['soLanBaoCao'] ?> báo cáo)</textarea>
                                        </div>

                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i> 
                                            <strong>Cảnh báo:</strong> Tài khoản sẽ bị khóa và người dùng không thể đăng nhập cho đến khi hết hạn hoặc được mở khóa thủ công.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" name="khoaTaiKhoan" class="btn btn-danger">
                                            <i class="bi bi-lock-fill"></i> Xác nhận khóa
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        Không có dữ liệu vi phạm
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function viewDetails(maNguoiDung) {
        // Redirect to details page or open modal
        window.location.href = `admin.php?page=danhSachBaoCao&uid=${maNguoiDung}`;
    }

    function toggleSoNgay(userId, show) {
        const container = document.getElementById('soNgayContainer' + userId);
        const select = container.querySelector('select');
        if (show) {
            container.style.display = 'block';
            select.required = true;
        } else {
            container.style.display = 'none';
            select.required = false;
        }
    }
</script>

