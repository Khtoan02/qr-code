<?php
/**
 * Installation script for PayGen Database
 * Run this file once to set up the database
 */

require_once 'config.php';

try {
    // Connect without database first
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);
    
    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Remove CREATE DATABASE and USE statements as we already handled them
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE.*?;/i', '', $sql);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Installation Complete</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { background: #10b981; color: white; padding: 20px; border-radius: 8px; }
        .info { background: #f3f4f6; padding: 15px; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='success'>
        <h2>✅ Cài đặt thành công!</h2>
        <p>Database đã được tạo và cấu hình thành công.</p>
    </div>
    <div class='info'>
        <h3>Bước tiếp theo:</h3>
        <ol>
            <li>Xóa file <code>install.php</code> để bảo mật</li>
            <li>Truy cập <a href='/login.php'>trang đăng nhập</a></li>
            <li>Sử dụng tài khoản: <strong>admin</strong> / <strong>123456</strong></li>
        </ol>
    </div>
</body>
</html>";
    
} catch (PDOException $e) {
    echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Installation Error</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .error { background: #ef4444; color: white; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class='error'>
        <h2>❌ Lỗi cài đặt</h2>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
        <p>Vui lòng kiểm tra cấu hình database trong file <code>config.php</code></p>
    </div>
</body>
</html>";
}
?>

