# 🎯 Hướng dẫn Cập nhật Chức năng Đề xuất Người dùng

## 📋 Tổng quan cập nhật

Đã cải tiến chức năng đề xuất người dùng với:
- ✅ Thuật toán matching thông minh dựa trên 4 yếu tố
- ✅ Bộ lọc nâng cao (khu vực, nghề nghiệp, độ tuổi)
- ✅ Hiển thị điểm tương thích (%)
- ✅ Hiển thị số sở thích chung
- ✅ Giao diện đẹp hơn với thông tin chi tiết

---

## 🔧 Các file đã thay đổi

### 1. Model Layer
**File: `model/mdexuat.php`**
- ✨ Thêm method `GetUserHobbies($uid)` - Lấy sở thích của user
- 🔄 Cập nhật `GetAllUserByDeXuat($filters)` - Hỗ trợ bộ lọc
- 📊 Cải tiến thuật toán tính điểm tương thích
- 🎯 Query tối ưu với JOIN và subquery

### 2. Controller Layer
**File: `controller/cdexuat.php`**
- ✨ Thêm method `GetAllNgheNghiep()` - Lấy danh sách nghề nghiệp
- ✨ Thêm method `GetAllSoThich()` - Lấy danh sách sở thích
- ✨ Thêm method `GetUserHobbies($uid)` - Lấy sở thích của user
- 🔄 Cập nhật `GetAllUser($filters)` - Nhận tham số bộ lọc

### 3. View Layer
**File: `view/dexuat.php`**
- 🎨 Giao diện mới với bộ lọc nâng cao
- 📊 Hiển thị % tương thích với màu sắc
- 💕 Hiển thị số sở thích chung
- 📍 Hiển thị thông tin nghề nghiệp, địa chỉ
- ✨ Animation và UX tốt hơn

### 4. Database
**File: `database_update_matching.sql`**
- 🗄️ Cấu trúc bảng đầy đủ
- 📊 Index tối ưu cho performance
- 🧪 Test query để kiểm tra
- 📝 Dữ liệu mẫu

### 5. Documentation
**File: `MATCHING_ALGORITHM.md`**
- 📖 Giải thích chi tiết thuật toán
- 📊 Ví dụ tính điểm cụ thể
- 💡 Lý do thiết kế

---

## 🚀 Hướng dẫn cài đặt

### Bước 1: Cập nhật Database

```bash
# Mở phpMyAdmin hoặc MySQL Workbench
# Chọn database: db_dating_final_v1
# Import file: database_update_matching.sql
```

Hoặc chạy bằng command line:
```bash
mysql -u root -p db_dating_final_v1 < database_update_matching.sql
```

### Bước 2: Kiểm tra cấu trúc bảng

Đảm bảo có đầy đủ các bảng:
- ✅ `nguoidung`
- ✅ `hosonguoidung`
- ✅ `sothich`
- ✅ `nghenghiep`
- ✅ `thanhpho`
- ✅ `nguoidung_sothich` (bảng trung gian)
- ✅ `quanhenguoidung`

### Bước 3: Kiểm tra dữ liệu

```sql
-- Kiểm tra có sở thích
SELECT * FROM sothich;

-- Kiểm tra có nghề nghiệp
SELECT * FROM nghenghiep;

-- Kiểm tra có thành phố
SELECT * FROM thanhpho;

-- Kiểm tra user có sở thích
SELECT * FROM nguoidung_sothich;
```

### Bước 4: Test chức năng

1. Đăng nhập vào hệ thống
2. Vào trang "Đề xuất" (`home.php?page=dexuat`)
3. Thử các bộ lọc:
   - Chọn khu vực
   - Chọn nghề nghiệp
   - Chọn độ tuổi
4. Xem kết quả hiển thị % tương thích

---

## 🎯 Cách sử dụng Bộ lọc

### 1. Không lọc (Mặc định)
```
Hiển thị tất cả người dùng theo thứ tự điểm tương thích
```

### 2. Lọc theo khu vực
```
Chỉ hiển thị người ở cùng thành phố được chọn
```

### 3. Lọc theo nghề nghiệp
```
Chỉ hiển thị người cùng nghề nghiệp được chọn
```

### 4. Lọc theo độ tuổi
```
Tuổi từ: 20
Tuổi đến: 30
→ Chỉ hiển thị người trong độ tuổi 20-30
```

### 5. Kết hợp nhiều bộ lọc
```
Khu vực: TPHCM
Nghề nghiệp: Developer
Tuổi từ: 22
Tuổi đến: 28
→ Tìm Developer 22-28 tuổi ở TPHCM
```

---

## 📊 Hiểu về Điểm Tương thích

### Màu sắc Badge

| Điểm | Màu | Ý nghĩa |
|------|-----|---------|
| 70-100% | 💗 Hồng gradient | Rất phù hợp |
| 40-69% | 🧡 Cam gradient | Khá phù hợp |
| 0-39% | 💙 Trắng | Ít phù hợp |

### Ví dụ Điểm cao

**User A và User B: 83% phù hợp**
- ✅ 3/5 sở thích chung = 21 điểm
- ✅ Cùng nghề Developer = 25 điểm
- ✅ Cùng TPHCM = 25 điểm
- ✅ Chênh 1 tuổi = 12 điểm
- 🏆 **Tổng: 83 điểm**

---

## 🐛 Xử lý lỗi thường gặp

### Lỗi 1: Không hiển thị người dùng
**Nguyên nhân:**
- Chưa có dữ liệu trong bảng `hosonguoidung`
- Người dùng hiện tại chưa có profile

**Giải pháp:**
```sql
-- Kiểm tra user có profile
SELECT * FROM hosonguoidung WHERE maNguoiDung = {your_user_id};

-- Nếu không có, tạo profile trước
```

### Lỗi 2: Không có sở thích chung
**Nguyên nhân:**
- Bảng `nguoidung_sothich` trống

**Giải pháp:**
```sql
-- Thêm sở thích cho user
INSERT INTO nguoidung_sothich (maNguoiDung, maSoThich) VALUES
(1, 1), -- User 1 thích Âm nhạc
(1, 2), -- User 1 thích Du lịch
(2, 2), -- User 2 thích Du lịch
(2, 3); -- User 2 thích Đọc sách
```

### Lỗi 3: Query chậm
**Nguyên nhân:**
- Thiếu index

**Giải pháp:**
```sql
-- Chạy lại phần tạo index trong database_update_matching.sql
```

---

## 🔧 Tùy chỉnh Thuật toán

### Thay đổi Trọng số

Mở file `model/mdexuat.php`, tìm đoạn tính điểm:

```php
// Hiện tại:
// Sở thích: 35%
// Nghề nghiệp: 25%
// Vị trí: 25%
// Tuổi: 15%

// Muốn thay đổi thành:
// Sở thích: 40%
// Nghề nghiệp: 20%
// Vị trí: 30%
// Tuổi: 10%
```

Thay đổi các số trong query:
```sql
(COUNT(DISTINCT nst2.maSoThich) * 40.0) / ...  -- Sở thích
WHEN ... THEN 20  -- Nghề nghiệp
WHEN ... THEN 30  -- Vị trí
WHEN ... THEN 10  -- Tuổi
```

### Thay đổi Số lượng Kết quả

```php
// Trong model/mdexuat.php
LIMIT 20  -- Thay 20 thành số khác
```

### Thêm Bộ lọc Mới

Ví dụ thêm filter theo giới tính:

```php
// Trong model/mdexuat.php, thêm vào $filterConditions:
if (!empty($filters['gioitinh'])) {
    $gioitinh = $conn->real_escape_string($filters['gioitinh']);
    $filterConditions .= " AND hs.gioiTinh = '$gioitinh' ";
}
```

---

## 📈 Performance Tips

### 1. Thêm Index
```sql
-- Đã có sẵn trong file SQL
ALTER TABLE hosonguoidung ADD INDEX idx_thanhpho (maThanhPho);
ALTER TABLE nguoidung_sothich ADD INDEX idx_user (maNguoiDung);
```

### 2. Cache kết quả
Trong tương lai có thể implement Redis cache cho query phức tạp

### 3. Limit hợp lý
Không nên quá 50 kết quả để tránh query chậm

---

## 📚 Tài liệu tham khảo

- `MATCHING_ALGORITHM.md` - Chi tiết thuật toán
- `database_update_matching.sql` - Cấu trúc database
- `model/mdexuat.php` - Logic matching
- `view/dexuat.php` - Giao diện

---

## ✅ Checklist Hoàn thành

- [x] Cải tiến thuật toán matching
- [x] Thêm bộ lọc theo khu vực
- [x] Thêm bộ lọc theo nghề nghiệp
- [x] Thêm bộ lọc theo độ tuổi
- [x] Hiển thị điểm tương thích
- [x] Hiển thị sở thích chung
- [x] Tối ưu database với index
- [x] Documentation đầy đủ
- [x] Test query

---

## 🎓 Học được gì từ update này?

1. **Database Design**: Many-to-many relationship với bảng trung gian
2. **SQL Advanced**: Subquery, JOIN, aggregation, CASE WHEN
3. **Algorithm**: Weighted scoring system
4. **UX/UI**: Progressive disclosure, visual feedback
5. **Performance**: Index optimization, query optimization

---

## 💡 Gợi ý Cải tiến Tiếp theo

1. ⭐ **Machine Learning**: Học sở thích từ hành vi user
2. 🔔 **Notification**: Thông báo khi có match mới
3. 📊 **Analytics**: Thống kê tỷ lệ match
4. 💬 **Ice breaker**: Gợi ý câu mở đầu dựa trên sở thích chung
5. 🎯 **A/B Testing**: Test nhiều công thức matching
6. 🌐 **Location**: Tính khoảng cách GPS thực tế
7. 👥 **Mutual friends**: Tính điểm dựa trên bạn chung

---

## 📞 Hỗ trợ

Nếu có vấn đề, kiểm tra:
1. Database connection trong `mKetNoi.php`
2. Session đã được start chưa
3. User đã đăng nhập chưa
4. User có profile trong `hosonguoidung` chưa
5. Các bảng có dữ liệu chưa

---

**Chúc bạn thành công! 🎉**
