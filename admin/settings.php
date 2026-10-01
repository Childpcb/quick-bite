<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

$error = '';
$success = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name'] ?? '');
    $site_email = trim($_POST['site_email'] ?? '');
    $site_phone_1 = trim($_POST['site_phone_1'] ?? '');
    $site_phone_2 = trim($_POST['site_phone_2'] ?? '');
    $site_location = trim($_POST['site_location'] ?? '');
    
    if (empty($site_name)) {
        $error = 'Site name is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = 'site_name'");
        $stmt->execute([$site_name]);
        
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = 'site_email'");
        $stmt->execute([$site_email]);
        
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = 'site_phone_1'");
        $stmt->execute([$site_phone_1]);
        
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = 'site_phone_2'");
        $stmt->execute([$site_phone_2]);
        
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = 'site_location'");
        $stmt->execute([$site_location]);
        
        $success = 'Settings updated successfully!';
    }
}

// Get current settings
$stmt = $pdo->query("SELECT * FROM settings");
$settingsData = $stmt->fetchAll();
$settings = [];
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['value'];
}
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Site Settings</h1>
            <p class="text-gray-500 mt-1">Configure your website settings</p>
        </div>
        
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
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <form method="POST" action="">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Site Name</label>
                        <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Quick Bite'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Support Email</label>
                        <input type="email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? 'alisonnaftali2003@gmail.com'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Support Phone 1</label>
                        <input type="text" name="site_phone_1" value="<?php echo htmlspecialchars($settings['site_phone_1'] ?? '+255754341171'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Support Phone 2</label>
                        <input type="text" name="site_phone_2" value="<?php echo htmlspecialchars($settings['site_phone_2'] ?? '619811171'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Location</label>
                        <input type="text" name="site_location" value="<?php echo htmlspecialchars($settings['site_location'] ?? 'Shinyanga, Tanzania'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="submit" class="btn-primary text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>