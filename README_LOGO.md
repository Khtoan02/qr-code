# Logo và Favicon - PayGen Gateway

## Các file đã tạo:

1. **`/assets/images/logo.svg`** - Logo chính (120x120px)
   - Sử dụng cho trang chủ, header lớn
   - Có QR code pattern + payment arrow + shield badge

2. **`/assets/images/icon.svg`** - Icon nhỏ (64x64px)
   - Sử dụng cho sidebar, buttons, small icons
   - Phiên bản đơn giản hóa của logo

3. **`/assets/images/favicon.svg`** - Favicon (32x32px)
   - Hiển thị trong browser tab
   - Tối ưu cho kích thước nhỏ

## Cách sử dụng:

### Trong HTML:
```html
<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="alternate icon" href="/assets/images/favicon.svg">

<!-- Logo -->
<img src="/assets/images/logo.svg" alt="PayGen Logo" class="w-16 h-16" />

<!-- Icon -->
<img src="/assets/images/icon.svg" alt="PayGen" class="w-6 h-6" />
```

## Troubleshooting:

### Nếu favicon không hiển thị:

1. **Clear browser cache:**
   - Chrome: Ctrl+Shift+Delete → Clear cached images
   - Hoặc hard refresh: Ctrl+F5

2. **Kiểm tra đường dẫn:**
   - Đảm bảo file tồn tại tại `/assets/images/favicon.svg`
   - Kiểm tra `.htaccess` không block SVG files

3. **Tạo fallback PNG (nếu cần):**
   - Sử dụng công cụ online: https://realfavicongenerator.net
   - Upload `favicon.svg` và generate các kích thước PNG
   - Download và đặt vào `/assets/images/`

4. **Kiểm tra server config:**
   - Đảm bảo server hỗ trợ MIME type `image/svg+xml`
   - Apache: Thêm vào `.htaccess`:
   ```apache
   AddType image/svg+xml .svg
   ```

## Design Notes:

- **Màu sắc:** Emerald (#10b981) - nhất quán với brand
- **Style:** Minimalist, modern, professional
- **Elements:** QR code + Payment arrow + Security shield
- **Scalable:** SVG format cho chất lượng tốt ở mọi kích thước

## Browser Support:

- ✅ Chrome/Edge (SVG favicon supported)
- ✅ Firefox (SVG favicon supported)
- ✅ Safari (SVG favicon supported từ iOS 11+)
- ⚠️ IE11 (cần fallback PNG)

