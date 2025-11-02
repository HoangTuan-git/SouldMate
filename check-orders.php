<?php
require_once(__DIR__ . '/model/mKetNoi.php');

$conn = new mKetNoi();
$con = $conn->KetNoi();

if ($con) {
    echo "<h2>Check đơn hàng trong database:</h2>";
    
    $result = $con->query("SELECT * FROM donhang ORDER BY ngayTao DESC LIMIT 5");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Mã ĐH</th><th>User</th><th>Gói</th><th>Tiền</th><th>Trạng thái</th><th>Ngày tạo</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['maDonHang']) . "</td>";
            echo "<td>" . $row['maNguoiDung'] . "</td>";
            echo "<td>" . htmlspecialchars($row['loaiGoi']) . "</td>";
            echo "<td>" . number_format($row['tongTien']) . "</td>";
            echo "<td>" . $row['trangThai'] . "</td>";
            echo "<td>" . $row['ngayTao'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color:red;'>Không có đơn hàng nào!</p>";
    }
    
    echo "<hr>";
    echo "<h2>Check bảng premium:</h2>";
    
    $result2 = $con->query("SELECT * FROM premium ORDER BY ngayTao DESC LIMIT 5");
    
    if ($result2 && $result2->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Mã ĐH</th><th>User</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Trạng thái</th></tr>";
        
        while ($row = $result2->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['maDonHang']) . "</td>";
            echo "<td>" . $row['maNguoiDung'] . "</td>";
            echo "<td>" . $row['ngayBatDau'] . "</td>";
            echo "<td>" . $row['ngayKetThuc'] . "</td>";
            echo "<td>" . $row['trangThai'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Chưa có Premium nào được kích hoạt</p>";
    }
    
    $con->close();
} else {
    echo "Không kết nối được database!";
}
?>
