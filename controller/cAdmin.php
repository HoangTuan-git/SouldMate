<?php
include_once(dirname(__DIR__) . '/model/mBaoCaoViPham.php');

class controlAdmin
{
    private $model;

    public function __construct()
    {
        $this->model = new modelBaoCaoViPham();
    }

    /**
     * Lấy danh sách người dùng vi phạm nhiều
     */
    public function getDanhSachViPham($minReports = 3)
    {
        return $this->model->getUsersWithManyReports($minReports);
    }

    /**
     * Lấy chi tiết báo cáo của người dùng
     */
    public function getChiTietBaoCao($maNguoiDung)
    {
        return $this->model->getReportDetailsByUser($maNguoiDung);
    }

    /**
     * Lấy danh sách tài khoản bị khóa
     */
    public function getDanhSachTaiKhoanKhoa()
    {
        return $this->model->getLockedAccounts();
    }

    /**
     * Khóa tài khoản với thời hạn
     * @param int $maNguoiDung
     * @param string $lyDo
     * @param int|null $soNgayKhoa - Số ngày khóa (null = vĩnh viễn)
     */
    public function khoaTaiKhoan($maNguoiDung, $lyDo, $soNgayKhoa = null)
    {
        if (empty($maNguoiDung) || empty($lyDo)) {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!'];
        }

        $result = $this->model->lockAccount($maNguoiDung, $lyDo, $soNgayKhoa);
        
        if ($result) {
            if ($soNgayKhoa === null) {
                $msg = 'Đã khóa tài khoản vĩnh viễn thành công!';
            } else {
                $msg = "Đã khóa tài khoản trong {$soNgayKhoa} ngày!";
            }
            return ['success' => true, 'message' => $msg];
        }
        
        return ['success' => false, 'message' => 'Tài khoản đã bị khóa hoặc có lỗi xảy ra!'];
    }

    /**
     * Tự động mở khóa các tài khoản đã hết hạn
     */
    public function autoUnlockExpiredAccounts()
    {
        return $this->model->autoUnlockExpiredAccounts();
    }

    /**
     * Mở khóa tài khoản
     */
    public function moKhoaTaiKhoan($maNguoiDung)
    {
        if (empty($maNguoiDung)) {
            return ['success' => false, 'message' => 'Không tìm thấy mã người dùng!'];
        }

        $result = $this->model->unlockAccount($maNguoiDung);
        
        if ($result) {
            return ['success' => true, 'message' => 'Đã mở khóa tài khoản thành công!'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi mở khóa tài khoản!'];
    }

    /**
     * Tìm kiếm người dùng vi phạm
     */
    public function timKiemViPham($keyword)
    {
        if (empty($keyword)) {
            return $this->getDanhSachViPham();
        }
        
        return $this->model->searchViolatingUsers($keyword);
    }

    /**
     * Tìm kiếm tài khoản bị khóa
     */
    public function timKiemTaiKhoanKhoa($keyword)
    {
        if (empty($keyword)) {
            return $this->getDanhSachTaiKhoanKhoa();
        }
        
        return $this->model->searchLockedAccounts($keyword);
    }

    /**
     * Lấy tất cả báo cáo
     */
    public function getAllReports($uid = null, $loaiBaoCao = null, $trangThai = null)
    {
        return $this->model->getReportsByType($uid, $loaiBaoCao, $trangThai);
    }

    /**
     * Cập nhật trạng thái báo cáo
     */
    public function updateReportStatus($maBaoCao, $trangThai)
    {
        return $this->model->updateReportStatus($maBaoCao, $trangThai);
    }

    /**
     * Lấy thông tin bài viết cho admin (để xem báo cáo)
     * Trả về post nếu còn tồn tại, null nếu đã xóa
     */
    public function getPostForAdmin($maBaiDang)
    {
        return $this->model->getPostById($maBaiDang);
    }

    /**
     * Xóa bài viết vi phạm
     */
    public function deleteViolatingPost($maBaiDang, $lyDo = '')
    {
        return $this->model->deletePost($maBaiDang, $lyDo);
    }
}
