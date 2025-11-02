<?php
require_once("model/mdexuat.php");
class Cdexuat
{
    public function GetAllKhuVuc()
    {
        $p = new Mdexuat();
        $tblKhuVuc = $p->GetAllKhuVuc();
        return $tblKhuVuc;
    }
    
    public function GetAllNgheNghiep()
    {
        $p = new Mdexuat();
        return $p->GetAllNgheNghiep();
    }
    
    public function GetAllSoThich()
    {
        $p = new Mdexuat();
        return $p->GetAllSoThich();
    }
    
    public function GetUserHobbies($uid)
    {
        $p = new Mdexuat();
        return $p->GetUserHobbies($uid);
    }
    
    /**
     * Lấy danh sách đề xuất với bộ lọc
     * @param array $filters - Mảng chứa các bộ lọc: ['thanhpho', 'tuoi_min', 'tuoi_max', 'nghenghiep']
     */
    public function GetAllUser($filters = [])
    {
        $p = new Mdexuat();
        $tblUser = $p->GetAllUserByDeXuat($filters);
        return $tblUser;
    }

    public function InsertUser($uid1, $uid2, $status)
    {
        $p = new Mdexuat();
        $tblInsert = $p->InsertUser($uid1, $uid2, $status);
        return $tblInsert;
    }
    
    public function HasLiked($uid1, $uid2)
    {
        $p = new Mdexuat();
        return $p->HasLiked($uid1, $uid2);
    }
}
