# PayGen Gateway - Phiên bản PHP

Hệ thống thanh toán nội bộ & quản lý giao dịch tập trung được chuyển đổi từ React/TypeScript sang PHP thuần.

## Cài đặt

### 1. Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên hoặc MariaDB
- Apache/Nginx với mod_rewrite
- Extension PHP: PDO, curl, json

### 2. Cấu hình Database

1. Tạo database:
```bash
mysql -u root -p < database.sql
```

2. Cấu hình kết nối database trong `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'paygen_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Cấu hình API Keys

Chỉnh sửa file `config.php`:
- `SEPAY_API_KEY`: API key từ SePay
- `GEMINI_API_KEY`: API key từ Google Gemini (nếu sử dụng tính năng chỉnh sửa ảnh)

### 4. Cấu trúc thư mục

```
qrcode/
├── api/              # API endpoints
├── assets/           # CSS, JS, images
├── includes/         # PHP includes (db, services)
├── config.php        # Cấu hình chính
├── index.php         # Trang chủ
├── login.php         # Trang đăng nhập
├── admin.php         # Trang quản trị
├── pay.php           # Trang thanh toán (customer view)
├── logout.php        # Đăng xuất
├── database.sql      # Schema database
└── .htaccess         # Apache rewrite rules
```

## Sử dụng

### Đăng nhập
- URL: `http://localhost/login.php`
- Tài khoản: `admin`
- Mật khẩu: `123456`

### Tạo giao dịch thanh toán
1. Đăng nhập vào admin panel
2. Chọn tab "Tạo thanh toán"
3. Nhập số tiền và ghi chú
4. Click "Tạo Mã QR"
5. Share link thanh toán cho khách hàng

### Kiểm tra thanh toán
- Hệ thống tự động kiểm tra trạng thái thanh toán mỗi 3 giây
- Sử dụng SePay API để kiểm tra giao dịch thực tế

## API Endpoints

### `GET /api/check_payment.php?id={transaction_id}`
Kiểm tra trạng thái thanh toán của một giao dịch.

### `GET /api/get_transactions.php?filter={ALL|PAID|PENDING}`
Lấy danh sách giao dịch (yêu cầu đăng nhập).

### `GET /api/get_stats.php`
Lấy thống kê tổng quan (yêu cầu đăng nhập).

## Webhook Configuration

### SePay Webhook Endpoint

**URL Webhook:** `http://yourdomain.com/webhook.php`

**Cấu hình trong SePay Dashboard:**
1. Đăng nhập vào SePay dashboard
2. Vào phần **Webhook Settings** hoặc **Cấu hình Webhook**
3. Nhập URL: `http://yourdomain.com/webhook.php`
4. Method: `POST`
5. Content-Type: `application/json`
6. Lưu cấu hình

**Cách hoạt động:**
- Khi có giao dịch thanh toán vào tài khoản SePay
- SePay sẽ gửi POST request đến webhook URL
- Hệ thống tự động cập nhật trạng thái giao dịch từ `PENDING` → `PAID`
- Khách hàng sẽ thấy trạng thái "Đã thanh toán" ngay lập tức

**Test Webhook:**
- Truy cập `/api/webhook_test.php` để xem format dữ liệu
- Kiểm tra logs tại `/logs/webhook.log` (nếu có)

**Lưu ý:**
- Đảm bảo webhook URL có thể truy cập được từ internet (không phải localhost)
- Nếu dùng localhost, có thể dùng ngrok để expose: `ngrok http 80`
- Webhook sẽ tự động log tất cả requests vào file log để debug

## Tính năng

- ✅ Quản lý giao dịch thanh toán
- ✅ Tạo mã QR thanh toán tự động
- ✅ Tự động kiểm tra trạng thái thanh toán
- ✅ Dashboard thống kê
- ✅ Lịch sử giao dịch với filter
- ✅ Tích hợp SePay API
- ✅ Responsive design với Tailwind CSS

## Bảo mật

- Session-based authentication
- SQL injection protection (PDO prepared statements)
- XSS protection (htmlspecialchars)
- CSRF protection (có thể thêm token nếu cần)

## Lưu ý

- Đảm bảo file `.htaccess` được kích hoạt trên Apache
- Nếu dùng Nginx, cần cấu hình rewrite rules tương tự
- API SePay sử dụng CORS proxy, có thể cần điều chỉnh trong môi trường production
- Gemini API key cần được cấu hình nếu sử dụng tính năng chỉnh sửa ảnh

## Hỗ trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
1. PHP error logs
2. Database connection
3. API keys configuration
4. File permissions

