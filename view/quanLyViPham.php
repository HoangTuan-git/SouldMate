<?php
require_once(dirname(__DIR__) . '/controller/cAdmin.php');
$adminController = new controlAdmin();

// Xử lý tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$violations = $keyword ? $adminController->timKiemViPham($keyword) : $adminController->getDanhSachViPham();

// Xử lý khóa tài khoản
if (isset($_POST['lockAccount'])) {
    $maNguoiDung = $_POST['maNguoiDung'];
    $lyDo = "Vi phạm nhiều lần (>15 báo cáo)";
    $result = $adminController->khoaTaiKhoan($maNguoiDung, $lyDo);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}
?>
<h1 class="h3 fw-bold mb-1">Quản lý vi phạm</h1>
<p class="text-muted mb-4">Danh sách tài khoản có từ 15 báo cáo trở lên</p>

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
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
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
        window.location.href = `chiTietViPham.php?uid=${maNguoiDung}`;
    }
</script>

