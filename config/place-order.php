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

// Verify address belongs to user
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
$stmt->execute([$addressId, $userId]);
$address = $stmt->fetch();

if (!$address) {
    $_SESSION['flash_message'] = 'Invalid address selected.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . SITE_URL . 'checkout.php');
    exit();
}

// Get restaurant ID from cart items
$firstItem = $cartItems[0];
$restaurantId = $firstItem['restaurant_id'];

// Verify all items are from same restaurant
foreach ($cartItems as $item) {
    if ($item['restaurant_id'] !== $restaurantId) {
        $_SESSION['flash_message'] = 'All items must be from the same restaurant.';
        $_SESSION['flash_type'] = 'error';
        header('Location: ' . SITE_URL . 'cart.php');
        exit();
    }
}

// Get customer name for notification
$stmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
$stmt->execute([$userId]);
$customer = $stmt->fetch();

// Get restaurant name for notification
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