<?php
class mKetNoi
{
    public function KetNoi()
    {
        $user = 'root';
        $host = 'localhost';
        $pass = '';
        $db = 'db_dating_final';
        return mysqli_connect($host, $user, $pass, $db);
    }
    public function NgatKetNoi($conn)
    {
        $conn->close();
    }
}
