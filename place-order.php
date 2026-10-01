<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . 'checkout.php');
    exit();
}

$userId = $_SESSION['user_id'];
$cartItems = getCartItems($pdo, $userId);

if (empty($cartItems)) {
    header('Location: ' . SITE_URL . 'cart.php');
    exit();
}

// Get form data
$addressId = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;
$paymentMethod = $_POST['payment_method'] ?? 'cash';
$notes = trim($_POST['notes'] ?? '');
$subtotal = (float)$_POST['subtotal'] ?? 0;
$deliveryFee = (float)$_POST['delivery_fee'] ?? 0;
$discount = (float)$_POST['discount'] ?? 0;
$total = (float)$_POST['total'] ?? 0;

// Validate address
if ($addressId <= 0) {
    $_SESSION['flash_message'] = 'Please select a delivery address.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . SITE_URL . 'checkout.php');
    exit();
}

// Verify address
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
$stmt->execute([$addressId, $userId]);
$address = $stmt->fetch();

if (!$address) {
    $_SESSION['flash_message'] = 'Invalid address selected.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . SITE_URL . 'checkout.php');
    exit();
}

// Get restaurant ID
$firstItem = $cartItems[0];
$restaurantId = $firstItem['restaurant_id'];

foreach ($cartItems as $item) {
    if ($item['restaurant_id'] !== $restaurantId) {
        $_SESSION['flash_message'] = 'All items must be from the same restaurant.';
        $_SESSION['flash_type'] = 'error';
        header('Location: ' . SITE_URL . 'cart.php');
        exit();
    }
}

// Get customer info
$stmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
$stmt->execute([$userId]);
$customer = $stmt->fetch();

// Get restaurant name
$stmt = $pdo->prepare("SELECT name FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();

try {
    $pdo->beginTransaction();
    
    // Generate order number
    $orderNumber = generateOrderNumber();
    
    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, restaurant_id, address_id, order_number, subtotal, delivery_fee, discount, total, payment_method, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId, $restaurantId, $addressId, $orderNumber, $subtotal, $deliveryFee, $discount, $total, $paymentMethod, $notes
    ]);
    $orderId = $pdo->lastInsertId();
    
    // Create order items
    foreach ($cartItems as $item) {
        $itemSubtotal = $item['price'] * $item['quantity'];
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, food_id, quantity, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $item['food_id'], $item['quantity'], $item['price'], $itemSubtotal]);
    }
    
    // Create payment record
    $stmt = $pdo->prepare("
        INSERT INTO payments (order_id, payment_method, amount, status)
        VALUES (?, ?, ?, 'pending')
    ");
    $stmt->execute([$orderId, $paymentMethod, $total]);
    
    // ============================================================
    // SEND EMAIL NOTIFICATION TO ADMIN
    // ============================================================
    $adminEmail = 'alisonnaftali2003@gmail.com'; // BADILISHA HAPA
    $subject = "🔔 NEW ORDER #$orderNumber";
    
    // Build email content
    $emailMessage = "
    <html>
    <head>
        <title>New Order Alert</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 10px; }
            .header { background: #6C63FF; color: white; padding: 15px; border-radius: 10px 10px 0 0; text-align: center; }
            .content { background: white; padding: 20px; border-radius: 0 0 10px 10px; }
            .details { background: #f0f2f5; padding: 15px; border-radius: 8px; margin: 10px 0; }
            .btn { display: inline-block; background: #6C63FF; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🛒 New Order Placed!</h2>
            </div>
            <div class='content'>
                <h3>Order #$orderNumber</h3>
                <div class='details'>
                    <p><strong>Customer:</strong> {$customer['name']}</p>
                    <p><strong>Phone:</strong> {$customer['phone']}</p>
                    <p><strong>Restaurant:</strong> {$restaurant['name']}</p>
                    <p><strong>Total:</strong> TZS " . number_format($total, 0) . "</p>
                    <p><strong>Payment:</strong> " . ucfirst($paymentMethod) . "</p>
                    <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                </div>
                <a href='http://localhost/quick-bite/admin/orders.php' class='btn'>View Order</a>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Quick Bite <orders@quickbite.com>\r\n";
    
    // Send email
    $emailSent = mail($adminEmail, $subject, $emailMessage, $headers);
    
    // Log result
    if ($emailSent) {
        error_log("✅ Email sent to $adminEmail for order #$orderNumber");
    } else {
        error_log("❌ Email failed for order #$orderNumber");
    }
    
    // ============================================================
    // CREATE NOTIFICATION IN DATABASE
    // ============================================================
    $notifTitle = "🛒 New Order #$orderNumber";
    $notifMessage = "Customer: {$customer['name']} | Total: TZS " . number_format($total, 0);
    createAdminNotification($pdo, $notifTitle, $notifMessage);
    
    // Clear cart
    $stmt = $pdo->prepare("
        DELETE ci FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    
    $pdo->commit();
    
    // Redirect to order confirmation
    header('Location: ' . SITE_URL . 'order-details.php?id=' . $orderId);
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash_message'] = 'Failed to place order. Please try again.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . SITE_URL . 'checkout.php');
    exit();
}
?>