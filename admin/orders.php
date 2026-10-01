<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

// Update order status - PRG Pattern
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $validStatuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
    
    if (in_array($status, $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        
        // Create notification for customer
        $stmt = $pdo->prepare("SELECT user_id, order_number FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order) {
            createNotification($pdo, $order['user_id'], 'Order Updated', 
                "Your order #{$order['order_number']} status changed to: " . ucfirst(str_replace('_', ' ', $status)));
        }
        
        // Redirect kwenye orders page (GET request) - HII NDIO SULUHISHO!
        header('Location: orders.php?updated=1');
        exit();
    }
}

// Get status filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "
    SELECT o.*, u.name as customer_name, u.email as customer_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
";
$params = [];

if ($statusFilter && $statusFilter !== 'all') {
    $query .= " WHERE o.order_status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$success = isset($_GET['updated']) ? 'Order status updated successfully!' : '';
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Order Management</h1>
                <p class="text-gray-500 mt-1">Manage and update customer orders</p>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Status Filter -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <form method="GET" action="" class="flex flex-wrap gap-2">
                <a href="orders.php" class="px-4 py-2 rounded-lg <?php echo $statusFilter === '' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'; ?>">
                    All
                </a>
                <?php 
                $statuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
                $labels = [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'preparing' => 'Preparing',
                    'out_for_delivery' => 'Out for Delivery',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled'
                ];
                foreach ($statuses as $status): ?>
                    <a href="?status=<?php echo $status; ?>" class="px-4 py-2 rounded-lg <?php echo $statusFilter === $status ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'; ?>">
                        <?php echo $labels[$status]; ?>
                    </a>
                <?php endforeach; ?>
            </form>
        </div>
        
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-indigo-600">
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>">
                                            <?php echo htmlspecialchars($order['order_number']); ?>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800">
                                        <?php echo formatPrice($order['total']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php echo getStatusBadge($order['order_status']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        <?php echo date('M d, H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center gap-2">
                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-700">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    No orders found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>