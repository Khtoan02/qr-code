# QR Code với Logo PayGen

## Cách hoạt động:

1. **QR Code được tạo** từ VietQR data string
2. **Logo PayGen được overlay** vào giữa QR code
3. **Kết quả** là QR code có logo PayGen thay vì logo VietQR mặc định

## Yêu cầu hệ thống:

### Option 1: Sử dụng Imagick (Khuyến nghị)
- Cài đặt PHP Imagick extension
- Hỗ trợ SVG logo trực tiếp

### Option 2: Sử dụng GD Library
- Cần convert `icon.svg` → `icon.png` trước
- Sử dụng công cụ: https://cloudconvert.com/svg-to-png
- Hoặc: `convert assets/images/icon.svg -resize 128x128 assets/images/icon.png`

## Cách sử dụng:

QR code sẽ tự động có logo khi:
- Tạo payment mới trong admin panel
- Hiển thị QR code trong trang thanh toán (`pay.php`)

## API Endpoints:

### `/api/qr_image.php?amount={amount}&code={paymentCode}`
Trả về QR code image với logo PayGen

### `/api/qr.php?amount={amount}&code={code}&format=image`
Trả về JSON với base64 QR code

## Troubleshooting:

### Nếu QR code không có logo:

1. **Kiểm tra Imagick:**
   ```php
   <?php
   var_dump(extension_loaded('imagick'));
   ?>
   ```

2. **Nếu không có Imagick:**
   - Convert `icon.svg` → `icon.png`
   - Đặt vào `/assets/images/icon.png`

3. **Kiểm tra file logo:**
   - Đảm bảo `/assets/images/icon.svg` tồn tại
   - Hoặc `/assets/images/icon.png` tồn tại

4. **Test QR endpoint:**
   ```
   http://yourdomain.com/api/qr_image.php?amount=100000&code=DH123456
   ```

## Cải thiện trong tương lai:

- [ ] Tích hợp thư viện QR code PHP (endroid/qr-code)
- [ ] Cache QR code để tăng performance
- [ ] Hỗ trợ nhiều format logo (SVG, PNG, JPG)
- [ ] Tùy chỉnh kích thước logo

