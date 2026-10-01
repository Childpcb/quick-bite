<?php
// ============================================================
// QUICK BITE - CANCEL ORDER
// Location: C:\xampp\htdocs\quick-bite\cancel-order.php
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login first'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

// Validate order ID
if ($orderId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid order ID'
    ]);
    exit();
}

try {
    // Verify order belongs to user and is still cancellable
    $stmt = $pdo->prepare("
        SELECT id, order_number, order_status, user_id 
        FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
        exit();
    }

    // Check if order can be cancelled
    $cancellableStatuses = ['pending', 'confirmed'];
    
    if (!in_array($order['order_status'], $cancellableStatuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Order cannot be cancelled at this stage. Current status: ' . ucfirst(str_replace('_', ' ', $order['order_status']))
        ]);
        exit();
    }

    // Update order status to cancelled
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET order_status = 'cancelled', updated_at = NOW() 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$orderId, $userId]);

    // Also update payment status if exists and is pending
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET status = 'failed' 
        WHERE order_id = ? AND status = 'pending'
    ");
    $stmt->execute([$orderId]);

    // Create notification for customer
    createNotification(
        $pdo, 
        $userId, 
        'Order Cancelled', 
        "Your order #{$order['order_number']} has been cancelled successfully."
    );

    // Create notification for admin
    createAdminNotification(
        $pdo,
        '❌ Order Cancelled',
        "Order #{$order['order_number']} was cancelled by the customer."
    );

    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully',
        'order_number' => $order['order_number']
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>