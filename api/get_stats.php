<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
requireAuth();

header('Content-Type: application/json');

$currentUser = getCurrentUser();
$userId = isAdmin() ? null : $currentUser['id'];
$stats = Database::getTransactionStats($userId);
echo json_encode($stats);
?>

