<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Get all orders for user
$stmt = $pdo->prepare("
    SELECT o.*, r.name as restaurant_name, COUNT(oi.id) as item_count
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<div class="pt-24 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">My Orders</h1>
        
        <?php if (!empty($orders)): ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
                                <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($order['restaurant_name']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-indigo-600"><?php echo formatPrice($order['total']); ?></p>
                                <?php echo getStatusBadge($order['order_status']); ?>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-sm text-gray-500"><?php echo $order['item_count']; ?> items</span>
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                               class="text-indigo-600 hover:text-indigo-700 font-medium text-sm">
                                View Details <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <div class="text-6xl mb-4">📦</div>
                <h3 class="text-xl font-bold text-gray-800">No Orders Yet</h3>
                <p class="text-gray-500 mt-2">You haven't placed any orders yet.</p>
                <a href="<?php echo SITE_URL; ?>menu.php" class="inline-block mt-4 btn-primary text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                    <i class="fas fa-utensils mr-2"></i> Browse Menu
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>