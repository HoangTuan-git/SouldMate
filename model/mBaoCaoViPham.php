<?php
include_once('mKetNoi.php');

class modelBaoCaoViPham
{

    /**
     * Tạo báo cáo vi phạm người dùng
     */
    public function createUserReport($maNguoiBaoCao, $maNguoiDungBiBaoCao, $lyDo)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "INSERT INTO baocaovipham (maNguoiBaoCao, loaiBaoCao, maNguoiDungBiBaoCao, lyDo) 
                    VALUES (?, 'nguoidung', ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iis", $maNguoiBaoCao, $maNguoiDungBiBaoCao, $lyDo);
            $kq = $stmt->execute();

            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Tạo báo cáo vi phạm bài viết
     */
    public function createPostReport($maNguoiBaoCao, $maBaiDang, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham = '')
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "INSERT INTO baocaovipham (maNguoiBaoCao, maBaiDang, maNguoiDungBiBaoCao, loaiBaoCao, lyDo, noiDungViPham) 
                    VALUES (?, ?, ?, 'baidang', ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iiiss", $maNguoiBaoCao, $maBaiDang, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham);
            $kq = $stmt->execute();

            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Tạo báo cáo vi phạm tin nhắn với context
     */
    public function createMessageReport($maNguoiBaoCao, $maTinNhan, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham, $contextTinNhan, $thoiGianTinNhan)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "INSERT INTO baocaovipham 
                    (maNguoiBaoCao, maTinNhan, maNguoiDungBiBaoCao, loaiBaoCao, lyDo, noiDungViPham, contextTinNhan, thoiGianTinNhan) 
                    VALUES (?, ?, ?, 'tinnhan', ?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iiissss", $maNguoiBaoCao, $maTinNhan, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham, $contextTinNhan, $thoiGianTinNhan);
            $kq = $stmt->execute();

            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Lấy context tin nhắn (10-20 tin nhắn trước và sau)
     */
    public function getMessageContext($maTinNhan, $maNguoiDung1, $maNguoiDung2, $contextSize = 10)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            // Lấy tin nhắn được báo cáo
            $sql = "SELECT * FROM tinnhan WHERE maTinNhan = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maTinNhan);
            $stmt->execute();
            $reportedMessage = $stmt->get_result()->fetch_assoc();

            if (!$reportedMessage) {
                $con->close();
                return null;
            }

            $thoiGianGui = $reportedMessage['thoiGianGui'];

            // Lấy tin nhắn trước đó
            $sqlBefore = "SELECT * FROM tinnhan 
                         WHERE ((maNguoiDung1 = ? AND maNguoiDung2 = ?) OR (maNguoiDung1 = ? AND maNguoiDung2 = ?))
                         AND thoiGianGui < ?
                         ORDER BY thoiGianGui DESC
                         LIMIT ?";
            $stmtBefore = $con->prepare($sqlBefore);
            $stmtBefore->bind_param("iiiisi", $maNguoiDung1, $maNguoiDung2, $maNguoiDung2, $maNguoiDung1, $thoiGianGui, $contextSize);
            $stmtBefore->execute();
            $messagesBefore = $stmtBefore->get_result()->fetch_all(MYSQLI_ASSOC);

            // Lấy tin nhắn sau đó
            $sqlAfter = "SELECT * FROM tinnhan 
                        WHERE ((maNguoiDung1 = ? AND maNguoiDung2 = ?) OR (maNguoiDung1 = ? AND maNguoiDung2 = ?))
                        AND thoiGianGui > ?
                        ORDER BY thoiGianGui ASC
                        LIMIT ?";
            $stmtAfter = $con->prepare($sqlAfter);
            $stmtAfter->bind_param("iiiisi", $maNguoiDung1, $maNguoiDung2, $maNguoiDung2, $maNguoiDung1, $thoiGianGui, $contextSize);
            $stmtAfter->execute();
            $messagesAfter = $stmtAfter->get_result()->fetch_all(MYSQLI_ASSOC);

            $con->close();

            // Sắp xếp và trả về context
            $messagesBefore = array_reverse($messagesBefore);
            $context = array_merge($messagesBefore, [$reportedMessage], $messagesAfter);
            
            return json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        return null;
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
                    INNER JOIN baocaovipham bc ON nd.maNguoiDung = bc.maNguoiDungBiBaoCao
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
                        bc.loaiBaoCao,
                        bc.lyDo,
                        bc.thoiGianBaoCao,
                        bc.trangThai,
                        bc.noiDungViPham,
                        h.hoTen as nguoiBaoCao
                    FROM baocaovipham bc
                    LEFT JOIN hosonguoidung h ON bc.maNguoiBaoCao = h.maNguoiDung
                    WHERE bc.maNguoiDungBiBaoCao = ?
                    ORDER BY bc.thoiGianBaoCao DESC";
            
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
     * Lấy tất cả báo cáo theo loại
     */
    public function getReportsByType($loaiBaoCao = null, $trangThai = 'dangxuly')
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        bc.*,
                        h1.hoTen as tenNguoiBaoCao,
                        h2.hoTen as tenNguoiBiBaoCao
                    FROM baocaovipham bc
                    LEFT JOIN hosonguoidung h1 ON bc.maNguoiBaoCao = h1.maNguoiDung
                    LEFT JOIN hosonguoidung h2 ON bc.maNguoiDungBiBaoCao = h2.maNguoiDung
                    WHERE bc.trangThai = ?";
            
            if ($loaiBaoCao) {
                $sql .= " AND bc.loaiBaoCao = ?";
            }
            
            $sql .= " ORDER BY bc.thoiGianBaoCao DESC";
            
            $stmt = $con->prepare($sql);
            
            if ($loaiBaoCao) {
                $stmt->bind_param("ss", $trangThai, $loaiBaoCao);
            } else {
                $stmt->bind_param("s", $trangThai);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $con->close();
            return $result;
        }
        return false;
    }

    /**
     * Cập nhật trạng thái báo cáo
     */
    public function updateReportStatus($maBaoCao, $trangThai)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "UPDATE baocaovipham SET trangThai = ? WHERE maBaoCao = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("si", $trangThai, $maBaoCao);
            $kq = $stmt->execute();
            
            $con->close();
            return $kq;
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
                    INNER JOIN baocaovipham bc ON nd.maNguoiDung = bc.maNguoiDungBiBaoCao
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
