<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khám phá - SoulMatch</title>
    <style>
        body {
            background-color: #f8f9fa; /* Màu nền xám rất nhạt */
        }
        /* === Search & Filters === */
        .filter-section {
            background-color: #ffffff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .search-bar {
            position: relative;
        }
        .search-bar .form-control {
            padding-left: 2.5rem;
        }
        .search-bar .bi-search {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        /* === Profile Card === */
        .profile-card {
            background-color: #ffffff;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            height: 100%; /* Đảm bảo các card cao bằng nhau */
        }
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
        }
        
        .profile-card-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto;
            border: 3px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .profile-card .card-body {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }
        
        .profile-card .card-text {
            font-size: 0.9rem;
            color: #6c757d;
            /* Giới hạn 3 dòng text */
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;  
            overflow: hidden;
            min-height: 4.05rem; /* 0.9rem * 1.5 line-height * 3 lines */
        }
        
        .like-button {
            font-weight: 500;
            color: #6c757d;
            text-decoration: none;
        }
        .like-button:hover {
            color: #dc3545; /* text-danger */
        }
        .like-button .bi-heart {
            vertical-align: middle;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 16px;
        }
        
        .empty-state p {
            font-size: 16px;
            color: #65676b;
            margin: 0;
        }

    </style>
</head>
<body>
<?php
// Xử lý tìm kiếm
include_once("controller/cTimKiem.php");
include_once("controller/cHoSo.php");

$timKiemController = new controlTimKiem();
$hoSoController = new controlHoSo();

// Lấy dữ liệu form
$formData = $hoSoController->getFormData();

// Lấy tham số tìm kiếm từ GET
$filters = [
    'tuKhoa' => $_GET['tuKhoa'] ?? '',
    'khuVuc' => $_GET['khuVuc'] ?? '',
    'doTuoi' => $_GET['doTuoi'] ?? '',
    'ngheNghiep' => $_GET['ngheNghiep'] ?? ''
];

// Thực hiện tìm kiếm
$danhSachNguoiDung = $timKiemController->layDanhSachKetQua($filters);
$soLuongKetQua = count($danhSachNguoiDung);
?>
    <main class="container py-4 py-lg-5">
        
        <h2 class="fw-bold text-center mb-4">Khám phá các hồ sơ mới</h2>

        <section class="filter-section">
            <form method="GET" action="home.php">
                <input type="hidden" name="page" value="timkiem">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label for="tuKhoa" class="form-label fw-semibold">Tìm kiếm</label>
                        <div class="search-bar">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" name="tuKhoa" id="tuKhoa" 
                                   placeholder="Tìm kiếm người dùng theo tên..."
                                   value="<?= htmlspecialchars($filters['tuKhoa']) ?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="khuVuc" class="form-label fw-semibold">Khu vực</label>
                        <select class="form-select" name="khuVuc" id="khuVuc">
                            <option value="" <?= empty($filters['khuVuc']) ? 'selected' : '' ?>>Tất cả</option>
                            <?php
                                if ($formData['cities'] && $formData['cities']->num_rows > 0) {
                                    while ($city = $formData['cities']->fetch_assoc()) {
                                        $selected = ($filters['khuVuc'] == $city['maThanhPho']) ? 'selected' : '';
                                        echo '<option value="' . $city['maThanhPho'] . '" ' . $selected . '>' 
                                             . htmlspecialchars($city['tenThanhPho']) . '</option>';
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="doTuoi" class="form-label fw-semibold">Độ tuổi</label>
                        <select class="form-select" name="doTuoi" id="doTuoi">
                            <option value="" <?= empty($filters['doTuoi']) ? 'selected' : '' ?>>Tất cả</option>
                            <option value="18-22" <?= $filters['doTuoi'] == '18-22' ? 'selected' : '' ?>>18 - 22</option>
                            <option value="23-28" <?= $filters['doTuoi'] == '23-28' ? 'selected' : '' ?>>23 - 28</option>
                            <option value="29-35" <?= $filters['doTuoi'] == '29-35' ? 'selected' : '' ?>>29 - 35</option>
                            <option value="35+" <?= $filters['doTuoi'] == '35+' ? 'selected' : '' ?>>Trên 35</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Lọc
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="results-section">
            <h4 class="fw-bold mb-4">Kết quả tìm kiếm (<?= $soLuongKetQua ?>)</h4>
            
            <?php if ($soLuongKetQua > 0): ?>
                <!-- Hiển thị danh sách người dùng, ngoại trừ người dùng hiện tại -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php foreach ($danhSachNguoiDung as $nguoiDung): 
                        if ($nguoiDung['maNguoiDung'] !== $_SESSION['uid']): ?>
                        <div class="col">
                            <div class="card profile-card text-center">
                                <div class="card-body">
                                    <?php 
                                    $avatarPath = !empty($nguoiDung['avatar']) 
                                        ? 'uploads/avatars/' . htmlspecialchars($nguoiDung['avatar'])
                                        : 'uploads/avatars/default.png';
                                    ?>
                                    <img src="<?= $avatarPath ?>" alt="Profile" class="profile-card-img mb-3">
                                    
                                    <h5 class="card-title fw-bold mb-1">
                                        <?= htmlspecialchars($nguoiDung['hoTen'] ?? 'Người dùng') ?>
                                    </h5>
                                    
                                    <p class="text-muted small mb-2">
                                        <?= $nguoiDung['tuoi'] ? $nguoiDung['tuoi'] . ' tuổi' : 'N/A' ?>
                                        <?php if (!empty($nguoiDung['gioiTinh'])): ?>
                                            • <?= $nguoiDung['gioiTinh'] == 'Nam' ? '👨' : '👩' ?>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <?php if (!empty($nguoiDung['tenThanhPho'])): ?>
                                        <p class="text-muted small mb-3">
                                            <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($nguoiDung['tenThanhPho']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($nguoiDung['tenNghe'])): ?>
                                        <p class="text-muted small mb-3">
                                            <i class="bi bi-briefcase-fill"></i> <?= htmlspecialchars($nguoiDung['tenNghe']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="card-text">
                                        <?= !empty($nguoiDung['moTa']) 
                                            ? htmlspecialchars($nguoiDung['moTa']) 
                                            : 'Chưa có mô tả...' ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="home.php?page=profile&uid=<?= $nguoiDung['maNguoiDung'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-person"></i> Xem profile
                                        </a>
                                        <a href="#" class="like-button" 
                                           onclick="handleLike(<?= $nguoiDung['maNguoiDung'] ?>); return false;">
                                            <i class="bi bi-heart"></i> Thích
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; 
                endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <p>Không tìm thấy kết quả phù hợp</p>
                    <p class="text-muted small">Hãy thử điều chỉnh bộ lọc của bạn</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        function handleLike(userId) {
            // TODO: Implement like functionality
            alert('Tính năng đang được phát triển. User ID: ' + userId);
        }
    </script>
</body>
</html>