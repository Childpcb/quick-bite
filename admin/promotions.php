<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

// Toggle promotion status
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE promotions SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: promotions.php');
    exit();
}

// Delete promotion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: promotions.php?deleted=1');
    exit();
}

// Get all promotions
$stmt = $pdo->query("SELECT * FROM promotions ORDER BY created_at DESC");
$promotions = $stmt->fetchAll();

$success = isset($_GET['added']) ? 'Promotion added successfully!' : 
           (isset($_GET['updated']) ? 'Promotion updated successfully!' : 
           (isset($_GET['deleted']) ? 'Promotion deleted successfully!' : ''));
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Promotion Management</h1>
                <p class="text-gray-500 mt-1">Manage promotional offers and discounts</p>
            </div>
            <a href="add-promotion.php" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Promotion
            </a>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Until</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($promotions)): ?>
                            <?php $counter = 1; foreach ($promotions as $promotion): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo $counter++; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($promotion['title']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs">
                                            <?php echo htmlspecialchars($promotion['promo_code']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php 
                                        if ($promotion['discount_type'] === 'percentage') {
                                            echo $promotion['discount_value'] . '% OFF';
                                        } elseif ($promotion['discount_type'] === 'fixed') {
                                            echo formatPrice($promotion['discount_value']) . ' OFF';
                                        } elseif ($promotion['discount_type'] === 'free_delivery') {
                                            echo 'Free Delivery';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($promotion['end_date'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $promotion['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                            <?php echo ucfirst($promotion['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center gap-2">
                                            <!-- ===== EDIT BUTTON ===== -->
                                            <a href="edit-promotion.php?id=<?php echo $promotion['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-700" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <!-- ===== TOGGLE STATUS ===== -->
                                            <a href="?toggle_status=1&id=<?php echo $promotion['id']; ?>" 
                                               class="<?php echo $promotion['status'] === 'active' ? 'text-yellow-600 hover:text-yellow-700' : 'text-green-600 hover:text-green-700'; ?>"
                                               title="Toggle Status">
                                                <i class="fas <?php echo $promotion['status'] === 'active' ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                            </a>
                                            <!-- ===== DELETE ===== -->
                                            <a href="?delete=1&id=<?php echo $promotion['id']; ?>" 
                                               onclick="return confirm('Delete this promotion?')"
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
                                    <div class="text-4xl mb-2">🎉</div>
                                    <p>No promotions found.</p>
                                    <a href="add-promotion.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Create your first promotion</a>
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