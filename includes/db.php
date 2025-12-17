<?php
require_once __DIR__ . '/../config.php';

class Database {
    private static $pdo = null;

    public static function getInstance() {
        if (self::$pdo === null) {
            self::$pdo = getDB();
        }
        return self::$pdo;
    }

    // Transaction Operations
    public static function getTransactions($filter = 'ALL') {
        $pdo = self::getInstance();
        $sql = "SELECT * FROM transactions ORDER BY created_at DESC";
        
        if ($filter !== 'ALL') {
            $sql = "SELECT * FROM transactions WHERE status = :status ORDER BY created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['status' => $filter]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    public static function getTransactionById($id) {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function saveTransaction($transaction) {
        $pdo = self::getInstance();
        $sql = "INSERT INTO transactions (id, payment_code, amount, description, customer_name, status, theme_image, created_at) 
                VALUES (:id, :payment_code, :amount, :description, :customer_name, :status, :theme_image, :created_at)";
        $stmt = $pdo->prepare($sql);
        
        // Handle timestamp - if it's in milliseconds, convert to seconds
        $timestamp = $transaction['created_at'] ?? time() * 1000;
        if ($timestamp > 9999999999) {
            $timestamp = $timestamp / 1000; // Convert milliseconds to seconds
        }
        
        return $stmt->execute([
            'id' => $transaction['id'],
            'payment_code' => $transaction['payment_code'],
            'amount' => $transaction['amount'],
            'description' => $transaction['description'],
            'customer_name' => $transaction['customer_name'],
            'status' => $transaction['status'],
            'theme_image' => $transaction['theme_image'] ?? null,
            'created_at' => date('Y-m-d H:i:s', $timestamp)
        ]);
    }

    public static function updateTransactionStatus($id, $status) {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("UPDATE transactions SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public static function getTotalRevenue() {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE status = 'PAID'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public static function getTransactionStats() {
        $pdo = self::getInstance();
        $stats = [];
        
        // Total transactions
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions");
        $stats['total'] = $stmt->fetch()['count'];
        
        // Pending transactions
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions WHERE status = 'PENDING'");
        $stats['pending'] = $stmt->fetch()['count'];
        
        // Paid transactions
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions WHERE status = 'PAID'");
        $stats['paid'] = $stmt->fetch()['count'];
        
        // Total revenue
        $stats['revenue'] = self::getTotalRevenue();
        
        return $stats;
    }
}
?>

