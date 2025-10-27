<?php
include_once('model/mxuly.php');
class Cxuly
{
    public function GetAllUserLike()
    {
        $p = new Mxuly();
        $tblUserLikes = $p->GetAllUserLike();
        return $tblUserLikes;
    }
}
