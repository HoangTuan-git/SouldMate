# Cập nhật: Kiểm tra trạng thái khóa và xử lý báo cáo

## ✅ Các vấn đề đã sửa:

### 1. **Kiểm tra trạng thái đã khóa hay chưa**
- ✅ Thêm cột `trangThaiViPham` vào query danh sách vi phạm
- ✅ Hiển thị badge trạng thái: "Đã khóa" (đỏ) hoặc "Hoạt động" (xanh)
- ✅ Ẩn nút "Khóa tài khoản" nếu đã bị khóa
- ✅ Kiểm tra trong model trước khi khóa để tránh khóa lại

### 2. **Cập nhật trạng thái báo cáo khi khóa**
- ✅ Thêm query UPDATE `baocaovipham` trong transaction
- ✅ Chuyển tất cả báo cáo `dangxuly` → `daxuly` khi khóa
- ✅ Message thông báo đã cập nhật

---

## 📝 Chi tiết thay đổi:

### Model (`model/mBaoCaoViPham.php`):

#### 1. `getUsersWithManyReports($minReports = 15)`
**Trước:**
```sql
SELECT nd.maNguoiDung, h.hoTen, COUNT(bc.maBaoCao) as soLanBaoCao
FROM nguoidung nd...
GROUP BY nd.maNguoiDung, h.hoTen
```

**Sau:**
```sql
SELECT nd.maNguoiDung, nd.trangThaiViPham, h.hoTen, COUNT(bc.maBaoCao) as soLanBaoCao
FROM nguoidung nd...
GROUP BY nd.maNguoiDung, nd.trangThaiViPham, h.hoTen
```

#### 2. `searchViolatingUsers($keyword, $minReports = 15)`
- Tương tự thêm `nd.trangThaiViPham` vào SELECT và GROUP BY

#### 3. `lockAccount($maNguoiDung, $lyDo)`
**Thêm logic:**
```php
// Kiểm tra trạng thái trước khi khóa
$checkSql = "SELECT trangThaiViPham FROM nguoidung WHERE maNguoiDung = ?";
if ($userData['trangThaiViPham'] == 'khoa') {
    return false; // Đã khóa rồi
}

// Trong transaction, thêm bước 3:
// 1. UPDATE nguoidung SET trangThaiViPham = 'khoa'
// 2. INSERT INTO lichsuvipham
// 3. UPDATE baocaovipham SET trangThai = 'daxuly' WHERE maNguoiDungBiBaoCao = ? AND trangThai = 'dangxuly'
```

---

### Controller (`controller/cAdmin.php`):

#### `khoaTaiKhoan($maNguoiDung, $lyDo)`
**Message cập nhật:**
```php
// Thành công
'Đã khóa tài khoản thành công và cập nhật trạng thái các báo cáo!'

// Thất bại
'Tài khoản đã bị khóa hoặc có lỗi xảy ra!'
```

---

### View (`view/quanLyViPham.php`):

#### Thêm cột "Trạng thái" vào table:
```html
<th scope="col">Trạng thái</th>
```

#### Hiển thị badge trạng thái:
```php
<?php if ($row['trangThaiViPham'] == 'khoa'): ?>
    <span class="badge bg-danger">
        <i class="bi bi-lock-fill"></i> Đã khóa
    </span>
<?php else: ?>
    <span class="badge bg-success">
        <i class="bi bi-check-circle-fill"></i> Hoạt động
    </span>
<?php endif; ?>
```

#### Hiển thị nút có điều kiện:
```php
<?php if ($row['trangThaiViPham'] != 'khoa'): ?>
    <form method="POST">
        <button type="submit" name="lockAccount" class="btn btn-danger btn-sm">
            <i class="bi bi-lock"></i> Khóa tài khoản
        </button>
    </form>
<?php else: ?>
    <span class="text-muted small">Đã khóa</span>
<?php endif; ?>
```

---

## 🎯 Luồng hoạt động khi khóa tài khoản:

1. **Admin click "Khóa tài khoản"**
2. **Kiểm tra**: Tài khoản đã bị khóa chưa?
   - Nếu đã khóa → return false
   - Nếu chưa → tiếp tục
3. **Transaction bắt đầu**:
   ```sql
   -- Step 1: Khóa tài khoản
   UPDATE nguoidung SET trangThaiViPham = 'khoa' WHERE maNguoiDung = X;
   
   -- Step 2: Ghi lịch sử
   INSERT INTO lichsuvipham (maNguoiDung, hanhDong) 
   VALUES (X, 'Khóa tài khoản: Vi phạm nhiều lần');
   
   -- Step 3: Cập nhật báo cáo
   UPDATE baocaovipham 
   SET trangThai = 'daxuly' 
   WHERE maNguoiDungBiBaoCao = X AND trangThai = 'dangxuly';
   ```
4. **Commit** (hoặc Rollback nếu lỗi)
5. **Hiển thị message**: "Đã khóa tài khoản thành công và cập nhật trạng thái các báo cáo!"
6. **Refresh trang**: Badge hiển thị "Đã khóa", nút "Khóa tài khoản" biến mất

---

## 🔍 Ví dụ trước/sau:

### Trước khi khóa:
| ID | Tên | Số báo cáo | Trạng thái | Hành động |
|----|-----|-----------|-----------|-----------|
| MS010 | User A | 20 báo cáo | ✅ Hoạt động | [Xem chi tiết] [Khóa tài khoản] |

**Trạng thái báo cáo trong DB:**
```
maBaoCao | maNguoiDungBiBaoCao | trangThai
1        | 10                  | dangxuly
2        | 10                  | dangxuly
3        | 10                  | dangxuly
```

### Sau khi khóa:
| ID | Tên | Số báo cáo | Trạng thái | Hành động |
|----|-----|-----------|-----------|-----------|
| MS010 | User A | 20 báo cáo | 🔒 Đã khóa | [Xem chi tiết] Đã khóa |

**Trạng thái báo cáo trong DB:**
```
maBaoCao | maNguoiDungBiBaoCao | trangThai
1        | 10                  | daxuly
2        | 10                  | daxuly
3        | 10                  | daxuly
```

---

## ✨ Lợi ích:

1. **Không khóa trùng**: Kiểm tra trước khi khóa tránh thao tác trùng
2. **UI rõ ràng**: Badge màu sắc giúp phân biệt trạng thái ngay lập tức
3. **Tránh lỗi**: Ẩn nút khóa với tài khoản đã khóa
4. **Quản lý báo cáo**: Tự động đánh dấu các báo cáo là đã xử lý
5. **Transaction safe**: Rollback nếu có lỗi ở bất kỳ bước nào
6. **Audit trail**: Vẫn giữ lịch sử đầy đủ trong `lichsuvipham`

---

## 📂 Files đã thay đổi:

1. ✅ `model/mBaoCaoViPham.php`
   - `getUsersWithManyReports()` - Thêm `trangThaiViPham`
   - `searchViolatingUsers()` - Thêm `trangThaiViPham`
   - `lockAccount()` - Thêm check + cập nhật báo cáo

2. ✅ `controller/cAdmin.php`
   - `khoaTaiKhoan()` - Cập nhật message

3. ✅ `view/quanLyViPham.php`
   - Thêm cột "Trạng thái"
   - Badge trạng thái với icon
   - Nút khóa có điều kiện
   - Icon cho nút khóa

---

## 🚀 Sẵn sàng!

Tính năng đã hoàn chỉnh với:
- ✅ Kiểm tra trạng thái trước khi khóa
- ✅ Hiển thị trạng thái trực quan
- ✅ Ẩn nút khóa khi đã khóa
- ✅ Tự động cập nhật trạng thái báo cáo
- ✅ Transaction đảm bảo dữ liệu nhất quán
