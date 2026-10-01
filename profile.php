<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser($pdo);

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (empty($name) || empty($email)) {
                $error = 'Name and email are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$name, $phone, $email, $userId])) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $success = 'Profile updated successfully!';
                    $user = getCurrentUser($pdo);
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        } elseif ($_POST['action'] === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = 'Please fill in all password fields.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch();
                
                if (password_verify($currentPassword, $userData['password'])) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($stmt->execute([$hashedPassword, $userId])) {
                        $success = 'Password changed successfully!';
                    } else {
                        $error = 'Failed to change password.';
                    }
                } else {
                    $error = 'Current password is incorrect.';
                }
            }
        } elseif ($_POST['action'] === 'add_address') {
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $region = trim($_POST['region'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $additional = trim($_POST['additional_info'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            
            if (empty($address) || empty($city) || empty($region)) {
                $error = 'Please fill in required fields.';
            } else {
                if ($isDefault) {
                    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
                    $stmt->execute([$userId]);
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO addresses (user_id, address, city, region, phone, additional_info, is_default)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([$userId, $address, $city, $region, $phone, $additional, $isDefault])) {
                    $success = 'Address added successfully!';
                } else {
                    $error = 'Failed to add address.';
                }
            }
        } elseif ($_POST['action'] === 'delete_address') {
            $addressId = (int)$_POST['address_id'];
            $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$addressId, $userId])) {
                $success = 'Address deleted successfully!';
            } else {
                $error = 'Failed to delete address.';
            }
        }
    }
}

// Get user addresses
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<div class="pt-24 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-8">My Profile</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Profile Info -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Profile Information</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Phone</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <button type="submit" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Change Password</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Current Password</label>
                        <input type="password" name="current_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">New Password</label>
                        <input type="password" name="new_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <button type="submit" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Addresses -->
        <div class="mt-6 bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800">Saved Addresses</h2>
                <button onclick="document.getElementById('addAddressForm').classList.toggle('hidden')" 
                        class="text-indigo-600 hover:text-indigo-700 font-medium">
                    <i class="fas fa-plus mr-1"></i> Add Address
                </button>
            </div>
            
            <!-- Add Address Form -->
            <div id="addAddressForm" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-3">Add New Address</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_address">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                            <input type="text" name="address" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                            <input type="text" name="city" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Region *</label>
                            <input type="text" name="region" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="phone" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Info</label>
                            <input type="text" name="additional_info" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_default" value="1" class="mr-2">
                                Set as default address
                            </label>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                                <i class="fas fa-save mr-2"></i> Save Address
                            </button>
                            <button type="button" onclick="document.getElementById('addAddressForm').classList.add('hidden')" 
                                    class="ml-2 text-gray-500 hover:text-gray-700">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Address List -->
            <?php if (!empty($addresses)): ?>
                <div class="space-y-3">
                    <?php foreach ($addresses as $address): ?>
                        <div class="flex justify-between items-start p-3 border rounded-lg hover:bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-800">
                                    <?php echo htmlspecialchars($address['address']); ?>
                                    <?php if ($address['is_default']): ?>
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full ml-2">Default</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($address['city'] . ', ' . $address['region']); ?>
                                    <?php if ($address['phone']): ?>
                                        • <?php echo htmlspecialchars($address['phone']); ?>
                                    <?php endif; ?>
                                </p>
                                <?php if ($address['additional_info']): ?>
                                    <p class="text-sm text-gray-400"><?php echo htmlspecialchars($address['additional_info']); ?></p>
                                <?php endif; ?>
                            </div>
                            <form method="POST" action="" onsubmit="return confirm('Delete this address?')">
                                <input type="hidden" name="action" value="delete_address">
                                <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">No saved addresses. Add one above.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>