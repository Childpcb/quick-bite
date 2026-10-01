<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: orders.php');
    exit();
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
           r.name as restaurant_name, r.phone as restaurant_phone,
           a.address, a.city, a.region, a.phone as delivery_phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN restaurants r ON o.restaurant_id = r.id
    JOIN addresses a ON o.address_id = a.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, f.name as food_name
    FROM order_items oi
    JOIN food_items f ON oi.food_id = f.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();

// Get payment
$stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->execute([$orderId]);
$payment = $stmt->fetch();
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="orders.php" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Order Details - #<?php echo htmlspecialchars($order['order_number']); ?></h1>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Items -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Order Items</h2>
                    <div class="space-y-3">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                <div class="w-16 h-16 rounded-lg flex items-center justify-center text-4xl" style="background: linear-gradient(135deg, <?php 
                                    $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#FF8A5C', '#A29BFE'];
                                    echo $colors[$item['food_id'] % count($colors)];
                                ?> 0%, #2D3436 100%);">
                                    <?php 
                                    $icons = [1 => '🍕', 2 => '🍔', 3 => '🍗', 4 => '🍟', 5 => '🍛', 6 => '🍛', 7 => '🥤', 8 => '🍰', 9 => '🍚', 10 => '🥗'];
                                    echo $icons[$item['food_id'] % 10] ?? '🍽️';
                                    ?>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($item['food_name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $item['quantity']; ?> x <?php echo formatPrice($item['unit_price']); ?></p>
                                </div>
                                <p class="font-bold text-gray-800"><?php echo formatPrice($item['subtotal']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium"><?php echo formatPrice($order['subtotal']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Delivery Fee</span>
                            <span class="font-medium"><?php echo formatPrice($order['delivery_fee']); ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span>-<?php echo formatPrice($order['discount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-indigo-600"><?php echo formatPrice($order['total']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Info -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Customer Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer_phone']); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Delivery Info -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Delivery Information</h2>
                    <div class="space-y-2">
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['address']); ?></p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($order['city'] . ', ' . $order['region']); ?></p>
                        <?php if ($order['delivery_phone']): ?>
                            <p class="text-gray-600">Phone: <?php echo htmlspecialchars($order['delivery_phone']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Update Status -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Update Status</h2>
                    <form method="POST" action="orders.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="update_status" value="1">
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-4">
                            <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="preparing" <?php echo $order['order_status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                            <option value="out_for_delivery" <?php echo $order['order_status'] === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                            <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="w-full btn-primary text-white px-4 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                            <i class="fas fa-sync mr-2"></i> Update Status
                        </button>
                    </form>
                </div>
                
                <!-- Payment Info -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Payment</h2>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-500">Method</p>
                        <p class="font-medium text-gray-800"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        <p class="text-sm text-gray-500">Status</p>
                        <p><?php echo getPaymentBadge($order['payment_status']); ?></p>
                    </div>
                </div>
                
                <!-- Order Info -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Order Info</h2>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-500">Placed On</p>
                        <p class="font-medium text-gray-800"><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></p>
                        <p class="text-sm text-gray-500 mt-3">Restaurant</p>
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['restaurant_name']); ?></p>
                        <?php if ($order['notes']): ?>
                            <p class="text-sm text-gray-500 mt-3">Notes</p>
                            <p class="text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>