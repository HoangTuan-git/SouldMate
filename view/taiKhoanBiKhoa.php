<?php

require_once(dirname(__DIR__) . '/controller/cAdmin.php');
$adminController = new controlAdmin();

// Tự động mở khóa tài khoản hết hạn
$adminController->autoUnlockExpiredAccounts();

// Xử lý tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$lockedAccounts = $keyword ? $adminController->timKiemTaiKhoanKhoa($keyword) : $adminController->getDanhSachTaiKhoanKhoa();

// Xử lý mở khóa tài khoản
if (isset($_POST['unlockAccount'])) {
    $maNguoiDung = $_POST['maNguoiDung'];
    $result = $adminController->moKhoaTaiKhoan($maNguoiDung);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}
?>

<h1 class="h3 fw-bold mb-4">Quản lý tài khoản bị khóa</h1>

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
                <th scope="col">ID Tài Khoản</th>
                <th scope="col">Tên Người Dùng</th>
                <th scope="col">Ngày Khóa</th>
                <th scope="col">Thời Hạn Mở Khóa</th>
                <th scope="col">Lý Do Khóa</th>
                <th scope="col" class="text-end">Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($lockedAccounts && $lockedAccounts->num_rows > 0): ?>
                <?php while ($row = $lockedAccounts->fetch_assoc()): ?>
                    <tr>
                        <td><strong>MSU<?= str_pad($row['maNguoiDung'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= htmlspecialchars($row['hoTen'] ?? 'Chưa cập nhật') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['ngayBiKhoa'])) ?></td>
                        <td>
                            <?php if ($row['ngayMoKhoa']): ?>
                                <?php 
                                $ngayMoKhoa = strtotime($row['ngayMoKhoa']);
                                $now = time();
                                $isExpired = $ngayMoKhoa <= $now;
                                ?>
                                <span class="<?= $isExpired ? 'text-success' : 'text-warning' ?> fw-bold">
                                    <?= date('d/m/Y H:i', $ngayMoKhoa) ?>
                                </span>
                                <?php if (!$isExpired): ?>
                                    <?php 
                                    $diff = $ngayMoKhoa - $now;
                                    $days = floor($diff / 86400);
                                    $hours = floor(($diff % 86400) / 3600);
                                    ?>
                                    <br><small class="text-muted">Còn <?= $days ?> ngày <?= $hours ?> giờ</small>
                                <?php else: ?>
                                    <br><small class="text-success"><i class="bi bi-check-circle"></i> Đã hết hạn</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-danger">Vĩnh viễn</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['lyDoKhoa']) ?></td>
                        <td class="text-end">
                            <form method="POST" style="display:inline;" 
                                    onsubmit="return confirm('Bạn có chắc muốn mở khóa tài khoản này?');">
                                <input type="hidden" name="maNguoiDung" value="<?= $row['maNguoiDung'] ?>">
                                <button type="submit" name="unlockAccount" class="btn btn-success btn-sm">
                                    <i class="bi bi-unlock-fill"></i> Mở khóa
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-unlock" style="font-size: 3rem;"></i>
                        <p class="mt-2">Không có tài khoản bị khóa</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>