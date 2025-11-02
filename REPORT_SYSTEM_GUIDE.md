# Hướng Dẫn Hệ Thống Báo Cáo (Report System)

## Tổng Quan

Hệ thống báo cáo đã được nâng cấp để hỗ trợ báo cáo 3 loại đối tượng:
1. **Người dùng** (`nguoidung`)
2. **Bài viết** (`baidang`)
3. **Tin nhắn** (`tinnhan`)

## Cấu Trúc Database

### Bảng `baocaovipham`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| `maBaoCao` | INT(11) AUTO_INCREMENT | ID báo cáo (Primary Key) |
| `maNguoiBaoCao` | INT(10) UNSIGNED | ID người gửi báo cáo |
| `loaiBaoCao` | ENUM('baidang', 'nguoidung', 'tinnhan') | Loại báo cáo |
| `maBaiDang` | INT(10) UNSIGNED NULL | ID bài viết (nếu báo cáo bài viết) |
| `maTinNhan` | INT(10) UNSIGNED NULL | ID tin nhắn (nếu báo cáo tin nhắn) |
| `maNguoiDungBiBaoCao` | INT(10) UNSIGNED | ID người bị báo cáo |
| `lyDo` | TEXT | Lý do báo cáo |
| `thoiGianBaoCao` | DATETIME DEFAULT CURRENT_TIMESTAMP | Thời gian báo cáo |
| `trangThai` | ENUM('dangxuly', 'daxuly', 'tuchoi') | Trạng thái xử lý |
| `noiDungViPham` | TEXT NULL | Snapshot nội dung vi phạm |
| `contextTinNhan` | LONGTEXT NULL | Context 10-20 tin nhắn xung quanh (JSON) |
| `thoiGianTinNhan` | DATETIME NULL | Thời điểm tin nhắn được gửi |

## Tính Năng Chính

### 1. Báo Cáo Người Dùng

**Vị trí:** 
- Trang profile: `view/profile.php`
- Trang tin nhắn: `view/tinnhan.php`

**Cách hoạt động:**
- Người dùng chọn "Báo cáo" từ menu dropdown
- Nhập lý do báo cáo
- Hệ thống lưu thông tin vào database với `loaiBaoCao='nguoidung'`

**View xử lý:** `view/report-user.php`

### 2. Báo Cáo Bài Viết

**Vị trí:** `view/bantin.php`

**Cách hoạt động:**
- Click vào menu 3 chấm trên bài viết
- Chọn "Báo cáo bài viết"
- Nhập lý do báo cáo
- Hệ thống lưu snapshot nội dung bài viết vào `noiDungViPham`

**View xử lý:** `view/report-post.php`

### 3. Báo Cáo Tin Nhắn ⭐ (Tính năng đặc biệt)

**Vị trí:** `view/tinnhan.php`

**Cách hoạt động:**
- Hover vào tin nhắn của người khác → xuất hiện nút báo cáo (icon cảnh báo)
- Click vào nút báo cáo
- Nhập lý do báo cáo
- Hệ thống tự động:
  - Lưu snapshot tin nhắn bị báo cáo vào `noiDungViPham`
  - Lấy **10 tin nhắn trước** và **10 tin nhắn sau** tin nhắn bị báo cáo
  - Lưu context dưới dạng JSON vào `contextTinNhan`
  - Lưu thời gian tin nhắn vào `thoiGianTinNhan`

**View xử lý:** `view/report-message.php`

**Lợi ích của Context:**
- Admin có thể hiểu đầy đủ ngữ cảnh cuộc trò chuyện
- Tránh việc người dùng báo cáo sai hoặc lạm dụng
- Có bằng chứng rõ ràng để xử lý vi phạm

## Luồng Xử Lý

### Model Layer (`model/mBaoCaoViPham.php`)

```php
// Báo cáo người dùng
createUserReport($maNguoiBaoCao, $maNguoiDungBiBaoCao, $lyDo)

// Báo cáo bài viết
createPostReport($maNguoiBaoCao, $maBaiDang, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham)

// Báo cáo tin nhắn (với context)
createMessageReport($maNguoiBaoCao, $maTinNhan, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham, $contextTinNhan, $thoiGianTinNhan)

// Lấy context tin nhắn
getMessageContext($maTinNhan, $maNguoiDung1, $maNguoiDung2, $contextSize = 10)
```

### Controller Layer (`controller/cQuanHeNguoiDung.php`)

```php
// Báo cáo người dùng
reportUser($maNguoiBaoCao, $maNguoiDungBiBaoCao, $lyDo)

// Báo cáo bài viết
reportPost($maNguoiBaoCao, $maBaiDang, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham)

// Báo cáo tin nhắn (tự động lấy context)
reportMessage($maNguoiBaoCao, $maTinNhan, $maNguoiDungBiBaoCao, $lyDo, $noiDungViPham, $maNguoiDung1, $maNguoiDung2)
```

### View Layer

1. **report-user.php** - Xử lý báo cáo người dùng
2. **report-post.php** - Xử lý báo cáo bài viết
3. **report-message.php** - Xử lý báo cáo tin nhắn

## Trạng Thái Báo Cáo

- `dangxuly` - Đang chờ admin xem xét (mặc định)
- `daxuly` - Admin đã xử lý xong
- `tuchoi` - Admin từ chối báo cáo (không vi phạm)

## Lấy Danh Sách Báo Cáo (Cho Admin)

```php
// Lấy tất cả báo cáo đang xử lý
$reports = $model->getReportsByType(null, 'dangxuly');

// Lấy báo cáo tin nhắn đang xử lý
$messageReports = $model->getReportsByType('tinnhan', 'dangxuly');

// Lấy báo cáo của một người dùng cụ thể
$userReports = $model->getReportDetailsByUser($maNguoiDung);
```

## Context Tin Nhắn - Cấu Trúc JSON

```json
[
  {
    "maTinNhan": 123,
    "maNguoiDung1": 1,
    "maNguoiDung2": 2,
    "noiDungText": "Tin nhắn trước đó...",
    "thoiGianGui": "2025-11-02 10:00:00"
  },
  {
    "maTinNhan": 124,
    "maNguoiDung1": 2,
    "maNguoiDung2": 1,
    "noiDungText": "TIN NHẮN BỊ BÁO CÁO",
    "thoiGianGui": "2025-11-02 10:01:00"
  },
  {
    "maTinNhan": 125,
    "maNguoiDung1": 1,
    "maNguoiDung2": 2,
    "noiDungText": "Tin nhắn sau đó...",
    "thoiGianGui": "2025-11-02 10:02:00"
  }
]
```

## Ghi Chú Kỹ Thuật

1. **Enum Values:** 
   - `loaiBaoCao`: 'baidang', 'nguoidung', 'tinnhan' (không có dấu cách)
   - `trangThai`: 'dangxuly', 'daxuly', 'tuchoi' (không có dấu gạch dưới)

2. **Context Size:** Mặc định lấy 10 tin nhắn trước/sau. Có thể điều chỉnh trong method `getMessageContext()`.

3. **Performance:** Các index đã được thêm vào để tăng tốc truy vấn:
   - `idx_nguoi_bao_cao`
   - `idx_nguoi_bi_bao_cao`
   - `idx_loai_bao_cao`
   - `idx_trang_thai`
   - `idx_thoi_gian`

4. **Security:** 
   - Không thể tự báo cáo chính mình
   - Sử dụng prepared statements để tránh SQL injection
   - Validate input trước khi lưu database

## TODO - Tính Năng Admin (Cần Phát Triển)

- [ ] Trang quản lý báo cáo cho admin
- [ ] Hiển thị context tin nhắn trong giao diện admin
- [ ] Cập nhật trạng thái báo cáo
- [ ] Gửi thông báo cho người bị báo cáo
- [ ] Tự động khóa tài khoản khi đủ số lượng báo cáo
- [ ] Xuất báo cáo thống kê

## Files Liên Quan

- `model/mBaoCaoViPham.php` - Model xử lý database
- `controller/cQuanHeNguoiDung.php` - Controller xử lý logic
- `view/report-user.php` - View báo cáo người dùng
- `view/report-post.php` - View báo cáo bài viết
- `view/report-message.php` - View báo cáo tin nhắn
- `view/profile.php` - Giao diện profile (có nút báo cáo)
- `view/tinnhan.php` - Giao diện tin nhắn (có nút báo cáo tin nhắn)
- `view/bantin.php` - Giao diện bản tin (có nút báo cáo bài viết)
- `database_baocaovipham.sql` - Script tạo/cập nhật database

---

**Ngày cập nhật:** 2025-11-02
**Phiên bản:** 2.0
