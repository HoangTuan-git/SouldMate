<?php
include_once("model/mTinNhan.php");
class controlTinNhan{
    public function getAllMessages($uid1, $uid2){
        $p = new modelTinNhan();
        return $p->mGetAllMessage($uid1, $uid2);
    }
    public function sendMessage($uid1, $uid2, $noidung){
        $p = new modelTinNhan();
        return $p->mSendMessage($uid1, $uid2, $noidung);
    }
    public function getLastMessage($uid1, $uid2){
        $p = new modelTinNhan();
        return $p->mGetLastMessage($uid1, $uid2);
    }
    public function getLastMessageId($uid1, $uid2){
        $p = new modelTinNhan();
        return $p->mGetLastMessageId($uid1, $uid2);
    }
    public function getAllFriends($uid){
        $p = new modelTinNhan();
        return $p->mGetAllFriend($uid);
    }

    public function checkBlocked($uid1, $uid2){
        $p = new modelTinNhan();
        return $p->mCheckBlocked($uid1, $uid2);
    }
}
?>