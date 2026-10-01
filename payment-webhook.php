<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Log webhook for debugging
$logFile = __DIR__ . '/webhook.log';
$rawInput = file_get_contents('php://input');
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $rawInput . "\n", FILE_APPEND);

// Parse webhook data
$data = json_decode($rawInput, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit();
}

// Extract data
$event = $data['event'] ?? '';
$paymentData = $data['data'] ?? [];
$orderReference = $paymentData['reference'] ?? '';
$status = $paymentData['status'] ?? '';
$transactionId = $paymentData['transactionId'] ?? '';
$amount = $paymentData['amount'] ?? 0;

// Handle different events
switch ($event) {
    case 'PAYMENT RECEIVED':
        handlePaymentReceived($pdo, $orderReference, $transactionId, $amount);
        break;
        
    case 'PAYMENT FAILED':
        handlePaymentFailed($pdo, $orderReference, $transactionId);
        break;
        
    case 'DEPOSIT RECEIVED':
        handleDepositReceived($pdo, $transactionId, $amount);
        break;
        
    default:
        file_put_contents($logFile, "Unknown event: $event\n", FILE_APPEND);
}

http_response_code(200);
echo json_encode(['status' => 'success']);
exit();

// ============================================================
// HANDLER FUNCTIONS
// ============================================================

function handlePaymentReceived($pdo, $orderReference, $transactionId, $amount) {
    try {
        // Find order by order number
        $stmt = $pdo->prepare("SELECT id, user_id, total FROM orders WHERE order_number = ?");
        $stmt->execute([$orderReference]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Update order payment status
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET payment_status = 'paid', order_status = 'confirmed', updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$order['id']]);
            
            // Update payment record
            $stmt = $pdo->prepare("
                UPDATE payments 
                SET status = 'completed', transaction_reference = ? 
                WHERE order_id = ?
            ");
            $stmt->execute([$transactionId, $order['id']]);
            
            // Create notification for customer
            $message = "Your payment of TZS " . number_format($amount, 0) . " has been received. Your order is now confirmed!";
            createNotification($pdo, $order['user_id'], 'Payment Confirmed', $message);
            
            // Create admin notification
            createAdminNotification(
                $pdo, 
                '💰 Payment Received', 
                "Order #$orderReference - TZS " . number_format($amount, 0)
            );
            
            error_log("Payment received: Order #$orderReference, Amount: $amount");
        }
    } catch (Exception $e) {
        error_log("Error handling payment: " . $e->getMessage());
    }
}

function handlePaymentFailed($pdo, $orderReference, $transactionId) {
    try {
        $stmt = $pdo->prepare("SELECT id, user_id FROM orders WHERE order_number = ?");
        $stmt->execute([$orderReference]);
        $order = $stmt->fetch();
        
        if ($order) {
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET payment_status = 'failed', updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$order['id']]);
            
            $stmt = $pdo->prepare("
                UPDATE payments 
                SET status = 'failed', transaction_reference = ? 
                WHERE order_id = ?
            ");
            $stmt->execute([$transactionId, $order['id']]);
            
            $message = "Your payment for order #$orderReference failed. Please try again.";
            createNotification($pdo, $order['user_id'], 'Payment Failed', $message);
        }
    } catch (Exception $e) {
        error_log("Error handling failed payment: " . $e->getMessage());
    }
}

function handleDepositReceived($pdo, $transactionId, $amount) {
    try {
        createAdminNotification(
            $pdo, 
            '💵 Deposit Received', 
            "TZS " . number_format($amount, 0) . " - Transaction: $transactionId"
        );
    } catch (Exception $e) {
        error_log("Error handling deposit: " . $e->getMessage());
    }
}
?>