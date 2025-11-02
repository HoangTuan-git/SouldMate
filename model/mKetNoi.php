<?php
class mKetNoi
{
    public function KetNoi()
    {
        $user = 'root';
        $host = 'localhost';
        $pass = '';
        $db = 'db_dating_final';
        $conn = mysqli_connect($host, $user, $pass, $db);
        
        mysqli_set_charset($conn, "utf8mb4");
        return $conn;
    }
    public function NgatKetNoi($conn)
    {
        if ($conn) {
            $conn->close();
        }
    }
}
