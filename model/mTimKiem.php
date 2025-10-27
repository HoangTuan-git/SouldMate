<?php
include_once('mKetNoi.php');
class TimKiem
{
    public function TimKiemNguoiDung($tuKhoa, $khuVuc, $doTuoiMin, $doTuoiMax, $ngheNghiep)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();

        $conditions = [];
        if (!empty($tuKhoa)) {
            $escapedTuKhoa = $conn->real_escape_string($tuKhoa);
            $conditions[] = "(hs.hoTen LIKE '%$escapedTuKhoa%' OR nd.tenDangNhap LIKE '%$escapedTuKhoa%')";
        }
        if (!empty($khuVuc)) {
            $escapedKhuVuc = $conn->real_escape_string($khuVuc);
            $conditions[] = "nd.diachi = '$escapedKhuVuc'";
        }
        if (!empty($doTuoiMin)) {
            $escapedDoTuoiMin = (int)$conn->real_escape_string($doTuoiMin);
            $conditions[] = "YEAR(CURDATE()) - YEAR(nd.ngaysinh) >= $escapedDoTuoiMin";
        }
        if (!empty($doTuoiMax)) {
            $escapedDoTuoiMax = (int)$conn->real_escape_string($doTuoiMax);
            $conditions[] = "YEAR(CURDATE()) - YEAR(nd.ngaysinh) <= $escapedDoTuoiMax";
        }
        if (!empty($ngheNghiep)) {
            $escapedNgheNghiep = $conn->real_escape_string($ngheNghiep);
            $conditions[] = "nd.nghenghiep = '$escapedNgheNghiep'";
        }

        $whereClause = '';
        if (count($conditions) > 0) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $query = "
            SELECT nd.*, hs.hoTen, hs.avatar
            FROM nguoidung nd
            LEFT JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung
            $whereClause
        ";

        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }
}
