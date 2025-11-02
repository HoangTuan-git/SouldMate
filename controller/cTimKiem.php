<?php
include_once('model/mTimKiem.php');

class controlTimKiem
{
    /**
     * Tìm kiếm người dùng với các bộ lọc
     */
    public function timKiemNguoiDung($filters = [])
    {
        $model = new TimKiem();

        // Lấy UID của người dùng hiện tại (nếu có)
        $currentUserId = isset($_SESSION['uid']) ? $_SESSION['uid'] : null;
        // Lấy các tham số tìm kiếm
        $tuKhoa = $filters['tuKhoa'] ?? '';
        $khuVuc = $filters['khuVuc'] ?? '';
        $doTuoi = $filters['doTuoi'] ?? '';
        $ngheNghiep = $filters['ngheNghiep'] ?? '';

        // Xử lý độ tuổi
        $doTuoiMin = null;
        $doTuoiMax = null;

        if (!empty($doTuoi)) {
            switch ($doTuoi) {
                case '18-22':
                    $doTuoiMin = 18;
                    $doTuoiMax = 22;
                    break;
                case '23-28':
                    $doTuoiMin = 23;
                    $doTuoiMax = 28;
                    break;
                case '29-35':
                    $doTuoiMin = 29;
                    $doTuoiMax = 35;
                    break;
                case '35+':
                    $doTuoiMin = 35;
                    $doTuoiMax = 100;
                    break;
            }
        }

        // Gọi model để tìm kiếm
        $result = $model->TimKiemNguoiDung(
            $tuKhoa,
            $khuVuc,
            $doTuoiMin,
            $doTuoiMax,
            $ngheNghiep,
            $currentUserId
        );

        return $result;
    }

    /**
     * Đếm số lượng kết quả tìm kiếm
     */
    public function demKetQuaTimKiem($filters = [])
    {
        $result = $this->timKiemNguoiDung($filters);
        return $result ? $result->num_rows : 0;
    }

    /**
     * Format kết quả tìm kiếm thành mảng
     */
    public function layDanhSachKetQua($filters = [])
    {
        $result = $this->timKiemNguoiDung($filters);
        $danhSach = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Tính tuổi
                if (!empty($row['ngaysinh'])) {
                    $ngaySinh = new DateTime($row['ngaysinh']);
                    $now = new DateTime();
                    $tuoi = $now->diff($ngaySinh)->y;
                    $row['tuoi'] = $tuoi;
                } else {
                    $row['tuoi'] = null;
                }

                $danhSach[] = $row;
            }
        }

        return $danhSach;
    }
}
