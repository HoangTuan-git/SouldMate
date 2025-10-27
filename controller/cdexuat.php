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
    public function GetAllUser()
    {
        $p = new Mdexuat();
        $tblUser = $p->GetAllUserByDeXuat();
        return $tblUser;
    }

    public function InsertUser($uid1, $uid2, $status, $first_like)
    {
        $p = new Mdexuat();
        $tblInsert = $p->InsertUser($uid1, $uid2, $status, $first_like);
        return $tblInsert;
    }
    public function HasLiked($uid1, $uid2)
    {
        $p = new Mdexuat();
        return $p->HasLiked($uid1, $uid2);
    }
}
