<?php
include_once('mKetNoi.php');

class modelBaoCaoViPham
{

    /**
     * Tạo báo cáo vi phạm
     */
    public function createReport($maNguoiDung1, $maNguoiDung2, $lyDo, $urlNoiDung = '')
    {
        $conn = new mKetNoi();
        $con = $conn->KetNoi();

        if ($con) {
            $sql = "INSERT INTO baocaovipham (maNguoiDung1, maNguoiDung2, lyDo, urlNoiDung, ngayTao) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iiss", $maNguoiDung1, $maNguoiDung2, $lyDo, $urlNoiDung);
            $kq = $stmt->execute();

            $con->close();
            return $kq;
        }
        return false;
    }
}
