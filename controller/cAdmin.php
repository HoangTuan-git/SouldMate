<?php
include_once('../model/mBaoCaoViPham.php');

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
    public function getDanhSachViPham($minReports = 15)
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
     * Khóa tài khoản
     */
    public function khoaTaiKhoan($maNguoiDung, $lyDo)
    {
        if (empty($maNguoiDung) || empty($lyDo)) {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!'];
        }

        $result = $this->model->lockAccount($maNguoiDung, $lyDo);
        
        if ($result) {
            return ['success' => true, 'message' => 'Đã khóa tài khoản thành công và cập nhật trạng thái các báo cáo!'];
        }
        
        return ['success' => false, 'message' => 'Tài khoản đã bị khóa hoặc có lỗi xảy ra!'];
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
}
