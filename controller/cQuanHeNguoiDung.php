<?php
include_once('model/mQuanHeNguoiDung.php');
include_once('model/mBaoCaoViPham.php');

class controlQuanHeNguoiDung {
    
    /**
     * Chặn người dùng
     */
    public function blockUser($maNguoiDung1, $maNguoiDung2) {
        $model = new modelQuanHeNguoiDung();
        return $model->blockUser($maNguoiDung1, $maNguoiDung2);
    }
    
    /**
     * Bỏ chặn người dùng
     */
    public function unblockUser($maNguoiDung1, $maNguoiDung2) {
        $model = new modelQuanHeNguoiDung();
        return $model->unblockUser($maNguoiDung1, $maNguoiDung2);
    }
    
    /**
     * Báo cáo vi phạm
     */
    public function reportUser($maNguoiDung1, $maNguoiDung2, $lyDo, $urlNoiDung = '') {
        $model = new modelBaoCaoViPham();
        return $model->createReport($maNguoiDung1, $maNguoiDung2, $lyDo, $urlNoiDung);
    }
}
?>
