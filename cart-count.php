<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

$count = 0;
if (isLoggedIn()) {
    $count = getCartCount($pdo, $_SESSION['user_id']);
}

echo json_encode(['count' => $count]);