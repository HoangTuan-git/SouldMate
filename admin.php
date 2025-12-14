<?php
session_start();
// Kiểm tra quyền admin
if (!isset($_SESSION['uid']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: home_test.php?page=dangnhap');
    exit();
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="view/assets/css/admin-style.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <nav class="nav flex-column">
                <a class="nav-link" href="admin.php">
                    <i class="bi bi-list-task"></i>
                    Quản lý vi phạm
                </a>
                <a class="nav-link" aria-current="page" href="admin.php?page=taiKhoanBiKhoa">
                    <i class="bi bi-unlock"></i>
                    Danh sách tài khoản bị khóa
                </a>
                <a class="nav-link" href="admin.php?page=danhSachBaoCao">
                    <i class="bi bi-flag"></i>
                    Danh sách báo cáo
                </a>
            </nav>
            <div class="logout-btn mt-auto">
                <a href="admin.php?page=dangxuat" class="btn btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </div>
        </aside>
        <main class="main-content">
            
            <?php
                $page = isset($_GET['page']) ? $_GET['page'] : '';
                switch ($page) {
                    case 'taiKhoanBiKhoa':
                        include_once('view/taiKhoanBiKhoa.php');
                        break;
                    case 'danhSachBaoCao':
                        include_once('view/danhSachBaoCao.php');
                        break;
                    case 'dangxuat':
                        include_once('view/dangxuat.php');
                        break;
                    default:
                        include_once('view/quanLyViPham.php');
                }
            ?>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>