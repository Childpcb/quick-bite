<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $foodId = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if ($foodId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid food item']);
            exit();
        }
        
        // Check if food exists and is available
        $stmt = $pdo->prepare("SELECT id, is_available FROM food_items WHERE id = ? AND status = 'active'");
        $stmt->execute([$foodId]);
        $food = $stmt->fetch();
        
        if (!$food || !$food['is_available']) {
            echo json_encode(['success' => false, 'message' => 'Food item is not available']);
            exit();
        }
        
        // Get or create cart
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();
        
        if (!$cart) {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
            $stmt->execute([$userId]);
            $cartId = $pdo->lastInsertId();
        } else {
            $cartId = $cart['id'];
        }
        
        // Check if food already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND food_id = ?");
        $stmt->execute([$cartId, $foodId]);
        $cartItem = $stmt->fetch();
        
        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $cartItem['id']]);
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, food_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$cartId, $foodId, $quantity]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
        break;
        
    case 'update':
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        
        if ($itemId <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit();
        }
        
        $stmt = $pdo->prepare("
            UPDATE cart_items ci
            JOIN cart c ON ci.cart_id = c.id
            SET ci.quantity = ?
            WHERE ci.id = ? AND c.user_id = ?
        ");
        $stmt->execute([$quantity, $itemId, $userId]);
        
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
        break;
        
    case 'remove':
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        
        if ($itemId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit();
        }
        
        $stmt = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN cart c ON ci.cart_id = c.id
            WHERE ci.id = ? AND c.user_id = ?
        ");
        $stmt->execute([$itemId, $userId]);
        
        echo json_encode(['success' => true, 'message' => 'Item removed']);
        break;
        
    case 'clear':
        $stmt = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN cart c ON ci.cart_id = c.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        
        echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>