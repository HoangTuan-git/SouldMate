<?php
include_once('mKetNoi.php');
class mBanTin
{
    public function mGetAllTinTuc()
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $query = "SELECT 
                    bd.*,
                    hs.hoTen,
                    hs.avatar
                  FROM baidang bd
                  LEFT JOIN nguoidung nd ON bd.maNguoiDung = nd.maNguoiDung
                  LEFT JOIN hosonguoidung hs ON nd.maNguoiDung = hs.maNguoiDung
                  ORDER BY bd.ngayTao DESC";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }

    public function mAddTinTuc($user_id, $text, $image)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        $text = $conn->real_escape_string($text);
        $image = $conn->real_escape_string($image);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $date = date('Y-m-d H:i:s');
        $query = "INSERT INTO baidang (maNguoiDung ,noidungText, noidungAnh, ngayTao) VALUES ('$user_id', '$text', '$image', '$date')";
        $kq = $conn->query($query);
        $p->NgatKetNoi($conn);
        return $kq;
    }

    public function mDeleteTinTuc($postId, $userId)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        
        // Kiểm tra quyền sở hữu bài viết
        $checkQuery = "SELECT maNguoiDung, noidungAnh FROM baidang WHERE maBaiDang = $postId";
        $result = $conn->query($checkQuery);
        
        if ($result && $result->num_rows > 0) {
            $post = $result->fetch_assoc();
            
            // Chỉ cho phép xóa nếu là chủ bài viết
            if ($post['maNguoiDung'] != $userId) {
                $p->NgatKetNoi($conn);
                return false;
            }
            
            // Xóa bài viết (các bình luận và tương tác sẽ tự động xóa nếu có foreign key cascade)
            $deleteQuery = "DELETE FROM baidang WHERE maBaiDang = $postId";
            $kq = $conn->query($deleteQuery);
            
            // Xóa file ảnh nếu có
            if ($kq && !empty($post['noidungAnh'])) {
                $images = explode(',', $post['noidungAnh']);
                foreach ($images as $image) {
                    $imagePath = __DIR__ . '/../img/' . trim($image);
                    if (file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }
            }
            
            $p->NgatKetNoi($conn);
            return $kq;
        }
        
        $p->NgatKetNoi($conn);
        return false;
    }
}
