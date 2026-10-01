<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin-auth.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$lastCheck = isset($_SESSION['last_order_check']) ? $_SESSION['last_order_check'] : date('Y-m-d H:i:s', strtotime('-5 minutes'));

$stmt = $pdo->prepare("
    SELECT 
        o.id, 
        o.order_number, 
        o.total,
        u.name as customer_name,
        r.name as restaurant_name,
        o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.created_at > ? 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute([$lastCheck]);
$orders = $stmt->fetchAll();

$_SESSION['last_order_check'] = date('Y-m-d H:i:s');

if (!empty($orders)) {
    $formattedOrders = [];
    foreach ($orders as $order) {
        $formattedOrders[] = [
            'order_number' => $order['order_number'],
            'customer' => $order['customer_name'],
            'restaurant' => $order['restaurant_name'],
            'total' => number_format($order['total'], 0),
            'time' => date('h:i A', strtotime($order['created_at']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'new_orders' => true,
        'count' => count($orders),
        'orders' => $formattedOrders
    ]);
} else {
    echo json_encode([
        'success' => true,
        'new_orders' => false,
        'count' => 0
    ]);
}
?>