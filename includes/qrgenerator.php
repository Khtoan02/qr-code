<?php
/**
 * QR Code Generator với Logo PayGen
 * Sử dụng thư viện phpqrcode để tạo QR code với logo tùy chỉnh
 */

require_once __DIR__ . '/../config.php';

class QRGenerator {
    /**
     * Tạo QR code với logo PayGen embed
     * 
     * @param string $data Dữ liệu QR code (VietQR string)
     * @param int $size Kích thước QR code (pixels)
     * @param string $logoPath Đường dẫn đến logo
     * @return string Base64 encoded image data URI
     */
    public static function generateWithLogo($data, $size = 400, $logoPath = null) {
        // Nếu không có thư viện phpqrcode, fallback về SePay URL
        if (!class_exists('QRcode')) {
            // Tạo VietQR URL và return
            return self::generateVietQRUrl($data);
        }
        
        // Tạo QR code tạm thời
        $tempFile = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';
        QRcode::png($data, $tempFile, QR_ECLEVEL_H, $size / 25, 2);
        
        // Load QR code image
        $qrImage = imagecreatefrompng($tempFile);
        
        // Load logo
        $logoPath = $logoPath ?: __DIR__ . '/../assets/images/icon.svg';
        $logo = self::loadLogo($logoPath, $size / 5); // Logo = 20% của QR size
        
        if ($logo) {
            // Tính toán vị trí đặt logo (giữa QR code)
            $logoX = ($size - imagesx($logo)) / 2;
            $logoY = ($size - imagesy($logo)) / 2;
            
            // Merge logo vào QR code
            imagecopymerge($qrImage, $logo, $logoX, $logoY, 0, 0, imagesx($logo), imagesy($logo), 100);
        }
        
        // Convert sang base64
        ob_start();
        imagepng($qrImage);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Cleanup
        imagedestroy($qrImage);
        if ($logo) imagedestroy($logo);
        @unlink($tempFile);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Load logo từ file (SVG hoặc PNG)
     */
    private static function loadLogo($logoPath, $size) {
        if (!file_exists($logoPath)) {
            return null;
        }
        
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        
        if ($ext === 'svg') {
            // Convert SVG to PNG using Imagick hoặc fallback
            return self::svgToImage($logoPath, $size);
        } elseif (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $image = imagecreatefromstring(file_get_contents($logoPath));
            if ($image) {
                // Resize logo
                $resized = imagecreatetruecolor($size, $size);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $size, $size, imagesx($image), imagesy($image));
                imagedestroy($image);
                return $resized;
            }
        }
        
        return null;
    }
    
    /**
     * Convert SVG to GD Image
     */
    private static function svgToImage($svgPath, $size) {
        // Sử dụng Imagick nếu có
        if (extension_loaded('imagick')) {
            $imagick = new Imagick();
            $imagick->setBackgroundColor(new ImagickPixel('transparent'));
            $imagick->readImage($svgPath);
            $imagick->setImageFormat('png');
            $imagick->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);
            
            $image = imagecreatefromstring($imagick->getImageBlob());
            $imagick->destroy();
            return $image;
        }
        
        // Fallback: Tạo logo đơn giản từ SVG content
        $svgContent = file_get_contents($svgPath);
        // Parse SVG và tạo image đơn giản
        // Hoặc return null để không có logo
        return null;
    }
    
    /**
     * Tạo VietQR URL (fallback nếu không có thư viện QR)
     */
    private static function generateVietQRUrl($data) {
        // Parse data để lấy thông tin
        // Format: acc=xxx&bank=MB&amount=xxx&des=xxx
        parse_str(parse_url($data, PHP_URL_QUERY), $params);
        
        if (isset($params['acc'])) {
            return "https://qr.sepay.vn/img?" . http_build_query([
                'acc' => $params['acc'],
                'bank' => $params['bank'] ?? 'MB',
                'amount' => $params['amount'] ?? 0,
                'des' => $params['des'] ?? ''
            ]);
        }
        
        return $data;
    }
    
    /**
     * Tạo QR code cho SePay payment
     */
    public static function generateSePayQR($amount, $paymentCode, $withLogo = true) {
        // Tạo VietQR data string
        $qrData = self::buildVietQRData($amount, $paymentCode);
        
        if ($withLogo) {
            return self::generateWithLogo($qrData, 400);
        } else {
            return SePayService::getQRUrl($amount, $paymentCode);
        }
    }
    
    /**
     * Build VietQR data string
     */
    private static function buildVietQRData($amount, $paymentCode) {
        // VietQR format: https://qr.sepay.vn/img?acc=xxx&bank=MB&amount=xxx&des=xxx
        $url = "https://qr.sepay.vn/img?acc=" . VA_ACCOUNT_NUMBER . "&bank=MB&amount=" . $amount . "&des=" . urlencode($paymentCode);
        return $url;
    }
}

