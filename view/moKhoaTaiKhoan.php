<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mở khóa tài khoản</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <nav class="nav flex-column">
                <a class="nav-link" href="quanLyViPham.html">
                    <i class="bi bi-list-task"></i>
                    Quản lý vi phạm
                </a>
                <a class="nav-link active" aria-current="page" href="moKhoaTaiKhoan.html">
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
            <h1 class="h3 fw-bold mb-4">Quản lý tài khoản bị khóa</h1>

            <form class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0" placeholder="Tìm kiếm theo ID người dùng...">
                    <button class="btn btn-primary" type="button">Tìm kiếm</button>
                </div>
            </form>
            

            <div class="table-responsive">
                <table class="table table-hover bg-white border rounded">
                    <thead>
                        <tr>
                            <th scope="col">ID Tài Khoản</th>
                            <th scope="col">Tên Người Dùng</th>
                            <th scope="col">Ngày Khóa</th>
                            <th scope="col">Lý Do Khóa</th>
                            <th scope="col" class="text-end">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>MSU001</strong></td>
                            <td>Nguyễn Văn A</td>
                            <td>2023-10-26</td>
                            <td>Nội dung không phù hợp lặp lại</td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Mở khóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MSU002</strong></td>
                            <td>Trần Thị B</td>
                            <td>2023-11-15</td>
                            <td>Hành vi quấy rối</td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Mở khóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MSU003</strong></td>
                            <td>Lê Minh C</td>
                            <td>2024-01-03</td>
                            <td>Sử dụng tài khoản giả mạo</td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Mở khóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MSU004</strong></td>
                            <td>Phạm Thị D</td>
                            <td>2024-02-20</td>
                            <td>Vi phạm bản quyền hình ảnh</td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Mở khóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MSU005</strong></td>
                            <td>Hoàng Đức E</td>
                            <td>2024-03-01</td>
                            <td>Spam liên tục</td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Mở khóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MSU006</strong></td>
                            <td>Đặng Thị G</td>
                            <td>2024-03-10</td>
                            <td>Đăng tải thông tin sai sự thật</td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm">Mở khóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>