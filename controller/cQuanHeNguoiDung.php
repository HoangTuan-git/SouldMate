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
     * Báo cáo người dùng
     */
    public function reportUser($maNguoiBaoCao, $maNguoiDungBiBaoCao, $lyDo) {
        $model = new modelBaoCaoViPham();
        return $model->createUserReport($maNguoiBaoCao, $maNguoiDungBiBaoCao, $lyDo);
    }

    /**
     * Báo cáo bài viết
     */
    public function reportPost($maNguoiBaoCao, $maBaiDang, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham = '') {
        $model = new modelBaoCaoViPham();
        return $model->createPostReport($maNguoiBaoCao, $maBaiDang, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham);
    }

    /**
     * Báo cáo tin nhắn (với context)
     */
    public function reportMessage($maNguoiBaoCao, $maTinNhan, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham, $maNguoiDung1, $maNguoiDung2) {
        $model = new modelBaoCaoViPham();
        
        // Lấy context tin nhắn (10 tin nhắn trước và sau)
        $contextTinNhan = $model->getMessageContext($maTinNhan, $maNguoiDung1, $maNguoiDung2, 10);
        
        if (!$contextTinNhan) {
            return false;
        }

        // Lấy thời gian của tin nhắn bị báo cáo
        include_once('model/mTinNhan.php');
        $mTinNhan = new modelTinNhan();
        $thoiGianTinNhan = $mTinNhan->getMessageTime($maTinNhan);
        
        return $model->createMessageReport(
            $maNguoiBaoCao, 
            $maTinNhan, 
            $maNguoiDungBiBaoCao, 
            $lyDo, 
            $noiDungViPham, 
            $contextTinNhan,
            $thoiGianTinNhan
        );
    }

    /**
     * Lấy danh sách người dùng bị chặn
     */
    public function getBlockedUsers($maNguoiDung) {
        $model = new modelQuanHeNguoiDung();
        return $model->getBlockedUsers($maNguoiDung);
    }
}
?>
