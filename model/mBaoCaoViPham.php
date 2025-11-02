<?php
include_once('mKetNoi.php');

class modelBaoCaoViPham
{

    /**
     * Tạo báo cáo vi phạm
     */
    public function createReport($maNguoiDung1, $maNguoiDung2, $lyDo, $urlNoiDung = '')
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "INSERT INTO baocaovipham (maNguoiDung1, maNguoiDung2, lyDo, urlNoiDung, ngayTao) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iiss", $maNguoiDung1, $maNguoiDung2, $lyDo, $urlNoiDung);
            $kq = $stmt->execute();

            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Lấy danh sách người dùng có nhiều báo cáo (>= ngưỡng)
     */
    public function getUsersWithManyReports($minReports = 15)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        nd.maNguoiDung,
                        h.hoTen,
                        COUNT(bc.maBaoCao) as soLanBaoCao
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    INNER JOIN baocaovipham bc ON nd.maNguoiDung = bc.maNguoiDung2
                    GROUP BY nd.maNguoiDung, h.hoTen
                    HAVING soLanBaoCao >= ?
                    ORDER BY soLanBaoCao DESC";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $minReports);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $con->close();
            return $result;
        }
        return false;
    }

    /**
     * Lấy chi tiết báo cáo của một người dùng
     */
    public function getReportDetailsByUser($maNguoiDung)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        bc.maBaoCao,
                        bc.lyDo,
                        bc.urlNoiDung,
                        bc.ngayTao,
                        h.hoTen as nguoiBaoCao
                    FROM baocaovipham bc
                    LEFT JOIN hosonguoidung h ON bc.maNguoiDung1 = h.maNguoiDung
                    WHERE bc.maNguoiDung2 = ?
                    ORDER BY bc.ngayTao DESC";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maNguoiDung);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $con->close();
            return $result;
        }
        return false;
    }

    /**
     * Lấy danh sách tài khoản bị khóa
     */
    public function getLockedAccounts()
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        nd.maNguoiDung,
                        h.hoTen,
                        nd.trangThaiViPham,
                        nd.ngayBiKhoa,
                        nd.lyDoKhoa
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    WHERE nd.trangThaiViPham = 'khoa'
                    ORDER BY nd.ngayBiKhoa DESC";
            
            $result = $con->query($sql);
            $con->close();
            return $result;
        }
        return false;
    }

    /**
     * Khóa tài khoản
     */
    public function lockAccount($maNguoiDung, $lyDo)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "UPDATE nguoidung 
                    SET trangThaiViPham = 'khoa', 
                        ngayBiKhoa = NOW(), 
                        lyDoKhoa = ?
                    WHERE maNguoiDung = ?";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("si", $lyDo, $maNguoiDung);
            $kq = $stmt->execute();
            
            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Mở khóa tài khoản
     */
    public function unlockAccount($maNguoiDung)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "UPDATE nguoidung 
                    SET trangThaiViPham = NULL, 
                        ngayBiKhoa = NULL, 
                        lyDoKhoa = NULL
                    WHERE maNguoiDung = ?";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maNguoiDung);
            $kq = $stmt->execute();
            
            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Tìm kiếm người dùng vi phạm
     */
    public function searchViolatingUsers($keyword, $minReports = 15)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $keyword = "%$keyword%";
            $sql = "SELECT 
                        nd.maNguoiDung,
                        h.hoTen,
                        COUNT(bc.maBaoCao) as soLanBaoCao
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    INNER JOIN baocaovipham bc ON nd.maNguoiDung = bc.maNguoiDung2
                    WHERE nd.maNguoiDung LIKE ? OR h.hoTen LIKE ?
                    GROUP BY nd.maNguoiDung, h.hoTen
                    HAVING soLanBaoCao >= ?
                    ORDER BY soLanBaoCao DESC";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ssi", $keyword, $keyword, $minReports);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $con->close();
            return $result;
        }
        return false;
    }

    /**
     * Tìm kiếm tài khoản bị khóa
     */
    public function searchLockedAccounts($keyword)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $keyword = "%$keyword%";
            $sql = "SELECT 
                        nd.maNguoiDung,
                        h.hoTen,
                        nd.trangThaiViPham,
                        nd.ngayBiKhoa,
                        nd.lyDoKhoa
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    WHERE nd.trangThaiViPham = 'khoa' 
                    AND (nd.maNguoiDung LIKE ? OR h.hoTen LIKE ?)
                    ORDER BY nd.ngayBiKhoa DESC";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ss", $keyword, $keyword);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $con->close();
            return $result;
        }
        return false;
    }
}
