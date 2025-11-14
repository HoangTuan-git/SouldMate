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
            // Lấy snapshot của bài viết
            $snapshot = $this->getPostSnapshot($maBaiDang);
            
            $sql = "INSERT INTO baocaovipham (maNguoiBaoCao, maBaiDang, maNguoiDungBiBaoCao, loaiBaoCao, lyDo, noiDungViPham, snapshotBaiViet) 
                    VALUES (?, ?, ?, 'baidang', ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iiisss", $maNguoiBaoCao, $maBaiDang, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham, $snapshot);
            $kq = $stmt->execute();

            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Lấy snapshot của bài viết để lưu vào báo cáo
     */
    private function getPostSnapshot($maBaiDang)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT bt.noiDungText, bt.noiDungAnh, bt.ngayTao, bt.phamVi, 
                           h.hoTen as tenNguoiDang, bt.maNguoiDung
                    FROM baidang bt
                    LEFT JOIN hosonguoidung h ON bt.maNguoiDung = h.maNguoiDung
                    WHERE bt.maBaiDang = ?";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maBaiDang);
            $stmt->execute();
            $result = $stmt->get_result();
            $post = $result->fetch_assoc();
            
            $con->close();
            
            if ($post) {
                $snapshot = [
                    'maBaiDang' => $maBaiDang,
                    'noiDungText' => $post['noiDungText'],
                    'noiDungAnh' => $post['noiDungAnh'],
                    'tenNguoiDang' => $post['tenNguoiDang'],
                    'maNguoiDung' => $post['maNguoiDung'],
                    'thoiGianDang' => $post['thoiGianDang'],
                    'quyenRiengTu' => $post['quyenRiengTu'],
                    'snapshotTime' => date('Y-m-d H:i:s')
                ];
                
                return json_encode($snapshot, JSON_UNESCAPED_UNICODE);
            }
        }
        return null;
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
            // Lấy tin nhắn được báo cáo (với tên người gửi)
            $sql = "SELECT tn.*, h.hoTen as tenNguoiGui, tn.thoiGianGui as thoiGian
                    FROM tinnhan tn
                    LEFT JOIN hosonguoidung h ON tn.maNguoiDung1 = h.maNguoiDung
                    WHERE tn.maTinNhan = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maTinNhan);
            $stmt->execute();
            $reportedMessage = $stmt->get_result()->fetch_assoc();

            if (!$reportedMessage) {
                $con->close();
                return null;
            }

            $thoiGianGui = $reportedMessage['thoiGianGui'];

            // Lấy tin nhắn trước đó (với tên người gửi)
            $sqlBefore = "SELECT tn.*, h.hoTen as tenNguoiGui, tn.thoiGianGui as thoiGian
                         FROM tinnhan tn
                         LEFT JOIN hosonguoidung h ON tn.maNguoiDung1 = h.maNguoiDung
                         WHERE ((tn.maNguoiDung1 = ? AND tn.maNguoiDung2 = ?) OR (tn.maNguoiDung1 = ? AND tn.maNguoiDung2 = ?))
                         AND tn.thoiGianGui < ?
                         ORDER BY tn.thoiGianGui DESC
                         LIMIT ?";
            $stmtBefore = $con->prepare($sqlBefore);
            $stmtBefore->bind_param("iiiisi", $maNguoiDung1, $maNguoiDung2, $maNguoiDung2, $maNguoiDung1, $thoiGianGui, $contextSize);
            $stmtBefore->execute();
            $messagesBefore = $stmtBefore->get_result()->fetch_all(MYSQLI_ASSOC);

            // Lấy tin nhắn sau đó (với tên người gửi)
            $sqlAfter = "SELECT tn.*, h.hoTen as tenNguoiGui, tn.thoiGianGui as thoiGian
                        FROM tinnhan tn
                        LEFT JOIN hosonguoidung h ON tn.maNguoiDung1 = h.maNguoiDung
                        WHERE ((tn.maNguoiDung1 = ? AND tn.maNguoiDung2 = ?) OR (tn.maNguoiDung1 = ? AND tn.maNguoiDung2 = ?))
                        AND tn.thoiGianGui > ?
                        ORDER BY tn.thoiGianGui ASC
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
    public function getUsersWithManyReports($minReports = 3)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        nd.maNguoiDung,
                        nd.trangThaiViPham,
                        h.hoTen,
                        COUNT(bc.maBaoCao) as soLanBaoCao
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    INNER JOIN baocaovipham bc ON nd.maNguoiDung = bc.maNguoiDungBiBaoCao
                    GROUP BY nd.maNguoiDung, nd.trangThaiViPham, h.hoTen
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
                        bc.thoiGianBaoCao as ngayBaoCao,
                        bc.trangThai,
                        bc.noiDungViPham,
                        bc.maBaiDang,
                        bc.maTinNhan,
                        bc.contextTinNhan,
                        bc.maNguoiDungBiBaoCao,
                        h.hoTen as tenNguoiBaoCao
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
    public function getReportsByType($loaiBaoCao = null, $trangThai = null)
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
                    WHERE 1=1";
            
            $params = [];
            $types = '';
            
            if ($trangThai) {
                $sql .= " AND bc.trangThai = ?";
                $params[] = $trangThai;
                $types .= 's';
            }
            
            if ($loaiBaoCao) {
                $sql .= " AND bc.loaiBaoCao = ?";
                $params[] = $loaiBaoCao;
                $types .= 's';
            }
            
            $sql .= " ORDER BY bc.thoiGianBaoCao DESC";
            
            $stmt = $con->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
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
                        (SELECT lv1.ngayThucHien 
                         FROM lichsuvipham lv1 
                         WHERE lv1.maNguoiDung = nd.maNguoiDung 
                         AND lv1.hanhDong LIKE 'Khóa tài khoản:%'
                         ORDER BY lv1.ngayThucHien DESC LIMIT 1) as ngayBiKhoa,
                        (SELECT lv2.hanhDong 
                         FROM lichsuvipham lv2 
                         WHERE lv2.maNguoiDung = nd.maNguoiDung 
                         AND lv2.hanhDong LIKE 'Khóa tài khoản:%'
                         ORDER BY lv2.ngayThucHien DESC LIMIT 1) as lyDoKhoa
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    WHERE nd.trangThaiViPham = 'khoa'
                    ORDER BY ngayBiKhoa DESC";
            
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
            // Kiểm tra trạng thái hiện tại
            $checkSql = "SELECT trangThaiViPham FROM nguoidung WHERE maNguoiDung = ?";
            $checkStmt = $con->prepare($checkSql);
            $checkStmt->bind_param("i", $maNguoiDung);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $userData = $checkResult->fetch_assoc();
            
            // Nếu đã bị khóa rồi
            if ($userData && $userData['trangThaiViPham'] == 'khoa') {
                $con->close();
                return false;
            }
            
            // Bắt đầu transaction
            $con->begin_transaction();
            
            try {
                // Cập nhật trạng thái vi phạm
                $sql1 = "UPDATE nguoidung 
                        SET trangThaiViPham = 'khoa'
                        WHERE maNguoiDung = ?";
                
                $stmt1 = $con->prepare($sql1);
                $stmt1->bind_param("i", $maNguoiDung);
                $stmt1->execute();
                
                // Thêm vào lịch sử vi phạm
                $hanhDong = "Khóa tài khoản: " . $lyDo;
                $sql2 = "INSERT INTO lichsuvipham (maNguoiDung, ngayThucHien, hanhDong) 
                        VALUES (?, NOW(), ?)";
                
                $stmt2 = $con->prepare($sql2);
                $stmt2->bind_param("is", $maNguoiDung, $hanhDong);
                $stmt2->execute();
                
                // Cập nhật trạng thái các báo cáo thành 'daxuly'
                $sql3 = "UPDATE baocaovipham 
                        SET trangThai = 'daxuly'
                        WHERE maNguoiDungBiBaoCao = ? 
                        AND trangThai = 'dangxuly'";
                
                $stmt3 = $con->prepare($sql3);
                $stmt3->bind_param("i", $maNguoiDung);
                $stmt3->execute();
                
                // Commit transaction
                $con->commit();
                $kq = true;
            } catch (Exception $e) {
                // Rollback nếu có lỗi
                $con->rollback();
                $kq = false;
            }
            
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
            // Bắt đầu transaction
            $con->begin_transaction();
            
            try {
                // Cập nhật trạng thái vi phạm
                $sql1 = "UPDATE nguoidung 
                        SET trangThaiViPham = NULL
                        WHERE maNguoiDung = ?";
                
                $stmt1 = $con->prepare($sql1);
                $stmt1->bind_param("i", $maNguoiDung);
                $stmt1->execute();
                
                // Thêm vào lịch sử vi phạm
                $hanhDong = "Mở khóa tài khoản";
                $sql2 = "INSERT INTO lichsuvipham (maNguoiDung, ngayThucHien, hanhDong) 
                        VALUES (?, NOW(), ?)";
                
                $stmt2 = $con->prepare($sql2);
                $stmt2->bind_param("is", $maNguoiDung, $hanhDong);
                $stmt2->execute();
                
                // Commit transaction
                $con->commit();
                $kq = true;
            } catch (Exception $e) {
                // Rollback nếu có lỗi
                $con->rollback();
                $kq = false;
            }
            
            $con->close();
            return $kq;
        }
        return false;
    }

    /**
     * Tìm kiếm người dùng vi phạm
     */
    public function searchViolatingUsers($keyword, $minReports = 3)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $keyword = "%$keyword%";
            $sql = "SELECT 
                        nd.maNguoiDung,
                        nd.trangThaiViPham,
                        h.hoTen,
                        COUNT(bc.maBaoCao) as soLanBaoCao
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    INNER JOIN baocaovipham bc ON nd.maNguoiDung = bc.maNguoiDungBiBaoCao
                    WHERE nd.maNguoiDung LIKE ? OR h.hoTen LIKE ?
                    GROUP BY nd.maNguoiDung, nd.trangThaiViPham, h.hoTen
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
                        (SELECT lv1.ngayThucHien 
                         FROM lichsuvipham lv1 
                         WHERE lv1.maNguoiDung = nd.maNguoiDung 
                         AND lv1.hanhDong LIKE 'Khóa tài khoản:%'
                         ORDER BY lv1.ngayThucHien DESC LIMIT 1) as ngayBiKhoa,
                        (SELECT lv2.hanhDong 
                         FROM lichsuvipham lv2 
                         WHERE lv2.maNguoiDung = nd.maNguoiDung 
                         AND lv2.hanhDong LIKE 'Khóa tài khoản:%'
                         ORDER BY lv2.ngayThucHien DESC LIMIT 1) as lyDoKhoa
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    WHERE nd.trangThaiViPham = 'khoa' 
                    AND (nd.maNguoiDung LIKE ? OR h.hoTen LIKE ?)
                    ORDER BY ngayBiKhoa DESC";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ss", $keyword, $keyword);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $con->close();
            return $result;
        }
        return false;
    }
    
    /**
     * Lấy thông tin người dùng
     */
    public function getUserInfo($maNguoiDung)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        nd.maNguoiDung,
                        nd.email,
                        nd.trangThaiViPham,
                        (SELECT lv1.ngayThucHien 
                         FROM lichsuvipham lv1 
                         WHERE lv1.maNguoiDung = nd.maNguoiDung 
                         AND lv1.hanhDong LIKE 'Khóa tài khoản:%'
                         ORDER BY lv1.ngayThucHien DESC LIMIT 1) as ngayBiKhoa,
                        (SELECT lv2.hanhDong 
                         FROM lichsuvipham lv2 
                         WHERE lv2.maNguoiDung = nd.maNguoiDung 
                         AND lv2.hanhDong LIKE 'Khóa tài khoản:%'
                         ORDER BY lv2.ngayThucHien DESC LIMIT 1) as lyDoKhoa,
                        h.hoTen
                    FROM nguoidung nd
                    LEFT JOIN hosonguoidung h ON nd.maNguoiDung = h.maNguoiDung
                    WHERE nd.maNguoiDung = ?";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maNguoiDung);
            $stmt->execute();
            $result = $stmt->get_result();
            $userInfo = $result->fetch_assoc();
            
            $con->close();
            return $userInfo;
        }
        return false;
    }

    /**
     * Lấy thống kê báo cáo của người dùng
     */
    public function getReportStatsByUser($maNguoiDung)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        COUNT(*) as tongBaoCao,
                        SUM(CASE WHEN loaiBaoCao = 'nguoidung' THEN 1 ELSE 0 END) as baoCaoNguoiDung,
                        SUM(CASE WHEN loaiBaoCao = 'baidang' THEN 1 ELSE 0 END) as baoCaoBaiDang,
                        SUM(CASE WHEN loaiBaoCao = 'tinnhan' THEN 1 ELSE 0 END) as baoCaoTinNhan
                    FROM baocaovipham
                    WHERE maNguoiDungBiBaoCao = ?";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maNguoiDung);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            
            $con->close();
            return $stats;
        }
        return false;
    }

    /**
     * Lấy lịch sử vi phạm của người dùng từ bảng lichsuvipham
     */
    public function getViolationHistory($maNguoiDung)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT 
                        maLichSu,
                        maNguoiDung,
                        ngayThucHien,
                        hanhDong
                    FROM lichsuvipham
                    WHERE maNguoiDung = ?
                    ORDER BY ngayThucHien DESC";
            
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
     * Lấy thông tin bài viết theo ID (cho admin)
     * Trả về null nếu bài viết đã bị xóa
     */
    public function getPostById($maBaiDang)
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "SELECT bt.*, h.hoTen as tenNguoiDang, h.avatar
                    FROM baidang bt
                    LEFT JOIN hosonguoidung h ON bt.maNguoiDung = h.maNguoiDung
                    WHERE bt.maBaiDang = ?";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $maBaiDang);
            $stmt->execute();
            $result = $stmt->get_result();
            $post = $result->fetch_assoc();
            
            $con->close();
            return $post;
        }
        return null;
    }

    /**
     * Xóa bài viết vi phạm (soft delete - cập nhật trạng thái)
     */
    public function deletePost($maBaiDang, $lyDo = '')
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            // Bắt đầu transaction
            $con->begin_transaction();
            
            try {
                // Bước 1: Cập nhật trạng thái các báo cáo liên quan TRƯỚC KHI xóa bài viết
                $sqlUpdate = "UPDATE baocaovipham 
                             SET trangThai = 'daxuly', 
                                 noiDungViPham = CONCAT(IFNULL(noiDungViPham, ''), '\n[Admin đã xóa bài viết: ', ?, ']')
                             WHERE maBaiDang = ? AND trangThai IN ('dangxuly', 'choxuly')";
                $stmtUpdate = $con->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $lyDo, $maBaiDang);
                $stmtUpdate->execute();
                $affectedReports = $stmtUpdate->affected_rows;
                $stmtUpdate->close();
                
                // Bước 2: Xóa bài viết
                $sql = "DELETE FROM baidang WHERE maBaiDang = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("i", $maBaiDang);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $con->commit();
                $con->close();
                
                return true;
                
            } catch (Exception $e) {
                // Rollback nếu có lỗi
                $con->rollback();
                $con->close();
                error_log("Error deleting post: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}
