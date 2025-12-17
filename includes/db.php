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
    public static function getTransactions($filter = 'ALL', $userId = null, $includeUserInfo = false) {
        $pdo = self::getInstance();
        $conditions = [];
        $params = [];
        
        // Filter by user if provided (for employees)
        if ($userId !== null) {
            $conditions[] = "t.created_by = :user_id";
            $params['user_id'] = $userId;
        }
        
        // Filter by status
        if ($filter !== 'ALL') {
            $conditions[] = "t.status = :status";
            $params['status'] = $filter;
        }
        
        // Build SQL with or without user info
        if ($includeUserInfo) {
            $sql = "SELECT t.*, u.username as created_by_username, u.role as created_by_role 
                    FROM transactions t 
                    LEFT JOIN users u ON t.created_by = u.id";
        } else {
            $sql = "SELECT * FROM transactions t";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY t.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
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
        $sql = "INSERT INTO transactions (id, payment_code, amount, description, customer_name, created_by, status, theme_image, created_at) 
                VALUES (:id, :payment_code, :amount, :description, :customer_name, :created_by, :status, :theme_image, :created_at)";
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
            'created_by' => $transaction['created_by'] ?? null,
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

    public static function getTotalRevenue($userId = null) {
        $pdo = self::getInstance();
        $sql = "SELECT SUM(amount) as total FROM transactions WHERE status = 'PAID'";
        $params = [];
        
        if ($userId !== null) {
            $sql .= " AND created_by = :user_id";
            $params['user_id'] = $userId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public static function getTransactionStats($userId = null) {
        $pdo = self::getInstance();
        $stats = [];
        $whereClause = $userId !== null ? " WHERE created_by = " . intval($userId) : "";
        
        // Total transactions
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions" . $whereClause);
        $stats['total'] = $stmt->fetch()['count'];
        
        // Pending transactions
        $pendingWhere = $userId !== null ? " WHERE created_by = " . intval($userId) . " AND status = 'PENDING'" : " WHERE status = 'PENDING'";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions" . $pendingWhere);
        $stats['pending'] = $stmt->fetch()['count'];
        
        // Paid transactions
        $paidWhere = $userId !== null ? " WHERE created_by = " . intval($userId) . " AND status = 'PAID'" : " WHERE status = 'PAID'";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions" . $paidWhere);
        $stats['paid'] = $stmt->fetch()['count'];
        
        // Total revenue
        $stats['revenue'] = self::getTotalRevenue($userId);
        
        return $stats;
    }

    // User Operations
    public static function getUserByUsername($username) {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    public static function getUserById($id) {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function getAllUsers() {
        $pdo = self::getInstance();
        $stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public static function createUser($username, $password, $role = 'employee') {
        $pdo = self::getInstance();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)");
        return $stmt->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'role' => $role
        ]);
    }

    public static function deleteUser($id) {
        $pdo = self::getInstance();
        // Don't allow deleting admin users
        $user = self::getUserById($id);
        if ($user && $user['role'] === 'admin') {
            return false;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function updateUserPassword($id, $newPassword) {
        $pdo = self::getInstance();
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'password_hash' => $passwordHash
        ]);
    }
}
?>

