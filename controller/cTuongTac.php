<?php
include_once('model/mTuongTac.php');

class cTuongTac
{
    // Toggle like/unlike
    public function ToggleLike($maNguoiDung, $maBaiDang)
    {
        $model = new mTuongTac();

        // Kiểm tra đã like chưa
        $isLiked = $model->CheckUserLiked($maNguoiDung, $maBaiDang);

        if ($isLiked) {
            // Nếu đã like thì unlike
            $result = $model->RemoveLike($maNguoiDung, $maBaiDang);
            $action = 'unliked';
        } else {
            // Nếu chưa like thì like
            $result = $model->AddLike($maNguoiDung, $maBaiDang);
            $action = 'liked';
        }

        // Lấy số lượt like mới
        $likeCount = $model->CountLikes($maBaiDang);

        return [
            'success' => $result,
            'action' => $action,
            'likeCount' => $likeCount,
            'isLiked' => !$isLiked
        ];
    }

    // Lấy trạng thái like của user
    public function CheckLikeStatus($maNguoiDung, $maBaiDang)
    {
        $model = new mTuongTac();
        return $model->CheckUserLiked($maNguoiDung, $maBaiDang);
    }

    // Lấy số lượt like
    public function GetLikeCount($maBaiDang)
    {
        $model = new mTuongTac();
        return $model->CountLikes($maBaiDang);
    }
}
