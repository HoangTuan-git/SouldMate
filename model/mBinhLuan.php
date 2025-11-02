<?php
include_once(__DIR__ . '/mKetNoi.php');

class mBinhLuan
{
    public function AddComment($maNguoiDung, $maBaiDang, $noiDung)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        if (!$conn) {
            error_log("[AddComment] Không kết nối được DB");
            return false;
        }
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $date = date('Y-m-d H:i:s');
        $noiDung = $conn->real_escape_string(trim($noiDung));
        $maNguoiDung = (int)$maNguoiDung;
        $maBaiDang = (int)$maBaiDang;
        $sql = "INSERT INTO binhluan (maNguoiDung, maBaiDang, noiDung, thoiGianTao, trangThai) 
                VALUES ($maNguoiDung, $maBaiDang, '$noiDung', '$date', 1)";
        $kq = $conn->query($sql);
        if (!$kq) {
            error_log('[AddComment] SQL Error: ' . $conn->error);
            error_log('[AddComment] Query: ' . $sql);
        }
        if ($kq) {
            $sqlUpdate = "UPDATE baidang SET soBinhLuan = soBinhLuan + 1 WHERE maBaiDang = $maBaiDang";
            $conn->query($sqlUpdate);
        }
        $p->NgatKetNoi($conn);
        return $kq ? true : false;
    }

    // Xóa bình luận - cho phép chủ comment hoặc chủ bài đăng xóa
    public function DeleteComment($maBinhLuan, $maNguoiDung)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        
        $maBinhLuan = (int)$maBinhLuan;
        $maNguoiDung = (int)$maNguoiDung;

        // Lấy thông tin comment và bài đăng
        $sqlCheck = "SELECT bl.maBaiDang, bl.maNguoiDung as commentOwner, bd.maNguoiDung as postOwner
                     FROM binhluan bl
                     INNER JOIN baidang bd ON bl.maBaiDang = bd.maBaiDang
                     WHERE bl.maBinhLuan = $maBinhLuan";
        $result = $conn->query($sqlCheck);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $maBaiDang = $row['maBaiDang'];
            $commentOwner = $row['commentOwner'];
            $postOwner = $row['postOwner'];
            
            // Kiểm tra quyền: phải là chủ comment HOẶC chủ bài đăng
            if ($maNguoiDung == $commentOwner || $maNguoiDung == $postOwner) {
                // Xóa bình luận
                $sql = "DELETE FROM binhluan WHERE maBinhLuan = $maBinhLuan";
                $kq = $conn->query($sql);
                
                if ($kq) {
                    // Cập nhật số lượng bình luận
                    $sqlUpdate = "UPDATE baidang SET soBinhLuan = GREATEST(soBinhLuan - 1, 0) WHERE maBaiDang = $maBaiDang";
                    $conn->query($sqlUpdate);
                }
                
                $p->NgatKetNoi($conn);
                return $kq;
            }
        }
        
        $p->NgatKetNoi($conn);
        return false;
    }

    public function GetComments($maBaiDang, $limit = 100, $offset = 0)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        
        $maBaiDang = (int)$maBaiDang;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT bl.*, 
                COALESCE(hs.hoTen, 'User') as hoTen, 
                COALESCE(hs.avatar, '') as avatar 
                FROM binhluan bl
                LEFT JOIN hosonguoidung hs ON bl.maNguoiDung = hs.maNguoiDung
                WHERE bl.maBaiDang = $maBaiDang AND bl.trangThai = 1
                ORDER BY bl.thoiGianTao DESC
                LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($sql);
        $comments = [];
        
        if ($result === false) {
            error_log("SQL Error in GetComments: " . $conn->error);
            error_log("SQL Query: " . $sql);
        } elseif ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Nếu không có avatar thì trả về default.png
                if (empty($row['avatar'])) {
                    $row['avatar'] = 'default.png';
                }
                $comments[] = $row;
            }
        }
        
        $p->NgatKetNoi($conn);
        return $comments;
    }

    public function CountComments($maBaiDang)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        
        $maBaiDang = (int)$maBaiDang;

        $sql = "SELECT COUNT(*) as total FROM binhluan WHERE maBaiDang = $maBaiDang AND trangThai = 1";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        $p->NgatKetNoi($conn);
        return (int)($row['total'] ?? 0);
    }
}
