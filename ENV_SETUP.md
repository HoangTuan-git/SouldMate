# 🔒 Cấu hình Biến Môi Trường (Environment Variables)

## 📋 Hướng dẫn Setup

### Bước 1: Tạo file `.env`

Copy file `.env.example` thành `.env`:

```bash
# Windows (CMD)
copy .env.example .env

# Windows (PowerShell)
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

### Bước 2: Cấu hình các biến môi trường

Mở file `.env` và thay đổi các giá trị theo môi trường của bạn:

```env
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password_here
DB_NAME=dating_app

# JWT Configuration
JWT_SECRET_KEY=change_this_to_random_string_in_production
JWT_ALGORITHM=HS256
JWT_EXPIRATION=86400

# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_gmail_app_password
MAIL_FROM_EMAIL=your_email@gmail.com
MAIL_FROM_NAME=SoulMatch
MAIL_ENCRYPTION=tls

# OTP Configuration
OTP_EXPIRY_MINUTES=5
OTP_LENGTH=6
```

### Bước 3: Tạo Gmail App Password

1. Truy cập: https://myaccount.google.com/apppasswords
2. Đăng nhập Gmail của bạn
3. Tạo App Password mới
4. Copy password và paste vào `MAIL_PASSWORD` trong file `.env`

## ⚠️ Bảo mật

### ✅ DO (Nên làm):
- ✅ Luôn thêm `.env` vào `.gitignore`
- ✅ Sử dụng App Password thay vì password Gmail thật
- ✅ Thay đổi `JWT_SECRET_KEY` thành chuỗi ngẫu nhiên mạnh
- ✅ Backup file `.env` ở nơi an toàn (không lưu trên Git)
- ✅ Sử dụng các giá trị khác nhau cho môi trường dev/production

### ❌ DON'T (Không nên):
- ❌ Commit file `.env` lên Git
- ❌ Share file `.env` qua email/chat
- ❌ Sử dụng password Gmail thật
- ❌ Sử dụng JWT secret đơn giản như "123456"
- ❌ Copy `.env` từ production về local

## 🔑 Tạo JWT Secret Key mạnh

Sử dụng một trong các cách sau:

### PHP:
```php
// Chạy trong terminal PHP
php -r "echo bin2hex(random_bytes(32));"
```

### Online Tool:
- https://www.random.org/strings/
- Length: 64 characters
- Type: Alphanumeric

### OpenSSL:
```bash
openssl rand -hex 32
```

## 📁 Cấu trúc Files

```
project/
├── .env                    # ❌ GIT IGNORED - Chứa giá trị thật
├── .env.example           # ✅ Committed - Template mẫu
├── .gitignore             # ✅ Chứa .env trong ignore list
├── config/
│   ├── jwt.config.php     # ✅ Đọc từ .env
│   └── mail.config.php    # ✅ Đọc từ .env
└── helper/
    └── EnvLoader.php      # ✅ Load .env file
```

## 🚀 Production Deployment

### Option 1: Server Environment Variables
Thiết lập biến môi trường trực tiếp trên server (khuyến nghị):

```bash
# Apache (.htaccess)
SetEnv JWT_SECRET_KEY "your_production_secret"
SetEnv DB_PASSWORD "your_db_password"

# Nginx
fastcgi_param JWT_SECRET_KEY "your_production_secret";
fastcgi_param DB_PASSWORD "your_db_password";
```

### Option 2: .env file
- Upload file `.env` thủ công lên server
- Không commit vào Git
- Chỉ admin có quyền đọc file

```bash
chmod 600 .env  # Chỉ owner đọc/ghi
```

## 🔍 Kiểm tra cấu hình

Tạo file `test-env.php`:

```php
<?php
require_once 'helper/EnvLoader.php';
EnvLoader::load();

echo "JWT Secret: " . (EnvLoader::get('JWT_SECRET_KEY') ? '✅ Set' : '❌ Not Set') . "\n";
echo "Mail Username: " . (EnvLoader::get('MAIL_USERNAME') ? '✅ Set' : '❌ Not Set') . "\n";
echo "DB Password: " . (EnvLoader::get('DB_PASSWORD') ? '✅ Set' : '❌ Not Set') . "\n";
```

## 📚 Tài liệu tham khảo

- [PHP dotenv](https://github.com/vlucas/phpdotenv)
- [12 Factor App](https://12factor.net/config)
- [Gmail App Passwords](https://support.google.com/accounts/answer/185833)

## ❓ Troubleshooting

### Lỗi: .env file not found
- Đảm bảo file `.env` tồn tại trong thư mục root
- Kiểm tra quyền đọc file (chmod 600 hoặc 644)

### Lỗi: Cannot send email
- Kiểm tra `MAIL_USERNAME` và `MAIL_PASSWORD`
- Đảm bảo đã bật "Less secure app access" hoặc dùng App Password
- Kiểm tra firewall/port 587 có bị block không

### Lỗi: JWT validation failed
- Đảm bảo `JWT_SECRET_KEY` giống nhau trên tất cả servers
- Không thay đổi secret khi có users đang login
