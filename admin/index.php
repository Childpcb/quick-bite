<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

$admin = getCurrentAdmin($pdo);

// Get statistics
$stats = [];

// Total Customers
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$stats['customers'] = $stmt->fetch()['count'] ?? 0;

// Total Restaurants
$stmt = $pdo->query("SELECT COUNT(*) as count FROM restaurants WHERE status = 'active'");
$stats['restaurants'] = $stmt->fetch()['count'] ?? 0;

// Total Food Items
$stmt = $pdo->query("SELECT COUNT(*) as count FROM food_items WHERE status = 'active'");
$stats['food_items'] = $stmt->fetch()['count'] ?? 0;

// Total Orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch()['count'] ?? 0;

// Pending Orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
$stats['pending_orders'] = $stmt->fetch()['count'] ?? 0;

// Delivered Orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'delivered'");
$stats['delivered_orders'] = $stmt->fetch()['count'] ?? 0;

// Total Revenue
$stmt = $pdo->query("SELECT SUM(total) as total FROM orders WHERE order_status = 'delivered'");
$stats['revenue'] = $stmt->fetch()['total'] ?? 0;

// Today's Sales
$stmt = $pdo->query("SELECT SUM(total) as total FROM orders WHERE DATE(created_at) = CURDATE() AND order_status = 'delivered'");
$stats['today_sales'] = $stmt->fetch()['total'] ?? 0;

// Recent Orders
$stmt = $pdo->query("
    SELECT o.*, u.name as customer_name, r.name as restaurant_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN restaurants r ON o.restaurant_id = r.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recentOrders = $stmt->fetchAll();

// Get notifications
$stmt = $pdo->query("SELECT * FROM notifications WHERE user_id = 1 ORDER BY created_at DESC LIMIT 5");
$notifications = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Welcome -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($admin['name']); ?>!</h1>
            <p class="text-gray-500">Here's what's happening with your restaurant today.</p>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Customers</p>
                <p class="text-2xl font-bold text-indigo-600"><?php echo number_format($stats['customers']); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Restaurants</p>
                <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['restaurants']); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Food Items</p>
                <p class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['food_items']); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Total Orders</p>
                <p class="text-2xl font-bold text-orange-600"><?php echo number_format($stats['orders']); ?></p>
            </div>
        </div>
        
        <!-- Second Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Pending Orders</p>
                <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['pending_orders']); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Delivered Orders</p>
                <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['delivered_orders']); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Total Revenue</p>
                <p class="text-2xl font-bold text-indigo-600"><?php echo formatPrice($stats['revenue']); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Today's Sales</p>
                <p class="text-2xl font-bold text-pink-600"><?php echo formatPrice($stats['today_sales']); ?></p>
            </div>
        </div>
        
        <!-- Notifications -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800">🔔 Notifications</h2>
                <span class="text-sm text-gray-500">
                    <?php 
                    $unread = 0;
                    foreach ($notifications as $n) {
                        if (!$n['is_read']) $unread++;
                    }
                    echo $unread > 0 ? "$unread new" : "All read";
                    ?>
                </span>
            </div>
            <div class="p-4">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition <?php echo $notif['is_read'] ? '' : 'bg-indigo-50 border-l-4 border-indigo-600'; ?>">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-bell text-indigo-600 text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($notif['title']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <p class="text-xs text-gray-400 mt-1"><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No notifications</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800">Recent Orders</h2>
                <a href="orders.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View All →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($recentOrders)): ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td class="px-4 py-3 text-sm font-medium text-indigo-600"><?php echo formatPrice($order['total']); ?></td>
                                    <td class="px-4 py-3 text-sm"><?php echo getStatusBadge($order['order_status']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>