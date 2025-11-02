<?php
include_once(__DIR__ . '/../model/mBinhLuan.php');

class cBinhLuan
{
    private $model;
    
    public function __construct()
    {
        $this->model = new mBinhLuan();
    }
    
    public function AddComment($maNguoiDung, $maBaiDang, $noiDung)
    {
        if (empty(trim($noiDung))) {
            return false;
        }
        return $this->model->AddComment($maNguoiDung, $maBaiDang, $noiDung);
    }

    public function DeleteComment($maBinhLuan, $maNguoiDung)
    {
        return $this->model->DeleteComment($maBinhLuan, $maNguoiDung);
    }

    public function GetComments($maBaiDang, $limit = 100, $offset = 0)
    {
        return $this->model->GetComments($maBaiDang, $limit, $offset);
    }
    
    public function CountComments($maBaiDang)
    {
        return $this->model->CountComments($maBaiDang);
    }
}
