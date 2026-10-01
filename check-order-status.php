<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Order ID']);
    exit();
}

// Get current status
$stmt = $pdo->prepare("SELECT order_status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Status badge function
function getStatusBadgeCustomer($status) {
    $badges = [
        'pending' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Pending</span>',
        'confirmed' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">Confirmed</span>',
        'preparing' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">Preparing</span>',
        'out_for_delivery' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 animate-pulse">Out for Delivery</span>',
        'delivered' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Delivered</span>',
        'cancelled' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Cancelled</span>',
    ];
    return $badges[$status] ?? '<span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">'.ucfirst($status).'</span>';
}

echo json_encode([
    'success' => true,
    'status' => $order['order_status'],
    'html' => getStatusBadgeCustomer($order['order_status'])
]);
?>