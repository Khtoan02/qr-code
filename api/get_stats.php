<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
requireAuth();

header('Content-Type: application/json');

$stats = Database::getTransactionStats();
echo json_encode($stats);
?>

