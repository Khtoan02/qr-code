<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
requireAuth();

header('Content-Type: application/json');

$filter = $_GET['filter'] ?? 'ALL';
$transactions = Database::getTransactions($filter);

echo json_encode(['transactions' => $transactions]);
?>

