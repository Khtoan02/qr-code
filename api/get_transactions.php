<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
requireAuth();

header('Content-Type: application/json');

$currentUser = getCurrentUser();
$userId = isAdmin() ? null : $currentUser['id'];
$filter = $_GET['filter'] ?? 'ALL';
$transactions = Database::getTransactions($filter, $userId);

echo json_encode(['transactions' => $transactions]);
?>

