<?php
include_once('model/mKetNoi.php');

class modelHoSo
{
    private function execQuery($query)
    {
        $p = new mKetNoi();
        $conn = $p->KetNoi();
        if ($conn) {
            $result = $conn->query($query);
            $p->NgatKetNoi($conn);
            return $result;
        } else {
            $p->NgatKetNoi($conn);
            return false;
        }
    }

    /**
     * Kiểm tra user đã có hồ sơ chưa
     */
    public function hasProfile($maNguoiDung)
    {
        $query = "SELECT maHoSo, hoTen FROM hosonguoidung WHERE maNguoiDung = $maNguoiDung";
        $result = $this->execQuery($query);
        return $result;
    }

    /**
     * Lấy hồ sơ theo maNguoiDung
     */
    public function getProfileByUserId($maNguoiDung)
    {
        $query = "SELECT h.*, tp.tenThanhPho, nnh.tenNganh, nn.tenNgheNghiep,
                  GROUP_CONCAT(st.tenSoThich SEPARATOR ', ') AS soThich
                  FROM hosonguoidung h
                  LEFT JOIN thanhpho tp ON h.maThanhPho = tp.maThanhPho
                  LEFT JOIN nghenghiep nn ON h.maNgheNghiep = nn.maNgheNghiep
                  LEFT JOIN nganhnghe nnh ON nn.maNganh = nnh.maNganh
                  LEFT JOIN hoso_sothich hst ON h.maHoSo = hst.maHoSo
                  LEFT JOIN sothich st ON hst.maSoThich = st.maSoThich
                  WHERE h.maNguoiDung = $maNguoiDung
                  GROUP BY h.maHoSo";
        $result = $this->execQuery($query);
        return $result;
    }

    /**
     * Lấy hồ sơ theo maHoSo
     */
    public function getProfileById($maHoSo)
    {
        $query = "SELECT h.*, tp.tenThanhPho, nn.tenNgheNghiep,
                  GROUP_CONCAT(st.tenSoThich SEPARATOR ', ') AS soThich
                  FROM hosonguoidung h
                  LEFT JOIN thanhpho tp ON h.maThanhPho = tp.maThanhPho
                  LEFT JOIN nghenghiep nn ON h.maNgheNghiep = nn.maNgheNghiep
                  LEFT JOIN hoso_sothich hst ON h.maHoSo = hst.maHoSo
                  LEFT JOIN sothich st ON hst.maSoThich = st.maSoThich
                  WHERE h.maHoSo = $maHoSo
                  GROUP BY h.maHoSo";
        $result = $this->execQuery($query);
        return $result;
    }

    /**
     * Tạo hồ sơ mới
     */
    public function createProfile($maNguoiDung, $hoTen, $ngaySinh, $gioiTinh, $maNgheNghiep, $maThanhPho, $moTa, $avatar, $trangThaiHenHo)
    {
        $hoSo_unique_id = 'profile_' . uniqid('', true);

        $query = "INSERT INTO hosonguoidung (hoSo_unique_id, maNguoiDung, hoTen, ngaySinh, gioiTinh, maNgheNghiep, maThanhPho, moTa, avatar, trangThaiHenHo) 
                  VALUES ('$hoSo_unique_id', $maNguoiDung, '$hoTen', '$ngaySinh', '$gioiTinh', " .
            ($maNgheNghiep ? $maNgheNghiep : 'NULL') . ", " .
            ($maThanhPho ? $maThanhPho : 'NULL') . ", '$moTa', '$avatar', '$trangThaiHenHo')";

        $result = $this->execQuery($query);

        if ($result) {
            // Lấy maHoSo vừa tạo
            $query = "SELECT maHoSo FROM hosonguoidung WHERE hoSo_unique_id = '$hoSo_unique_id'";
            $result = $this->execQuery($query);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['maHoSo'];
            }
        }
        return false;
    }

    /**
     * Cập nhật hồ sơ
     */
    public function updateProfile($maHoSo, $hoTen, $ngaySinh, $gioiTinh, $maNgheNghiep, $maThanhPho, $moTa, $avatar = null, $trangThaiHenHo = 'trainghiem')
    {
        $query = "UPDATE hosonguoidung SET 
                  hoTen = '$hoTen',
                  ngaySinh = '$ngaySinh',
                  gioiTinh = '$gioiTinh',
                  maNgheNghiep = " . ($maNgheNghiep ? $maNgheNghiep : 'NULL') . ",
                  maThanhPho = " . ($maThanhPho ? $maThanhPho : 'NULL') . ",
                  moTa = '$moTa',
                  trangThaiHenHo = '$trangThaiHenHo'";

        if ($avatar) {
            $query .= ", avatar = '$avatar'";
        }

        $query .= " WHERE maHoSo = $maHoSo";

        return $this->execQuery($query);
    }

    /**
     * Thêm sở thích cho hồ sơ
     */
    public function addHobby($maHoSo, $maSoThich)
    {
        $query = "INSERT INTO hoso_sothich (maHoSo, maSoThich) VALUES ($maHoSo, $maSoThich)";
        return $this->execQuery($query);
    }

    /**
     * Xóa tất cả sở thích của hồ sơ
     */
    public function removeAllHobbies($maHoSo)
    {
        $query = "DELETE FROM hoso_sothich WHERE maHoSo = $maHoSo";
        return $this->execQuery($query);
    }

    /**
     * Lấy sở thích của hồ sơ
     */
    public function getHobbies($maHoSo)
    {
        $query = "SELECT st.* FROM sothich st
                  INNER JOIN hoso_sothich hs ON st.maSoThich = hs.maSoThich
                  WHERE hs.maHoSo = $maHoSo";
        return $this->execQuery($query);
    }

    /**
     * Lấy tất cả thành phố
     */
    public function getAllCities()
    {
        $query = "SELECT * FROM thanhpho ORDER BY tenThanhPho ASC";
        return $this->execQuery($query);
    }

    /**
     * Lấy tất cả ngành nghề
     */
    public function getAllIndustries()
    {
        $query = "SELECT * FROM nganhnghe ORDER BY tenNganh ASC";
        return $this->execQuery($query);
    }

    /**
     * Lấy nghề nghiệp theo ngành
     */
    public function getJobsByIndustry($maNganh)
    {
        $query = "SELECT * FROM nghenghiep WHERE maNganh = $maNganh ORDER BY tenNgheNghiep ASC";
        return $this->execQuery($query);
    }

    /**
     * Lấy tất cả nghề nghiệp
     */
    public function getAllJobs()
    {
        $query = "SELECT nn.*, nnh.tenNganh FROM nghenghiep nn
                  INNER JOIN nganhnghe nnh ON nn.maNganh = nnh.maNganh
                  ORDER BY nnh.tenNganh, nn.tenNgheNghiep ASC";
        return $this->execQuery($query);
    }

    /**
     * Lấy tất cả sở thích
     */
    public function getAllHobbies()
    {
        $query = "SELECT * FROM sothich ORDER BY tenSoThich ASC";
        return $this->execQuery($query);
    }
    public function checkHoSoExists($maNguoiDung)
    {
        $query = "SELECT h.*, 
                  tp.tenThanhPho, 
                  nn.tenNgheNghiep AS tenNgheNghiep,
                  GROUP_CONCAT(st.tenSoThich SEPARATOR ', ') AS soThich
                  FROM hosonguoidung h
                  LEFT JOIN thanhpho tp ON h.maThanhPho = tp.maThanhPho
                  LEFT JOIN nghenghiep nn ON h.maNgheNghiep = nn.maNgheNghiep
                  LEFT JOIN hoso_sothich hst ON h.maHoSo = hst.maHoSo
                  LEFT JOIN sothich st ON hst.maSoThich = st.maSoThich
                  WHERE h.maNguoiDung = $maNguoiDung
                  GROUP BY h.maHoSo";
        $result = $this->execQuery($query);
        return $result;
    }
}
