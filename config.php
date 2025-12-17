<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'paygen_db');
define('DB_USER', 'root');
define('DB_PASS', '1');
define('DB_CHARSET', 'utf8mb4');

// SePay API Configuration
define('SEPAY_API_KEY', 'TZ6IDLTMBQGTVGUOGSNXWOMQZD0FKR94D02MWZXE7CCQ7WFCRVKUXSHZEEVJ92YJ');
define('MAIN_ACCOUNT_NUMBER', '0329249536');
define('VA_ACCOUNT_NUMBER', 'VQRQAFYMM9200');

// Account Information for Display
define('ACCOUNT_NAME', 'NGUYEN KHANH TOAN');
define('BANK_NAME', 'MBBank');
define('BANK_CODE', 'MB');

// Gemini API Configuration
define('GEMINI_API_KEY', ''); // Set your Gemini API key here

// Application Settings
define('APP_NAME', 'PayGen Gateway');
define('SESSION_NAME', 'paygen_session');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Database Connection with auto-setup
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            // First, try to connect to the database
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Check if tables exist, if not, create them
            setupDatabase($pdo);
        } catch (PDOException $e) {
            // If database doesn't exist, try to create it
            if ($e->getCode() == 1049) {
                try {
                    $dsnNoDb = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                    $pdoTemp = new PDO($dsnNoDb, DB_USER, DB_PASS, $options);
                    $pdoTemp->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdoTemp->exec("USE " . DB_NAME);
                    $pdo = $pdoTemp;
                    setupDatabase($pdo);
                } catch (PDOException $e2) {
                    die("Database setup failed: " . $e2->getMessage());
                }
            } else {
                die("Database connection failed: " . $e->getMessage());
            }
        }
    }
    return $pdo;
}

// Setup database tables if they don't exist
function setupDatabase($pdo) {
    try {
        // Check if transactions table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'transactions'");
        if ($stmt->rowCount() == 0) {
            // Create transactions table
            $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
                id VARCHAR(36) PRIMARY KEY,
                payment_code VARCHAR(20) NOT NULL UNIQUE,
                amount DECIMAL(15, 2) NOT NULL,
                description TEXT,
                customer_name VARCHAR(255) DEFAULT 'Khách lẻ',
                status ENUM('PENDING', 'PAID', 'FAILED') DEFAULT 'PENDING',
                theme_image TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_payment_code (payment_code),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        
        // Check if users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() == 0) {
            // Create users table
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            // Insert default admin user (password: 123456)
            $pdo->exec("INSERT IGNORE INTO users (username, password_hash) VALUES 
                ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')");
        }
    } catch (PDOException $e) {
        // Silently fail - tables might already exist
        error_log("Database setup warning: " . $e->getMessage());
    }
}

// Helper function to check authentication
function isAuthenticated() {
    return isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated'] === true;
}

// Helper function to require authentication
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /login.php');
        exit;
    }
}

// Helper function to format currency
function formatVND($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

// Helper function to generate payment code
function generatePaymentCode() {
    return 'DH' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}
?>

