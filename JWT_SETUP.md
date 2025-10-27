# JWT Authentication Setup Guide

## 📋 Cài đặt

### 1. Cài đặt PHP JWT Library
```bash
cd d:\PTUD\codePTUD - Copy
composer install
```

### 2. Cài đặt Node.js JWT Library
```bash
npm install
```

## 🔧 Cấu hình

### Secret Key
File: `config/jwt.config.php`
```php
define('JWT_SECRET_KEY', 'your-secret-key-change-this-in-production-2024');
```

File: `server.js`
```javascript
const JWT_SECRET_KEY = 'your-secret-key-change-this-in-production-2024';
```

⚠️ **QUAN TRỌNG**: Secret key phải GIỐNG NHAU ở cả PHP và Node.js

## 🚀 Cách hoạt động

### Flow đăng nhập:

1. **User đăng nhập** (PHP)
   ```
   POST /home.php?page=dangnhap
   ↓
   Controller: cNguoiDung->Login()
   ↓
   Tạo JWT token: JWTHelper::createToken($userId, $email)
   ↓
   Lưu vào $_SESSION['jwt_token']
   ```

2. **Mở trang chat** (PHP → JavaScript)
   ```php
   <script>
     window.jwtToken = '<?= $_SESSION['jwt_token'] ?>';
   </script>
   ```

3. **Connect Socket.IO** (JavaScript → Node.js)
   ```javascript
   socket.emit('join', {
     userId: userId,
     token: window.jwtToken
   });
   ```

4. **Verify token** (Node.js)
   ```javascript
   const decoded = jwt.verify(token, JWT_SECRET_KEY);
   if (decoded.uid == userId) {
     // ✅ Valid
   }
   ```

## ✅ Test

### 1. Đăng nhập
- Login vào hệ thống
- Check console: Không có lỗi JWT

### 2. Mở chat
- Vào trang chat
- F12 → Console
- Kiểm tra log: `✅ JWT verified for user {userId}`

### 3. Test invalid token
```javascript
// Trong console browser
window.jwtToken = 'invalid-token';
// Reload page → Sẽ báo lỗi
```

## 🔒 Bảo mật

- ✅ Token expire sau 24h
- ✅ Token chứa userId và email
- ✅ Server verify token trước khi accept connection
- ✅ Backward compatibility: vẫn chấp nhận connection không có token (để test)

## 📝 Notes

- Token được tạo khi đăng nhập
- Token được lưu trong PHP session
- Token được truyền sang client qua JavaScript global variable
- Token được gửi lên server khi connect socket
- Server verify token và cho phép/từ chối connection
