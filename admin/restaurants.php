<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

// Toggle status
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE restaurants SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: restaurants.php');
    exit();
}

// Delete restaurant
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE restaurants SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: restaurants.php?deleted=1');
    exit();
}

// Get all restaurants
$stmt = $pdo->query("SELECT * FROM restaurants ORDER BY created_at DESC");
$restaurants = $stmt->fetchAll();

$success = isset($_GET['added']) ? 'Restaurant added successfully!' : 
           (isset($_GET['updated']) ? 'Restaurant updated successfully!' : 
           (isset($_GET['deleted']) ? 'Restaurant deleted successfully!' : ''));
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">🏪 Restaurant Management</h1>
                <p class="text-gray-500 mt-1">Manage all restaurants on your platform</p>
            </div>
            <a href="add-restaurant.php" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Restaurant
            </a>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery Fee</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($restaurants)): ?>
                            <?php foreach ($restaurants as $restaurant): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <img src="<?php echo RESTAURANT_IMAGE_URL . ($restaurant['image'] ?: DEFAULT_RESTAURANT_IMAGE); ?>" 
                                             alt="<?php echo htmlspecialchars($restaurant['name']); ?>" 
                                             class="w-12 h-12 rounded-lg object-cover">
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($restaurant['name']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($restaurant['city'] . ', ' . $restaurant['region']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <i class="fas fa-star text-yellow-400"></i> <?php echo number_format($restaurant['rating'], 1); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-indigo-600">
                                        <?php echo formatPrice($restaurant['delivery_fee']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $restaurant['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                            <?php echo ucfirst($restaurant['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center gap-2">
                                            <a href="edit-restaurant.php?id=<?php echo $restaurant['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-700" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?toggle_status=1&id=<?php echo $restaurant['id']; ?>" 
                                               class="<?php echo $restaurant['status'] === 'active' ? 'text-yellow-600 hover:text-yellow-700' : 'text-green-600 hover:text-green-700'; ?>"
                                               title="Toggle Status">
                                                <i class="fas <?php echo $restaurant['status'] === 'active' ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                            </a>
                                            <a href="?delete=1&id=<?php echo $restaurant['id']; ?>" 
                                               onclick="return confirm('Delete this restaurant?')"
                                               class="text-red-600 hover:text-red-700" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <div class="text-4xl mb-2">🏪</div>
                                    <p>No restaurants found.</p>
                                    <a href="add-restaurant.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Add your first restaurant</a>
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