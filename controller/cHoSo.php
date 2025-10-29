<?php
include_once('model/mHoSo.php');

class controlHoSo
{

    /**
     * Tạo hồ sơ mới
     */
    public function createProfile($maNguoiDung, $data, $avatar = null)
    {
        $model = new modelHoSo();

        // Kiểm tra đã có hồ sơ chưa
        if ($model->hasProfile($maNguoiDung)) {
            return ['success' => false, 'message' => 'Bạn đã có hồ sơ rồi!'];
        }

        // Validate dữ liệu
        if (empty($data['hoTen']) || empty($data['ngaySinh']) || empty($data['gioiTinh'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!'];
        }

        // Xử lý avatar
        $avatarPath = $avatar ? $avatar : 'default.jpg';

        // Tạo hồ sơ
        $maHoSo = $model->createProfile(
            $maNguoiDung,
            $data['hoTen'],
            $data['ngaySinh'],
            $data['gioiTinh'],
            $data['maNgheNghiep'] ?? null,
            $data['maThanhPho'] ?? null,
            $data['moTa'] ?? '',
            $avatarPath,
            $data['trangThaiHenHo'] ?? 'trainghiem'
        );

        if ($maHoSo) {
            // Thêm sở thích nếu có
            if (!empty($data['soThich'])) {
                $soThichArray = is_array($data['soThich']) ? $data['soThich'] : explode(',', $data['soThich']);
                foreach ($soThichArray as $maSoThich) {
                    if (!empty($maSoThich)) {
                        $model->addHobby($maHoSo, $maSoThich);
                    }
                }
            }
            //Lưu avatar vào session
            if (!empty($avatarPath) && $avatarPath !== 'default.jpg') {
                $_SESSION['avatar'] = $avatarPath;
            } else {
                $_SESSION['avatar'] = 'default.png';
            }
            return ['success' => true, 'message' => 'Tạo hồ sơ thành công!', 'maHoSo' => $maHoSo];
        }

        return ['success' => false, 'message' => 'Lỗi khi tạo hồ sơ!'];
    }

    /**
     * Cập nhật hồ sơ
     */
    public function updateProfile($maHoSo, $data, $avatar = null)
    {
        $model = new modelHoSo();

        // Validate dữ liệu
        if (empty($data['hoTen']) || empty($data['ngaySinh']) || empty($data['gioiTinh'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!'];
        }

        // Cập nhật hồ sơ
        $updated = $model->updateProfile(
            $maHoSo,
            $data['hoTen'],
            $data['ngaySinh'],
            $data['gioiTinh'],
            $data['maNgheNghiep'] ?? null,
            $data['maThanhPho'] ?? null,
            $data['moTa'] ?? '',
            $avatar
        );

        if ($updated) {
            // Cập nhật sở thích
            if (isset($data['soThich'])) {
                $model->removeAllHobbies($maHoSo);
                $soThichArray = is_array($data['soThich']) ? $data['soThich'] : explode(',', $data['soThich']);
                foreach ($soThichArray as $maSoThich) {
                    if (!empty($maSoThich)) {
                        $model->addHobby($maHoSo, $maSoThich);
                    }
                }
            }

            return ['success' => true, 'message' => 'Cập nhật hồ sơ thành công!'];
        }

        return ['success' => false, 'message' => 'Lỗi khi cập nhật hồ sơ!'];
    }

    /**
     * Lấy hồ sơ của user
     */
    public function getProfile($maNguoiDung)
    {
        $model = new modelHoSo();
        return $model->getProfileByUserId($maNguoiDung);
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar($file)
    {
        // Kiểm tra file
        if (!isset($file) || $file['error'] != 0) {
            return ['success' => false, 'message' => 'Vui lòng chọn file!'];
        }

        // Kiểm tra loại file
        $allowedTypes = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG)!'];
        }

        // Kiểm tra kích thước (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File quá lớn! Tối đa 5MB.'];
        }

        // Tạo thư mục nếu chưa có
        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Tạo tên file unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('avatar_') . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        // Upload file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => true, 'path' => $fileName];
        }

        return ['success' => false, 'message' => 'Lỗi khi upload file!'];
    }

    /**
     * Lấy dữ liệu cho form
     */
    public function getFormData()
    {
        $model = new modelHoSo();

        return [
            'cities' => $model->getAllCities(),
            'jobs' => $model->getAllJobs(),
            'hobbies' => $model->getAllHobbies()
        ];
    }
    public function checkHoSoExists($maNguoiDung)
    {
        $model = new modelHoSo();
        // Dùng đúng logic kiểm tra đã có hồ sơ chưa
        return $model->hasProfile($maNguoiDung);
    }

}
