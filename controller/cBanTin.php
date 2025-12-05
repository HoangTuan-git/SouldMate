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
    public function cAddTinTuc($user_id, $text, $image)
    {
        $text = trim($text);
        
        // Kiểm tra có file thật sự hay không
        $hasFiles = false;
        if (isset($image['name']) && is_array($image['name'])) {
            foreach ($image['name'] as $fileName) {
                if (!empty(trim($fileName))) {
                    $hasFiles = true;
                    break;
                }
            }
        }
        
        // LOGIC ĐÚNG: Phải có text HOẶC file
        if (empty($text) && !$hasFiles) {
            return '1';
        }

        $hinhs = '';
        if ($hasFiles) {
            $uploader = new CUpload();
            $res = $uploader->processImagesOnly($image);

            if (!$res['success']) {
                return $res['error']; // '2' | '3' | '4'
            }
            
            $hinhs = $res['hinhs'];
        }

        $p = new mBanTin();
        $kq = $p->mAddTinTuc($user_id, $text, $hinhs);

        // Giữ nguyên: thành công -> '5'
        return '5';
    }

    public function cDeleteTinTuc($postId, $userId)
    {
        // Kiểm tra tham số
        if (empty($postId) || empty($userId)) {
            return false;
        }
        
        $p = new mBanTin();
        $kq = $p->mDeleteTinTuc($postId, $userId);
        return $kq;
    }
}
