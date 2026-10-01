<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/admin-auth.php';
require_once __DIR__ . '/functions.php';

$admin = getCurrentAdmin($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);

// Get notification count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = 1 AND is_read = 0");
$unreadCount = $stmt->fetch()['count'] ?? 0;

// Get customer count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND status = 'active'");
$customerCount = $stmt->fetch()['count'] ?? 0;

// Get pending orders count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
$pendingCount = $stmt->fetch()['count'] ?? 0;

// Get admin count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role != 'customer' AND status = 'active'");
$adminCount = $stmt->fetch()['count'] ?? 0;

// Get unread messages count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
$unreadMessages = $stmt->fetch()['count'] ?? 0;
?>
<nav class="bg-white shadow-lg fixed top-0 left-0 right-0 z-50 md:ml-64">
    <div class="px-4 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <button id="sidebar-toggle" class="md:hidden text-gray-600 hover:text-gray-900">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h1 class="text-xl font-bold text-gray-800">Admin Dashboard</h1>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600 hidden sm:inline"><?php echo htmlspecialchars($admin['name'] ?? 'Admin'); ?></span>
            <a href="<?php echo SITE_URL; ?>logout.php" class="text-red-600 hover:text-red-800 transition">
                <i class="fas fa-sign-out-alt"></i>
                <span class="hidden sm:inline">Logout</span>
            </a>
        </div>
    </div>
</nav>

<!-- ============================================================
     SIDEBAR - WITH SCROLLING
     ============================================================ -->
<div class="admin-sidebar fixed top-0 left-0 w-64 h-full z-40 hidden md:block">
    <div class="h-full overflow-y-auto">
        <div class="p-4">
            <div class="flex items-center space-x-2 mb-8">
                <i class="fas fa-utensils text-2xl text-indigo-400"></i>
                <span class="text-xl font-bold text-white">Quick Bite</span>
            </div>
            
            <nav class="space-y-1 pb-20">
                <!-- Dashboard -->
                <a href="index.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'index.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Orders -->
                <a href="orders.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'orders.php' || $currentPage == 'order-details.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-shopping-bag w-5"></i>
                    <span>Orders</span>
                    <?php if ($pendingCount > 0): ?>
                        <span class="float-right bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- Food Management -->
                <a href="foods.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'foods.php' || $currentPage == 'add-food.php' || $currentPage == 'edit-food.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-utensils w-5"></i>
                    <span>Food Management</span>
                </a>
                
                <!-- Restaurant Management -->
                <a href="restaurants.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'restaurants.php' || $currentPage == 'add-restaurant.php' || $currentPage == 'edit-restaurant.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-store w-5"></i>
                    <span>Restaurants</span>
                </a>
                
                <!-- Category Management -->
                <a href="categories.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'categories.php' || $currentPage == 'add-category.php' || $currentPage == 'edit-category.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-tags w-5"></i>
                    <span>Categories</span>
                </a>
                
                <!-- ===== USER MANAGEMENT ===== -->
                <div class="mt-2">
                    <p class="text-xs text-gray-500 uppercase px-4 py-1">User Management</p>
                </div>
                
                <!-- All Customers -->
                <a href="customers.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'customers.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-users w-5"></i>
                    <span>All Customers</span>
                    <?php if ($customerCount > 0): ?>
                        <span class="float-right bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $customerCount; ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- Add Customer -->
                <a href="add-customer.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'add-customer.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-user-plus w-5"></i>
                    <span>Add Customer</span>
                </a>
                
                <!-- ===== ADMIN MANAGEMENT ===== -->
                <a href="admins.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'admins.php' || $currentPage == 'add-admin.php' || $currentPage == 'reset-password.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-user-shield w-5"></i>
                    <span>Admins</span>
                    <?php if ($adminCount > 0): ?>
                        <span class="float-right bg-purple-600 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $adminCount; ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- ===== MANAGEMENT ===== -->
                <div class="mt-2">
                    <p class="text-xs text-gray-500 uppercase px-4 py-1">Management</p>
                </div>
                
                <!-- Promotions -->
                <a href="promotions.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'promotions.php' || $currentPage == 'add-promotion.php' || $currentPage == 'edit-promotion.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-gift w-5"></i>
                    <span>Promotions</span>
                </a>
                
                <!-- Reports -->
                <a href="reports.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'reports.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Reports</span>
                </a>
                
                <!-- Contact Messages -->
                <a href="contact-messages.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'contact-messages.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-envelope w-5"></i>
                    <span>Contact Messages</span>
                    <?php if ($unreadMessages > 0): ?>
                        <span class="float-right bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $unreadMessages; ?></span>
                    <?php endif; ?>
                </a>
                
                <hr class="border-gray-700 my-2">
                
                <!-- View Website -->
                <a href="<?php echo SITE_URL; ?>" target="_blank" class="block px-4 py-2.5 rounded-lg transition text-gray-300 hover:bg-white/10 hover:text-white">
                    <i class="fas fa-globe w-5"></i>
                    <span>View Website</span>
                </a>
                
                <!-- Logout -->
                <a href="<?php echo SITE_URL; ?>logout.php" class="block px-4 py-2.5 rounded-lg transition text-red-400 hover:bg-red-500/10 hover:text-red-300">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
    </div>
</div>

<!-- ============================================================
     MOBILE SIDEBAR - WITH SCROLLING
     ============================================================ -->
<div id="mobile-sidebar" class="admin-sidebar fixed top-0 left-0 w-64 h-full z-50 hidden md:hidden">
    <div class="h-full overflow-y-auto">
        <div class="p-4">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-utensils text-2xl text-indigo-400"></i>
                    <span class="text-xl font-bold text-white">Quick Bite</span>
                </div>
                <button id="close-sidebar" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <nav class="space-y-1 pb-20">
                <a href="index.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'index.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-tachometer-alt w-5"></i> Dashboard
                </a>
                <a href="orders.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'orders.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-shopping-bag w-5"></i> Orders
                </a>
                <a href="foods.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'foods.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-utensils w-5"></i> Food
                </a>
                <a href="restaurants.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'restaurants.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-store w-5"></i> Restaurants
                </a>
                <a href="categories.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'categories.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-tags w-5"></i> Categories
                </a>
                
                <hr class="border-gray-700 my-2">
                
                <a href="customers.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'customers.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-users w-5"></i> Customers
                </a>
                <a href="add-customer.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'add-customer.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-user-plus w-5"></i> Add Customer
                </a>
                
                <a href="admins.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'admins.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-user-shield w-5"></i> Admins
                </a>
                
                <hr class="border-gray-700 my-2">
                
                <a href="promotions.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'promotions.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-gift w-5"></i> Promotions
                </a>
                <a href="reports.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'reports.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-chart-bar w-5"></i> Reports
                </a>
                <a href="contact-messages.php" class="block px-4 py-2.5 rounded-lg transition <?php echo $currentPage == 'contact-messages.php' ? 'active bg-indigo-600/20 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'; ?>">
                    <i class="fas fa-envelope w-5"></i> Messages
                </a>
                
                <hr class="border-gray-700 my-2">
                
                <a href="<?php echo SITE_URL; ?>" target="_blank" class="block px-4 py-2.5 rounded-lg transition text-gray-300 hover:bg-white/10 hover:text-white">
                    <i class="fas fa-globe w-5"></i> Website
                </a>
                <a href="<?php echo SITE_URL; ?>logout.php" class="block px-4 py-2.5 rounded-lg transition text-red-400 hover:bg-red-500/10 hover:text-red-300">
                    <i class="fas fa-sign-out-alt w-5"></i> Logout
                </a>
            </nav>
        </div>
    </div>
</div>

<script>
// Toggle sidebar on mobile
document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
    const sidebar = document.getElementById('mobile-sidebar');
    if (sidebar) {
        sidebar.classList.toggle('hidden');
    }
});

// Close sidebar
document.getElementById('close-sidebar')?.addEventListener('click', function() {
    document.getElementById('mobile-sidebar')?.classList.add('hidden');
});

// Click outside to close
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('mobile-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    if (sidebar && !sidebar.classList.contains('hidden')) {
        if (!sidebar.contains(e.target) && !toggleBtn?.contains(e.target)) {
            sidebar.classList.add('hidden');
        }
    }
});
</script>