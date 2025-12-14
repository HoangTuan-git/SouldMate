<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa hồ sơ - SoulMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f0f0f0;
        }

        .avatar-upload-btn {
            position: relative;
            cursor: pointer;
        }

        .avatar-upload-btn input[type="file"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .hobby-checkbox {
            cursor: pointer;
        }

        .hobby-checkbox:checked+label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .hobby-label {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            display: inline-block;
            cursor: pointer;
            transition: all 0.3s;
        }

        .hobby-label:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-cancel {
            background: #f0f0f0;
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            color: #666;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background: #e0e0e0;
        }
    </style>
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

    // Lấy hồ sơ hiện tại
    $profileResult = $controller->getProfile($_SESSION['uid']);
    if (!$profileResult || $profileResult->num_rows == 0) {
        echo '<script>alert("Bạn chưa có hồ sơ! Vui lòng tạo hồ sơ trước.");</script>';
        header("refresh:0;url=home.php?page=profile_quiz");
        exit();
    }

    $profile = $profileResult->fetch_assoc();
    $maHoSo = $profile['maHoSo'];

    // Lấy dữ liệu cho form
    $formData = $controller->getFormData();

    // Lấy sở thích hiện tại
    $model = new modelHoSo();
    $currentHobbies = [];
    $hobbiesResult = $model->getHobbies($maHoSo);
    if ($hobbiesResult && $hobbiesResult->num_rows > 0) {
        while ($hobby = $hobbiesResult->fetch_assoc()) {
            $currentHobbies[] = $hobby['maSoThich'];
        }
    }

    // Xử lý submit
    if (isset($_POST['submit'])) {
        $data = [
            'hoTen' => $_POST['fullName'] ?? '',
            'ngaySinh' => $_POST['birthDate'] ?? '',
            'gioiTinh' => $_POST['gender'] ?? '',
            'trangThaiHenHo' => $_POST['relationshipStatus'] ?? 'trải nghiệm',
            'maNgheNghiep' => !empty($_POST['job']) ? intval($_POST['job']) : null,
            'maThanhPho' => !empty($_POST['location']) ? intval($_POST['location']) : null,
            'moTa' => $_POST['bio'] ?? '',
            'soThich' => $_POST['hobbies'] ?? []
        ];

        // Nếu chọn trải nghiệm thì xóa nghề nghiệp và sở thích
        if ($data['trangThaiHenHo'] === 'trải nghiệm') {
            $data['maNgheNghiep'] = null;
            $data['soThich'] = [];
        }

        // Upload avatar nếu có
        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $uploadResult = $controller->uploadAvatar($_FILES['avatar']);
            if ($uploadResult['success']) {
                $avatarPath = $uploadResult['path'];
            } else {
                echo '<script>alert("' . $uploadResult['message'] . '");</script>';
            }
        }

        $result = $controller->updateProfile($maHoSo, $data, $avatarPath);

        if ($result['success']) {
            echo '<script>
                alert("' . $result['message'] . '");
                window.location.href = "home.php?page=me";
            </script>';
        } else {
            echo '<script>alert("' . $result['message'] . '");</script>';
        }
    }
    ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-11">
                <div class="profile-card p-4 p-md-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <h2 class="fw-bold mb-2">
                            <i class="bi bi-pencil-square text-primary"></i>
                            Chỉnh sửa hồ sơ
                        </h2>
                        <p class="text-muted">Cập nhật thông tin của bạn để có nhiều đề xuất phù hợp hơn</p>
                    </div>

                    <form action="" method="post" enctype="multipart/form-data" id="editProfileForm">
                        
                        <!-- Avatar -->
                        <div class="text-center mb-4">
                            <img src="<?= !empty($profile['avatar']) ? 'uploads/avatars/' . htmlspecialchars($profile['avatar']) : 'uploads/avatars/default.png' ?>" 
                                 alt="Avatar" 
                                 class="avatar-preview mb-3" 
                                 id="avatarPreview">
                            <div>
                                <label for="avatar" class="avatar-upload-btn btn btn-outline-primary btn-sm">
                                    <i class="bi bi-camera-fill"></i> Đổi ảnh đại diện
                                    <input type="file" name="avatar" id="avatar" accept="image/*">
                                </label>
                            </div>
                        </div>

                        <!-- Thông tin cơ bản -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fullName" class="form-label fw-semibold">
                                    Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="fullName" 
                                       name="fullName" 
                                       value="<?= htmlspecialchars($profile['hoTen']) ?>"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="birthDate" class="form-label fw-semibold">
                                    Ngày sinh <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="birthDate" 
                                       name="birthDate" 
                                       value="<?= htmlspecialchars($profile['ngaySinh']) ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Giới tính -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Giới tính <span class="text-danger">*</span>
                            </label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="gender" 
                                           id="male" 
                                           value="Nam" 
                                           <?= $profile['gioiTinh'] == 'Nam' ? 'checked' : '' ?> 
                                           required>
                                    <label class="form-check-label" for="male">
                                        <i class="bi bi-gender-male text-primary"></i> Nam
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="gender" 
                                           id="female" 
                                           value="Nữ" 
                                           <?= $profile['gioiTinh'] == 'Nữ' ? 'checked' : '' ?> 
                                           required>
                                    <label class="form-check-label" for="female">
                                        <i class="bi bi-gender-female text-danger"></i> Nữ
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Trạng thái hẹn hò -->
                        <div class="mb-3">
                            <label for="relationshipStatus" class="form-label fw-semibold">
                                <i class="bi bi-heart-fill text-danger"></i> Mục đích hẹn hò <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="relationshipStatus" name="relationshipStatus" required>
                                <option value="trải nghiệm" <?= ($profile['trangThaiHenHo'] ?? 'trải nghiệm') == 'trải nghiệm' ? 'selected' : '' ?>>
                                    Trải nghiệm - Tìm hiểu bạn bè mới
                                </option>
                                <option value="nghiêm túc" <?= ($profile['trangThaiHenHo'] ?? '') == 'nghiêm túc' ? 'selected' : '' ?>>
                                    Nghiêm túc - Tìm kiếm mối quan hệ lâu dài
                                </option>
                            </select>
                            <small class="text-muted">Chọn mục đích để chúng tôi gợi ý phù hợp hơn</small>
                        </div>

                        <!-- Địa điểm & Nghề nghiệp -->
                        <div class="row mb-3" id="seriousOnlySection">
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt-fill text-danger"></i> Thành phố <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="location" name="location" required>
                                    <option value="">-- Chọn thành phố --</option>
                                    <?php 
                                    if ($formData['cities'] && $formData['cities']->num_rows > 0):
                                        while ($city = $formData['cities']->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $city['maThanhPho'] ?>" 
                                                <?= $profile['maThanhPho'] == $city['maThanhPho'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($city['tenThanhPho']) ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    endif; 
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="job" class="form-label fw-semibold">
                                    <i class="bi bi-briefcase-fill text-primary"></i> Nghề nghiệp
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="job" name="job" required>
                                    <option value="">-- Chọn nghề nghiệp --</option>
                                    <?php 
                                    if ($formData['jobs'] && $formData['jobs']->num_rows > 0):
                                        $currentNganh = '';
                                        while ($job = $formData['jobs']->fetch_assoc()): 
                                            $tenNganh = $job['tenNganh'] ?? 'Khác';
                                            
                                            if ($currentNganh !== $tenNganh) {
                                                if ($currentNganh !== '') echo '</optgroup>';
                                                echo '<optgroup label="' . htmlspecialchars($tenNganh) . '">';
                                                $currentNganh = $tenNganh;
                                            }
                                    ?>
                                        <option value="<?= $job['maNgheNghiep'] ?>" 
                                                <?= $profile['maNgheNghiep'] == $job['maNgheNghiep'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($job['tenNgheNghiep']) ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                        if ($currentNganh !== '') echo '</optgroup>';
                                    endif; 
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Giới thiệu bản thân -->
                        <div class="mb-4">
                            <label for="bio" class="form-label fw-semibold">
                                <i class="bi bi-person-lines-fill text-success"></i> Giới thiệu bản thân
                            </label>
                            <textarea class="form-control" 
                                      id="bio" 
                                      name="bio" 
                                      rows="4" 
                                      placeholder="Viết vài dòng về bản thân, sở thích, và điều bạn đang tìm kiếm..."><?= htmlspecialchars($profile['moTa'] ?? '') ?></textarea>
                            <small class="text-muted">Tối đa 500 ký tự</small>
                        </div>

                        <!-- Sở thích -->
                        <div class="mb-4" id="hobbiesSection">
                            <label class="form-label fw-semibold mb-3">
                                <i class="bi bi-heart-fill text-danger"></i> Sở thích của bạn
                                <span class="badge bg-info ms-1">Chỉ với mục đích nghiêm túc</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php 
                                if ($formData['hobbies'] && $formData['hobbies']->num_rows > 0):
                                    while ($hobby = $formData['hobbies']->fetch_assoc()): 
                                        $isChecked = in_array($hobby['maSoThich'], $currentHobbies);
                                ?>
                                    <div>
                                        <input type="checkbox" 
                                               class="hobby-checkbox d-none" 
                                               id="hobby_<?= $hobby['maSoThich'] ?>" 
                                               name="hobbies[]" 
                                               value="<?= $hobby['maSoThich'] ?>"
                                               <?= $isChecked ? 'checked' : '' ?>>
                                        <label class="hobby-label" for="hobby_<?= $hobby['maSoThich'] ?>">
                                            <?= htmlspecialchars($hobby['tenSoThich']) ?>
                                        </label>
                                    </div>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button type="button" class="btn-cancel" onclick="window.location.href='home.php?page=me'">
                                <i class="bi bi-x-circle"></i> Hủy
                            </button>
                            <button type="submit" name="submit" class="btn-save">
                                <i class="bi bi-check-circle"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle hiển thị nghề nghiệp và sở thích dựa trên trạng thái hẹn hò
        function toggleSeriousOnlyFields() {
            const relationshipStatus = document.getElementById('relationshipStatus').value;
            const seriousOnlySection = document.getElementById('seriousOnlySection');
            const hobbiesSection = document.getElementById('hobbiesSection');
            const jobSelect = document.getElementById('job');
            
            if (relationshipStatus === 'trải nghiệm') {
                // Ẩn nghề nghiệp (nhưng vẫn giữ địa điểm)
                seriousOnlySection.querySelector('.col-md-6:last-child').style.display = 'none';
                hobbiesSection.style.display = 'none';
                
                // Xóa giá trị nghề nghiệp và sở thích
                jobSelect.value = '';
                document.querySelectorAll('.hobby-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
            } else {
                // Hiển thị lại
                seriousOnlySection.querySelector('.col-md-6:last-child').style.display = 'block';
                hobbiesSection.style.display = 'block';
            }
        }
        
        // Khởi tạo khi load trang
        document.addEventListener('DOMContentLoaded', function() {
            toggleSeriousOnlyFields();
            
            // Lắng nghe thay đổi
            document.getElementById('relationshipStatus').addEventListener('change', toggleSeriousOnlyFields);
        });

        // Preview avatar
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Validate form
        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            const fullName = document.getElementById('fullName').value.trim();
            const birthDate = document.getElementById('birthDate').value;
            
            if (!fullName) {
                e.preventDefault();
                alert('Vui lòng nhập họ tên!');
                return false;
            }
            
            if (!birthDate) {
                e.preventDefault();
                alert('Vui lòng chọn ngày sinh!');
                return false;
            }

            // Kiểm tra tuổi (phải >= 18)
            const today = new Date();
            const birth = new Date(birthDate);
            const age = today.getFullYear() - birth.getFullYear();
            
            if (age < 18) {
                e.preventDefault();
                alert('Bạn phải từ 18 tuổi trở lên!');
                return false;
            }

            return true;
        });
    </script>
</body>

</html>
