<?php
require_once("model/mTim.php");

class cTim
{
    /**
     * Thích người dùng
     */
    public function likeUser($uid1, $uid2)
    {
        // Kiểm tra không thể tự thích mình
        if ($uid1 == $uid2) {
            return ['success' => false, 'message' => 'Không thể thích chính mình!'];
        }

        $p = new mTim();

        // Kiểm tra đã thích chưa
        if ($p->checkLiked($uid1, $uid2)) {
            return ['success' => false, 'message' => 'Bạn đã thích người này rồi!'];
        }

        // Kiểm tra xem người kia đã thích mình chưa (uid2 -> uid1)
        if ($p->checkLiked($uid2, $uid1)) {
            // Người kia đã thích mình rồi -> Chỉ cập nhật bản ghi cũ thành 'ghep'
            // KHÔNG tạo bản ghi mới
            $p->UpdateToMatch($uid1, $uid2);
            return ['success' => true, 'message' => 'Ghép đôi thành công! 💑'];
        }

        // Người kia chưa thích mình -> Tạo bản ghi mới với status 'thich'
        $result = $p->InsertUser($uid1, $uid2, 'thich');

        if ($result) {
            return ['success' => true, 'message' => 'Đã thích thành công! ❤️'];
        }

        return ['success' => false, 'message' => 'Lỗi khi thích người dùng!'];
    }

    /**
     * Bỏ thích người dùng
     */
    public function unlikeUser($uid1, $uid2)
    {
        // Kiểm tra không thể bỏ thích chính mình
        if ($uid1 == $uid2) {
            return ['success' => false, 'message' => 'Không thể bỏ thích chính mình!'];
        }

        $p = new mTim();

        // Kiểm tra đã thích chưa
        if (!$p->checkLiked($uid1, $uid2)) {
            return ['success' => false, 'message' => 'Bạn chưa thích người này!'];
        }

        // Xóa khỏi database
        $result = $p->DeleteLike($uid1, $uid2);

        if ($result) {
            return ['success' => true, 'message' => 'Đã bỏ thích thành công!'];
        }

        return ['success' => false, 'message' => 'Lỗi khi bỏ thích người dùng!'];
    }

    /**
     * Xóa quan hệ giữa 2 người (dùng cho người được thích bấm bỏ thích)
     */
    public function removeRelation($uid1, $uid2)
    {
        // Kiểm tra không thể xóa quan hệ với chính mình
        if ($uid1 == $uid2) {
            return ['success' => false, 'message' => 'Không thể xóa quan hệ với chính mình!'];
        }

        $p = new mTim();

        // Xóa quan hệ (cả 2 chiều)
        $result = $p->DeleteRelation($uid1, $uid2);

        if ($result) {
            return ['success' => true, 'message' => 'Đã xóa thành công!'];
        }

        return ['success' => false, 'message' => 'Lỗi khi xóa quan hệ!'];
    }

    /**
     * Kiểm tra đã thích chưa
     */
    public function checkLiked($uid1, $uid2)
    {
        $p = new mTim();
        return $p->checkLiked($uid1, $uid2);
    }
    /**
     * Lấy danh sách người mình đã thích
     */
    public function GetMyLikedUsers($userId)
    {
        $p = new mTim();
        return $p->GetMyLikedUsers($userId);
    }

    /**
     * Lấy danh sách người dùng đã thích mình
     */
    public function GetAllUserLike($userId)
    {
        $p = new mTim();
        return $p->GetAllUserLike($userId);
    }
}
