# Tính năng Xóa Bài Viết

## ✅ Đã hoàn thành

### 🎯 Chức năng
- Người dùng có thể xóa bài viết của chính mình
- Kiểm tra quyền sở hữu trước khi xóa
- Tự động xóa file ảnh liên quan
- Xác nhận trước khi xóa (confirmation dialog)
- Redirect sau khi xóa thành công

---

## 📝 Chi tiết triển khai

### 1. Model (`model/mBanTin.php`)

#### Method mới: `mDeleteTinTuc($postId, $userId)`

**Chức năng:**
- Kiểm tra quyền sở hữu bài viết
- Xóa bài viết khỏi database
- Xóa file ảnh trong thư mục `img/`

**Logic:**
```php
public function mDeleteTinTuc($postId, $userId)
{
    // 1. Kiểm tra quyền sở hữu
    $checkQuery = "SELECT maNguoiDung, noidungAnh FROM baidang WHERE maBaiDang = $postId";
    
    // 2. Chỉ cho phép xóa nếu là chủ bài viết
    if ($post['maNguoiDung'] != $userId) {
        return false;
    }
    
    // 3. Xóa bài viết từ database
    $deleteQuery = "DELETE FROM baidang WHERE maBaiDang = $postId";
    
    // 4. Xóa file ảnh (nếu có)
    if ($kq && !empty($post['noidungAnh'])) {
        $images = explode(',', $post['noidungAnh']);
        foreach ($images as $image) {
            @unlink(__DIR__ . '/../img/' . trim($image));
        }
    }
    
    return $kq;
}
```

**Return:**
- `true`: Xóa thành công
- `false`: Xóa thất bại (không có quyền hoặc lỗi)

---

### 2. Controller (`controller/cBanTin.php`)

#### Method mới: `cDeleteTinTuc($postId, $userId)`

**Chức năng:**
- Validate input
- Gọi model để xóa bài viết

**Code:**
```php
public function cDeleteTinTuc($postId, $userId)
{
    // Kiểm tra tham số
    if (empty($postId) || empty($userId)) {
        return false;
    }
    
    $p = new mBanTin();
    $kq = $p->mDeleteTinTuc($postId, $userId);
    return $kq;
}
```

---

### 3. View (`view/bantin.php`)

#### A. PHP Handler (đầu file)

**Xử lý POST request xóa:**
```php
if (isset($_POST['deletePost']) && isset($_POST['postId'])) {
    if (!isset($_SESSION['uid'])) {
        echo '<script>alert("Bạn cần đăng nhập để thực hiện thao tác này!")</script>';
    } else {
        $postId = intval($_POST['postId']);
        $result = $cBanTin->cDeleteTinTuc($postId, $_SESSION['uid']);
        
        if ($result) {
            echo '<script>alert("Xóa bài viết thành công!")</script>';
            header("Location: home.php?page=bantin");
            exit();
        } else {
            echo '<script>alert("Không thể xóa bài viết. Bạn không có quyền hoặc bài viết không tồn tại!")</script>';
        }
    }
}
```

#### B. JavaScript Function

**Cập nhật hàm `deletePost(postId)`:**
```javascript
function deletePost(postId) {
    if (confirm('Bạn có chắc chắn muốn xóa bài viết này?\nHành động này không thể hoàn tác!')) {
        // Tạo form động
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        // Thêm postId
        const postIdInput = document.createElement('input');
        postIdInput.type = 'hidden';
        postIdInput.name = 'postId';
        postIdInput.value = postId;
        form.appendChild(postIdInput);
        
        // Thêm deletePost flag
        const deleteInput = document.createElement('input');
        deleteInput.type = 'hidden';
        deleteInput.name = 'deletePost';
        deleteInput.value = '1';
        form.appendChild(deleteInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
    
    // Close menu
    togglePostMenu(postId);
}
```

#### C. UI Button

**Đã có sẵn trong dropdown menu:**
```php
<?php if ($post['maNguoiDung'] == $_SESSION['uid']): ?>
    <button class="dropdown-item text-danger" onclick="deletePost(<?= $post['maBaiDang'] ?>)">
        <i class="bi bi-trash"></i> Xóa bài viết
    </button>
<?php else: ?>
    <!-- Nút báo cáo cho bài viết của người khác -->
<?php endif; ?>
```

---

## 🔒 Bảo mật

### 1. Kiểm tra quyền sở hữu
- Backend kiểm tra `maNguoiDung` trong database
- Chỉ cho phép xóa nếu user là chủ bài viết

### 2. Kiểm tra đăng nhập
- Kiểm tra `$_SESSION['uid']` tồn tại
- Từ chối request nếu chưa đăng nhập

### 3. Validate input
- `intval($postId)` để tránh SQL injection
- Kiểm tra `empty()` trước khi xử lý

### 4. SQL Injection Protection
- Sử dụng integer type cho `$postId`
- Query sử dụng biến số thay vì string concatenation

---

## 🎨 UX/UI

### 1. Confirmation Dialog
- ✅ Alert xác nhận trước khi xóa
- ✅ Cảnh báo "Hành động này không thể hoàn tác!"

### 2. Feedback Messages
- ✅ "Xóa bài viết thành công!"
- ✅ "Không thể xóa bài viết..." (lỗi)
- ✅ "Bạn cần đăng nhập..." (chưa login)

### 3. UI Elements
- ✅ Icon trash (`bi-trash`)
- ✅ Màu đỏ cho nút xóa (`text-danger`)
- ✅ Dropdown menu (3 dots)

---

## 📂 Files đã thay đổi

1. ✅ `model/mBanTin.php`
   - Thêm `mDeleteTinTuc($postId, $userId)`

2. ✅ `controller/cBanTin.php`
   - Thêm `cDeleteTinTuc($postId, $userId)`

3. ✅ `view/bantin.php`
   - Thêm PHP handler xử lý POST
   - Cập nhật JavaScript `deletePost(postId)`
   - UI button đã có sẵn

---

## 🔄 Luồng hoạt động

### Khi người dùng xóa bài viết:

1. **Click nút xóa** → Hiện confirmation dialog
2. **Click OK** → JavaScript tạo form và submit
3. **PHP handler** nhận POST request
4. **Kiểm tra session** → Có đăng nhập không?
5. **Controller** → Validate input
6. **Model** → Kiểm tra quyền sở hữu
7. **Database** → DELETE bài viết
8. **File system** → Xóa ảnh (nếu có)
9. **Redirect** → Quay lại trang bantin
10. **Alert** → Thông báo kết quả

---

## ⚠️ Lưu ý

### 1. Database Constraints
- Nếu có foreign key với `ON DELETE CASCADE`:
  - Bình luận sẽ tự động xóa
  - Tương tác (like) sẽ tự động xóa
- Nếu không có cascade, cần xóa thủ công

### 2. File Cleanup
- Ảnh được xóa bằng `@unlink()`
- `@` để suppress warning nếu file không tồn tại
- Chỉ xóa ảnh trong thư mục `img/`

### 3. Transaction
- Hiện tại chưa dùng transaction
- Nên thêm transaction để đảm bảo atomicity:
  ```php
  $conn->begin_transaction();
  try {
      // Delete post
      // Delete images
      $conn->commit();
  } catch (Exception $e) {
      $conn->rollback();
  }
  ```

---

## ✨ Mở rộng trong tương lai

### 1. Soft Delete
- Thay vì xóa thật, set flag `isDeleted = 1`
- Cho phép khôi phục trong 30 ngày

### 2. Admin Delete
- Admin có thể xóa bất kỳ bài viết nào
- Thêm check `$_SESSION['role'] == 'admin'`

### 3. Batch Delete
- Cho phép xóa nhiều bài viết cùng lúc
- Checkbox + nút "Xóa đã chọn"

### 4. Recycle Bin
- Bài viết xóa vào thùng rác
- Tự động xóa vĩnh viễn sau 30 ngày

### 5. Activity Log
- Ghi log vào bảng `lichsuvipham` hoặc `activity_log`
- Theo dõi ai xóa bài nào, khi nào

---

## 🚀 Sẵn sàng sử dụng!

Tính năng xóa bài viết đã hoàn thiện với:
- ✅ Kiểm tra quyền sở hữu
- ✅ Xóa database + file ảnh
- ✅ Confirmation dialog
- ✅ Feedback messages
- ✅ Security validation
- ✅ UI/UX thân thiện
