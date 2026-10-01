<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

// Toggle status
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE food_items SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: foods.php');
    exit();
}

// Delete (soft delete)
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE food_items SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: foods.php?deleted=1');
    exit();
}

// Get all food items
$stmt = $pdo->query("
    SELECT f.*, r.name as restaurant_name, c.name as category_name
    FROM food_items f
    JOIN restaurants r ON f.restaurant_id = r.id
    JOIN categories c ON f.category_id = c.id
    ORDER BY f.created_at DESC
");
$foodItems = $stmt->fetchAll();

$success = isset($_GET['added']) ? 'Food added successfully!' : 
           (isset($_GET['updated']) ? 'Food updated successfully!' : 
           (isset($_GET['deleted']) ? 'Food deleted successfully!' : ''));
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Food Management</h1>
                <p class="text-gray-500 mt-1">Manage all food items on your platform</p>
            </div>
            <a href="add-food.php" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Food
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Restaurant</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($foodItems)): ?>
                            <?php foreach ($foodItems as $food): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <img src="<?php echo FOOD_IMAGE_URL . ($food['image'] ?: DEFAULT_FOOD_IMAGE); ?>" 
                                             alt="<?php echo htmlspecialchars($food['name']); ?>" 
                                             class="w-12 h-12 rounded-lg object-cover">
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($food['name']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($food['restaurant_name']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($food['category_name']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-indigo-600">
                                        <?php echo formatPrice($food['price']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $food['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                            <?php echo ucfirst($food['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center gap-2">
                                            <!-- ===== EDIT BUTTON (SASA INAFANYA KAZI) ===== -->
                                            <a href="edit-food.php?id=<?php echo $food['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-700" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?toggle_status=1&id=<?php echo $food['id']; ?>" 
                                               class="<?php echo $food['status'] === 'active' ? 'text-yellow-600 hover:text-yellow-700' : 'text-green-600 hover:text-green-700'; ?>"
                                               title="Toggle Status">
                                                <i class="fas <?php echo $food['status'] === 'active' ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                            </a>
                                            <a href="?delete=1&id=<?php echo $food['id']; ?>" 
                                               onclick="return confirm('Delete this food item?')"
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
                                    <div class="text-4xl mb-2">🍽️</div>
                                    <p>No food items found.</p>
                                    <a href="add-food.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Add your first food item</a>
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