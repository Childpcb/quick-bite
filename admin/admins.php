<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

// Check if current user is super admin (id = 1)
$currentAdmin = getCurrentAdmin($pdo);
$isSuperAdmin = ($currentAdmin['id'] == 1);

// Toggle status
if (isset($_GET['toggle_status']) && isset($_GET['id']) && $isSuperAdmin) {
    $id = (int)$_GET['id'];
    if ($id != 1) {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE id = ? AND role != 'customer'");
        $stmt->execute([$id]);
    }
    header('Location: admins.php');
    exit();
}

// Delete admin
if (isset($_GET['delete']) && isset($_GET['id']) && $isSuperAdmin) {
    $id = (int)$_GET['id'];
    if ($id != 1) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'customer'");
        $stmt->execute([$id]);
    }
    header('Location: admins.php?deleted=1');
    exit();
}

// Get all admins
$stmt = $pdo->query("SELECT * FROM users WHERE role != 'customer' ORDER BY id ASC");
$admins = $stmt->fetchAll();

$success = isset($_GET['added']) ? 'Admin added successfully!' : 
           (isset($_GET['deleted']) ? 'Admin deleted successfully!' : '');
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">👥 Admin Management</h1>
                <p class="text-gray-500 mt-1">Manage all administrators</p>
            </div>
            <?php if ($isSuperAdmin): ?>
                <a href="add-admin.php" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                    <i class="fas fa-user-plus mr-2"></i> Add Admin
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$isSuperAdmin): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                You are viewing admins. Only Super Admin can add or delete admins.
            </div>
        <?php endif; ?>
        
        <!-- Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($admins)): ?>
                            <?php $counter = 1; foreach ($admins as $admin): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo $counter++; ?></td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($admin['name']); ?>
                                        <?php if ($admin['id'] == 1): ?>
                                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full ml-2">Super</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $admin['role'] == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                            <?php echo ucfirst($admin['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $admin['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                            <?php echo ucfirst($admin['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php if ($isSuperAdmin): ?>
                                            <?php if ($admin['id'] == 1): ?>
                                                <span class="text-xs text-gray-400">Super Admin</span>
                                            <?php else: ?>
                                                <div class="flex items-center gap-2">
                                                    <a href="reset-password.php?id=<?php echo $admin['id']; ?>" 
                                                       class="text-blue-600 hover:text-blue-700" title="Reset Password">
                                                        <i class="fas fa-key"></i>
                                                    </a>
                                                    <a href="?toggle_status=1&id=<?php echo $admin['id']; ?>" 
                                                       class="<?php echo $admin['status'] === 'active' ? 'text-yellow-600 hover:text-yellow-700' : 'text-green-600 hover:text-green-700'; ?>"
                                                       title="Toggle Status">
                                                        <i class="fas <?php echo $admin['status'] === 'active' ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                                    </a>
                                                    <a href="?delete=1&id=<?php echo $admin['id']; ?>" 
                                                       onclick="return confirm('Delete this admin?')"
                                                       class="text-red-600 hover:text-red-700" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">View only</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    No admins found.
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