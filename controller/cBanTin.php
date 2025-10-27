<?php
include_once('model/mBanTin.php');
include_once('controller/CUpload.php'); // thêm dòng này để gọi CUpload
class cBanTin
{
    public function cGetAllTinTuc()
    {
        $p = new mBanTin();
        $kq = $p->mGetAllTinTuc();
        return $kq;
    }
    public function cAddTinTuc($user_id, $text, $image, $quyenRiengTu)
    {
        // GIỮ logic mã lỗi '1' (text rỗng + không có ảnh)
        if ($text == '' && count($image["name"]) == 0) {
            return '1';
        }
        $uploader = new CUpload();
        $res = $uploader->processImagesOnly($image);

        // Nếu upload fail -> trả đúng mã lỗi cũ
        if (!$res['success']) {
            return $res['error']; // '2' | '3' | '4'
        }

        // Thành công: lấy chuỗi $hinhs và lưu DB như cũ
        $hinhs = $res['hinhs'];

        $p = new mBanTin();
        $kq = $p->mAddTinTuc($user_id, $text, $hinhs, $quyenRiengTu);

        // Giữ nguyên: thành công -> '5'
        return '5';
    }
}
