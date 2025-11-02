<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý vi phạm</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <nav class="nav flex-column">
                <a class="nav-link active" aria-current="page" href="quanLyViPham.html">
                    <i class="bi bi-list-task"></i>
                    Quản lý vi phạm
                </a>
                <a class="nav-link" href="moKhoaTaiKhoan.php">
                    <i class="bi bi-unlock"></i>
                    Mở khóa tài khoản
                </a>
            </nav>
            <div class="logout-btn mt-auto">
                <a href="dangxuat.php" class="btn btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </div>
            <div class="watermark text-center mt-3">
                 <span>Made with <span class="logo-v">V</span></span>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="h3 fw-bold mb-1">Danh sách tài khoản vi phạm (>15 báo cáo)</h1>
            <p class="text-muted mb-4">Những tài khoản có hơn 15 báo cáo</p>

            <div class="search-bar position-relative mb-4">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Tìm kiếm theo ID hoặc Tên người dùng...">
            </div>

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
                        <tr>
                            <td><strong>MS001</strong></td>
                            <td>Nguyễn Văn An</td>
                            <td><span class="badge text-bg-danger rounded-pill">22 báo cáo</span></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Xem chi tiết</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MS002</strong></td>
                            <td>Trần Thị Bình</td>
                            <td><span class="badge text-bg-danger rounded-pill">18 báo cáo</span></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Xem chi tiết</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MS003</strong></td>
                            <td>Lê Hoàng Cường</td>
                            <td><span class="badge text-bg-danger rounded-pill">30 báo cáo</span></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Xem chi tiết</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MS004</strong></td>
                            <td>Đặng Thanh Duyên</td>
                            <td><span class="badge text-bg-danger rounded-pill">16 báo cáo</span></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Xem chi tiết</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MS005</strong></td>
                            <td>Hoàng Minh Đức</td>
                            <td><span class="badge text-bg-danger rounded-pill">25 báo cáo</span></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Xem chi tiết</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>