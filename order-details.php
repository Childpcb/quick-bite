<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: ' . SITE_URL . 'orders.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, r.name as restaurant_name, r.phone as restaurant_phone,
           a.address, a.city, a.region, a.phone as delivery_phone
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.id
    JOIN addresses a ON o.address_id = a.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . SITE_URL . 'orders.php');
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, f.name as food_name, f.image
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

// Status steps for tracking
$statusSteps = [
    'pending' => ['label' => 'Order Placed', 'icon' => 'fa-clipboard-check', 'completed' => true],
    'confirmed' => ['label' => 'Order Confirmed', 'icon' => 'fa-check-circle', 'completed' => in_array($order['order_status'], ['confirmed', 'preparing', 'out_for_delivery', 'delivered'])],
    'preparing' => ['label' => 'Preparing', 'icon' => 'fa-utensils', 'completed' => in_array($order['order_status'], ['preparing', 'out_for_delivery', 'delivered'])],
    'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'fa-truck', 'completed' => in_array($order['order_status'], ['out_for_delivery', 'delivered'])],
    'delivered' => ['label' => 'Delivered', 'icon' => 'fa-home', 'completed' => $order['order_status'] === 'delivered'],
];
$currentStepIndex = array_search($order['order_status'], array_keys($statusSteps));
$currentStepLabel = $statusSteps[$order['order_status']]['label'] ?? 'Pending';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<div class="pt-24 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <!-- Back button -->
        <a href="<?php echo SITE_URL; ?>orders.php" class="text-indigo-600 hover:text-indigo-700 font-medium inline-block mb-6">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
        
        <!-- Order Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-800">Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                        <?php echo getStatusBadge($order['order_status']); ?>
                    </div>
                    <p class="text-gray-500 mt-1">Placed on <?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="text-2xl font-bold text-indigo-600"><?php echo formatPrice($order['total']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Tracking Timeline -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6">Order Tracking</h2>
            <div class="relative">
                <?php $totalSteps = count($statusSteps); $step = 0; ?>
                <?php foreach ($statusSteps as $key => $status): ?>
                    <?php 
                    $isCompleted = $status['completed'];
                    $isCurrent = $key === $order['order_status'];
                    $step++;
                    ?>
                    <div class="flex items-center mb-6 relative">
                        <!-- Line connector -->
                        <?php if ($step < $totalSteps): ?>
                            <div class="absolute left-5 top-10 h-12 w-0.5 <?php echo $isCompleted ? 'bg-indigo-600' : 'bg-gray-300'; ?>"></div>
                        <?php endif; ?>
                        
                        <!-- Icon -->
                        <div class="relative z-10">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center 
                                      <?php echo $isCompleted ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400'; ?>
                                      <?php echo $isCurrent ? 'ring-4 ring-indigo-300' : ''; ?>">
                                <i class="fas <?php echo $status['icon']; ?>"></i>
                            </div>
                        </div>
                        
                        <!-- Label -->
                        <div class="ml-4 flex-1">
                            <p class="font-medium <?php echo $isCompleted ? 'text-gray-800' : 'text-gray-400'; ?>">
                                <?php echo $status['label']; ?>
                            </p>
                            <?php if ($isCurrent): ?>
                                <p class="text-sm text-indigo-600 font-medium">Current Status</p>
                            <?php endif; ?>
                            <?php if ($isCompleted && $key !== 'delivered'): ?>
                                <p class="text-xs text-green-500"><i class="fas fa-check mr-1"></i> Completed</p>
                            <?php endif; ?>
                            <?php if ($key === 'delivered' && $isCompleted): ?>
                                <p class="text-xs text-green-500"><i class="fas fa-check-circle mr-1"></i> Delivered successfully!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Order Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Order Items -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Order Items</h2>
                    <div class="space-y-3">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                <img src="<?php echo FOOD_IMAGE_URL . ($item['image'] ?: DEFAULT_FOOD_IMAGE); ?>" 
                                     alt="<?php echo htmlspecialchars($item['food_name']); ?>" 
                                     class="w-16 h-16 rounded-lg object-cover">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($item['food_name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $item['quantity']; ?> x <?php echo formatPrice($item['unit_price']); ?></p>
                                </div>
                                <p class="font-bold text-gray-800"><?php echo formatPrice($item['subtotal']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Order Summary</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span><?php echo formatPrice($order['subtotal']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Delivery Fee</span>
                            <span><?php echo formatPrice($order['delivery_fee']); ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span>-<?php echo formatPrice($order['discount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-indigo-600"><?php echo formatPrice($order['total']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Info -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Payment Method</p>
                        <p class="font-medium text-gray-800"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        <p class="text-sm mt-1">
                            Status: <?php echo getPaymentBadge($order['payment_status']); ?>
                        </p>
                    </div>
                    
                    <!-- Delivery Info -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Delivery Address</p>
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['address']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($order['city'] . ', ' . $order['region']); ?></p>
                        <?php if ($order['delivery_phone']): ?>
                            <p class="text-sm text-gray-500">Phone: <?php echo htmlspecialchars($order['delivery_phone']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($order['notes']): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">Notes</p>
                            <p class="text-sm text-gray-500"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['order_status'] === 'pending'): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <button onclick="cancelOrder(<?php echo $order['id']; ?>)" 
                                    class="w-full bg-red-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-600 transition">
                                <i class="fas fa-times mr-2"></i> Cancel Order
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;
    
    fetch('<?php echo SITE_URL; ?>cancel-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to cancel order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>