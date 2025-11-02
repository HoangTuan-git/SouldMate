<?php
session_start();
// Kiểm tra quyền admin
if (!isset($_SESSION['uid']) || $_SESSION['uid'] != 5) {
    header('Location: home_test.php?page=dangnhap');
    exit();
}

include_once('controller/cAdmin.php');
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
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý vi phạm - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <nav class="nav flex-column">
                <a class="nav-link active" aria-current="page" href="quanLyViPham.php">
                    <i class="bi bi-list-task"></i>
                    Quản lý vi phạm
                </a>
                <a class="nav-link" href="moKhoaTaiKhoan.php">
                    <i class="bi bi-unlock"></i>
                    Mở khóa tài khoản
                </a>
            </nav>
            <div class="logout-btn mt-auto">
                <a href="home.php?page=dangxuat" class="btn btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </div>
            <div class="watermark text-center mt-3">
                 <span>Made with <span class="logo-v">V</span></span>
            </div>
        </aside>

        <main class="main-content">
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
                            <th scope="col" class="text-end">Hành động</th>
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
                                    <td class="text-end">
                                        <button class="btn btn-outline-secondary btn-sm me-2" 
                                                onclick="viewDetails(<?= $row['maNguoiDung'] ?>)">
                                            Xem chi tiết
                                        </button>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('Bạn có chắc muốn khóa tài khoản này?');">
                                            <input type="hidden" name="maNguoiDung" value="<?= $row['maNguoiDung'] ?>">
                                            <button type="submit" name="lockAccount" class="btn btn-danger btn-sm">
                                                Khóa tài khoản
                                            </button>
                                        </form>
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
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewDetails(maNguoiDung) {
            // Redirect to details page or open modal
            window.location.href = `chiTietViPham.php?uid=${maNguoiDung}`;
        }
    </script>
</body>
</html>