<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

// Get date filter
$period = isset($_GET['period']) ? $_GET['period'] : 'today';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

switch ($period) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-365 days'));
        $end_date = date('Y-m-d');
        break;
    case 'custom':
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        break;
}

// Get sales data
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total) as total_revenue,
        SUM(delivery_fee) as total_delivery_fee,
        SUM(discount) as total_discount,
        AVG(total) as average_order_value
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND order_status = 'delivered'
");
$stmt->execute([$start_date, $end_date]);
$sales = $stmt->fetch();

// Get orders by status
$stmt = $pdo->prepare("
    SELECT order_status, COUNT(*) as count 
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY order_status
");
$stmt->execute([$start_date, $end_date]);
$orderStatuses = $stmt->fetchAll();

// Get popular foods
$stmt = $pdo->prepare("
    SELECT f.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue
    FROM order_items oi
    JOIN food_items f ON oi.food_id = f.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.order_status = 'delivered'
    GROUP BY oi.food_id
    ORDER BY total_sold DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$popularFoods = $stmt->fetchAll();

// Get popular restaurants
$stmt = $pdo->prepare("
    SELECT r.name, COUNT(o.id) as total_orders, SUM(o.total) as total_revenue
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.order_status = 'delivered'
    GROUP BY o.restaurant_id
    ORDER BY total_orders DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$popularRestaurants = $stmt->fetchAll();

// Daily sales for chart
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND order_status = 'delivered'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$start_date, $end_date]);
$dailySales = $stmt->fetchAll();

// Get customer stats
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT user_id) as total_customers,
           COUNT(DISTINCT CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN user_id END) as active_customers
    FROM orders
");
$stmt->execute([$start_date, $end_date]);
$customerStats = $stmt->fetch();

// Get top customers
$stmt = $pdo->prepare("
    SELECT u.name, u.email, COUNT(o.id) as total_orders, SUM(o.total) as total_spent
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.order_status = 'delivered'
    GROUP BY o.user_id
    ORDER BY total_spent DESC
    LIMIT 5
");
$stmt->execute([$start_date, $end_date]);
$topCustomers = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
                <p class="text-gray-500 mt-1">View sales and order statistics</p>
            </div>
        </div>
        
        <!-- Date Filter -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <form method="GET" action="" class="flex flex-wrap gap-4 items-center">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                    <select name="period" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="today" <?php echo $period == 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $period == 'week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>This Year</option>
                        <option value="custom" <?php echo $period == 'custom' ? 'selected' : ''; ?>>Custom</option>
                    </select>
                </div>
                
                <?php if ($period == 'custom'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>"
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>"
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition self-end">
                        <i class="fas fa-search mr-2"></i> Apply
                    </button>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Total Revenue</p>
                <p class="text-2xl font-bold text-indigo-600"><?php echo formatPrice($sales['total_revenue'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Total Orders</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($sales['total_orders'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Average Order Value</p>
                <p class="text-2xl font-bold text-green-600"><?php echo formatPrice($sales['average_order_value'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-sm text-gray-500">Active Customers</p>
                <p class="text-2xl font-bold text-orange-600"><?php echo number_format($customerStats['active_customers'] ?? 0); ?></p>
            </div>
        </div>
        
        <!-- Daily Sales Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Daily Sales</h2>
            <?php if (!empty($dailySales)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($dailySales as $day): ?>
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?php echo $day['orders']; ?></td>
                                    <td class="px-4 py-2 text-sm font-medium text-indigo-600"><?php echo formatPrice($day['revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">No sales data for this period.</p>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Order Status Distribution -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Order Status Distribution</h2>
                <?php if (!empty($orderStatuses)): ?>
                    <div class="space-y-2">
                        <?php foreach ($orderStatuses as $status): ?>
                            <div class="flex items-center justify-between">
                                <span><?php echo getStatusBadge($status['order_status']); ?></span>
                                <span class="font-medium"><?php echo $status['count']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No orders for this period.</p>
                <?php endif; ?>
            </div>
            
            <!-- Top Customers -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Top Customers</h2>
                <?php if (!empty($topCustomers)): ?>
                    <div class="space-y-3">
                        <?php foreach ($topCustomers as $customer): ?>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($customer['name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($customer['email']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-indigo-600"><?php echo formatPrice($customer['total_spent']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $customer['total_orders']; ?> orders</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No customer data for this period.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Popular Foods -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Most Popular Foods</h2>
                <?php if (!empty($popularFoods)): ?>
                    <div class="space-y-3">
                        <?php foreach ($popularFoods as $food): ?>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($food['name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $food['total_sold']; ?> sold</p>
                                </div>
                                <span class="font-bold text-indigo-600"><?php echo formatPrice($food['total_revenue']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No food data for this period.</p>
                <?php endif; ?>
            </div>
            
            <!-- Popular Restaurants -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Top Restaurants</h2>
                <?php if (!empty($popularRestaurants)): ?>
                    <div class="space-y-3">
                        <?php foreach ($popularRestaurants as $restaurant): ?>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($restaurant['name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $restaurant['total_orders']; ?> orders</p>
                                </div>
                                <span class="font-bold text-indigo-600"><?php echo formatPrice($restaurant['total_revenue']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No restaurant data for this period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>