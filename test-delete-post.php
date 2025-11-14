<?php
// Test script để kiểm tra xóa bài viết và cập nhật trạng thái báo cáo

include_once('model/mKetNoi.php');

// Kết nối database
$conn = new mKetNoi();
$con = $conn->KetNoi();

if (!$con) {
    die("Không thể kết nối database!");
}

echo "<h2>Test: Kiểm tra cấu trúc bảng</h2>";

// 1. Kiểm tra bảng baidang
echo "<h3>1. Cấu trúc bảng baidang:</h3>";
$result = $con->query("DESCRIBE baidang");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Lỗi: " . $con->error . "</p>";
}

// 2. Kiểm tra bảng baocaovipham
echo "<h3>2. Cấu trúc bảng baocaovipham:</h3>";
$result = $con->query("DESCRIBE baocaovipham");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Lỗi: " . $con->error . "</p>";
}

// 3. Kiểm tra các báo cáo bài đăng hiện có
echo "<h3>3. Báo cáo bài đăng trong DB:</h3>";
$result = $con->query("SELECT maBaoCao, maBaiDang, trangThai, lyDo FROM baocaovipham WHERE loaiBaoCao = 'baidang' LIMIT 10");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Mã BC</th><th>Mã Bài Đăng</th><th>Trạng Thái</th><th>Lý Do</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['maBaoCao']}</td>";
        echo "<td>{$row['maBaiDang']}</td>";
        echo "<td>{$row['trangThai']}</td>";
        echo "<td>{$row['lyDo']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Lỗi: " . $con->error . "</p>";
}

// 4. Test UPDATE query
echo "<h3>4. Test câu lệnh UPDATE:</h3>";
$testMaBaiDang = 1; // Thay đổi số này theo bài đăng thực tế
$testLyDo = "Test update";

$sqlUpdate = "UPDATE baocaovipham 
             SET trangThai = 'daxuly', 
                 noiDungViPham = CONCAT(IFNULL(noiDungViPham, ''), '\n[Admin đã xóa bài viết: ', ?, ']')
             WHERE maBaiDang = ? AND trangThai IN ('dangxuly', 'choxuly')";

echo "<pre>" . htmlspecialchars($sqlUpdate) . "</pre>";
echo "<p>Tham số: lyDo = '$testLyDo', maBaiDang = $testMaBaiDang</p>";

// Test prepared statement
$stmtTest = $con->prepare($sqlUpdate);
if ($stmtTest) {
    echo "<p style='color: green;'>✓ Prepared statement OK</p>";
    $stmtTest->bind_param("si", $testLyDo, $testMaBaiDang);
    echo "<p style='color: green;'>✓ Bind param OK</p>";
    // Không execute để không thay đổi dữ liệu thật
    echo "<p><strong>Note:</strong> Không execute để tránh thay đổi dữ liệu</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi prepare: " . $con->error . "</p>";
}

$con->close();

echo "<hr>";
echo "<h3>Hướng dẫn:</h3>";
echo "<ol>";
echo "<li>Kiểm tra xem tên cột trong bảng <code>baidang</code> có đúng không</li>";
echo "<li>Kiểm tra xem tên cột trong bảng <code>baocaovipham</code> có đúng không</li>";
echo "<li>Xác nhận có báo cáo nào đang ở trạng thái <code>dangxuly</code> không</li>";
echo "<li>Kiểm tra prepared statement có lỗi không</li>";
echo "</ol>";
?>
