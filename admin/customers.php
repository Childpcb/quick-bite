<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

// Toggle status
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE id = ? AND role = 'customer'");
    $stmt->execute([$id]);
    header('Location: customers.php');
    exit();
}

// Delete customer
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $stmt->execute([$id]);
    header('Location: customers.php?deleted=1');
    exit();
}

// Get all customers
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC");
$customers = $stmt->fetchAll();

$success = isset($_GET['added']) ? 'Customer added successfully!' : 
           (isset($_GET['deleted']) ? 'Customer deleted successfully!' : '');

$total = count($customers);
$active = 0;
$inactive = 0;
foreach ($customers as $c) {
    if ($c['status'] == 'active') $active++;
    else $inactive++;
}
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">👥 Customer Management</h1>
                <p class="text-gray-500 mt-1">Manage all registered customers</p>
            </div>
            <div class="flex gap-3">
                <a href="add-customer.php" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                    <i class="fas fa-user-plus mr-2"></i> Add Customer
                </a>
                <span class="bg-blue-100 text-blue-700 px-3 py-2 rounded-lg text-sm">
                    Total: <?php echo $total; ?>
                </span>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-2xl font-bold text-blue-600"><?php echo $total; ?></p>
                <p class="text-sm text-gray-500">Total Customers</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-2xl font-bold text-green-600"><?php echo $active; ?></p>
                <p class="text-sm text-gray-500">Active</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-2xl font-bold text-red-600"><?php echo $inactive; ?></p>
                <p class="text-sm text-gray-500">Inactive</p>
            </div>
        </div>
        
        <!-- Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($customers)): ?>
                            <?php $counter = 1; foreach ($customers as $customer): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo $counter++; ?></td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $customer['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                            <?php echo ucfirst($customer['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center gap-2">
                                            <a href="?toggle_status=1&id=<?php echo $customer['id']; ?>" 
                                               class="<?php echo $customer['status'] === 'active' ? 'text-yellow-600 hover:text-yellow-700' : 'text-green-600 hover:text-green-700'; ?>"
                                               title="Toggle Status">
                                                <i class="fas <?php echo $customer['status'] === 'active' ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                            </a>
                                            <a href="?delete=1&id=<?php echo $customer['id']; ?>" 
                                               onclick="return confirm('Delete this customer?')"
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
                                    <div class="text-4xl mb-2">👤</div>
                                    <p>No customers registered yet.</p>
                                    <a href="add-customer.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Add your first customer</a>
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