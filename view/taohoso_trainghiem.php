<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo hồ sơ trải nghiệm - SoulMatch</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


</head>

<body>
    <?php
    if (!isset($_SESSION['uid'])) {
        echo '<script>alert("Vui lòng đăng nhập!");</script>';
        header("refresh:0;url=home.php?page=dangnhap");
        exit();
    }

    include_once('controller/cHoSo.php');
    $controller = new controlHoSo();

    // Kiểm tra đã có hồ sơ chưa
    $profile = $controller->getProfile($_SESSION['uid']);
    if ($profile && $profile->num_rows > 0) {
        echo '<script>alert("Bạn đã có hồ sơ rồi!");</script>';
        header("refresh:0;url=home.php");
        exit();
    }

    // Lấy dữ liệu cho form
    $formData = $controller->getFormData();

    // Xử lý submit
    if (isset($_POST['submit'])) {
        $data = [
            'hoTen' => $_POST['fullName'] ?? '',
            'ngaySinh' => $_POST['age'] ?? '',
            'gioiTinh' => $_POST['gender'] ?? '',
            'maThanhPho' => $_POST['location'] ?? null,
            'moTa' => $_POST['bio'] ?? '',
            'trangThaiHenHo' => 'trải nghiệm'
        ];

        // Upload avatar nếu có
        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $uploadResult = $controller->uploadAvatar($_FILES['avatar']);
            if ($uploadResult['success']) {
                $avatarPath = $uploadResult['path'];
            }
        }

        $result = $controller->createProfile($_SESSION['uid'], $data, $avatarPath);

        if ($result['success']) {
            echo '<script>alert("' . $result['message'] . '");</script>';
            header("refresh:0.5;url=home.php");
        } else {
            echo '<script>alert("' . $result['message'] . '");</script>';
        }
    }
    ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-10">
                <h2 class="text-center fw-bold mb-4">Tạo hồ sơ</h2>
                <div class="card border-0">
                    <div class="card-body p-2 p-md-2">
                        <form action="" method="post" enctype="multipart/form-data" id="profileForm">
                            <div class="mb-3">
                                <label for="fullName" class="form-label fw-semibold">Tên đầy đủ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Nhập tên đầy đủ của bạn" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="age" class="form-label fw-semibold">Ngày sinh của bạn <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="age" name="age" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label fw-semibold">Giới tính <span class="text-danger">*</span></label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="" selected disabled>Chọn giới tính của bạn</option>
                                        <option value="Nam">Nam</option>
                                        <option value="Nữ">Nữ</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label fw-semibold">Địa điểm</label>
                                <select class="form-select" id="location" name="location">
                                    <option value="" selected>Chọn địa điểm của bạn</option>
                                    <?php
                                    if ($formData['cities'] && $formData['cities']->num_rows > 0) {
                                        while ($city = $formData['cities']->fetch_assoc()) {
                                            echo '<option value="' . $city['maThanhPho'] . '">' . $city['tenThanhPho'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label fw-semibold">Mô tả bản thân</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Kể cho chúng tôi biết thêm về bạn và những gì bạn tìm kiếm..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Ảnh đại diện</label>
                                <div class="d-flex justify-content-center">
                                    <label for="avatarUpload" class="avatar-upload-box text-center">
                                        <div class="avatar-placeholder" id="avatarPreview">
                                            <i class="bi bi-person-fill fs-1 text-secondary"></i>
                                        </div>
                                        <div class="text-secondary mt-2 small">
                                            Kéo và thả ảnh của bạn vào đây,
                                            <span class="text-primary text-decoration-underline">hoặc nhấn để duyệt</span>
                                        </div>
                                        <p class="text-muted small">(Tối đa 5MB)</p>
                                        <input type="file" id="avatarUpload" name="avatar" class="d-none" accept="image/*" onchange="previewAvatar(event)">
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="home_test.php?page=profile_quiz" class="btn btn-light border">Hủy</a>
                                <button type="submit" name="submit" class="btn btn-primary">Lưu hồ sơ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewAvatar(event) {
            const preview = document.getElementById('avatarPreview');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';
                }
                reader.readAsDataURL(file);
            }
        }

        // Validate ngày sinh (phải >= 18 tuổi)
        document.getElementById('age').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();

            if (age < 18) {
                alert('Bạn phải đủ 18 tuổi để tạo hồ sơ!');
                this.value = '';
            }
        });
    </script>
</body>

</html>