<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: restaurants.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    header('Location: restaurants.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $delivery_fee = (float)($_POST['delivery_fee'] ?? 0);
    $delivery_time = trim($_POST['delivery_time'] ?? '');
    $opening_hours = trim($_POST['opening_hours'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name) || empty($description) || empty($address) || empty($city) || empty($region)) {
        $error = 'Please fill in all required fields.';
    } else {
        $image = $restaurant['image'];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['image'], RESTAURANT_UPLOAD_PATH, 'restaurant');
            if ($uploaded) {
                if ($image && file_exists(RESTAURANT_UPLOAD_PATH . $image)) {
                    unlink(RESTAURANT_UPLOAD_PATH . $image);
                }
                $image = $uploaded;
            }
        }
        
        if (empty($error)) {
            $stmt = $pdo->prepare("
                UPDATE restaurants 
                SET name = ?, description = ?, phone = ?, email = ?, address = ?, 
                    city = ?, region = ?, image = ?, delivery_fee = ?, 
                    delivery_time = ?, opening_hours = ?, status = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$name, $description, $phone, $email, $address, $city, $region, $image, $delivery_fee, $delivery_time, $opening_hours, $status, $id])) {
                header('Location: restaurants.php?updated=1');
                exit();
            } else {
                $error = 'Failed to update restaurant.';
            }
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="restaurants.php" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Edit Restaurant</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Restaurant Name *</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($restaurant['name']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($restaurant['phone']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($restaurant['email']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Region *</label>
                        <select name="region" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="Dar es Salaam" <?php echo $restaurant['region'] == 'Dar es Salaam' ? 'selected' : ''; ?>>Dar es Salaam</option>
                            <option value="Arusha" <?php echo $restaurant['region'] == 'Arusha' ? 'selected' : ''; ?>>Arusha</option>
                            <option value="Mwanza" <?php echo $restaurant['region'] == 'Mwanza' ? 'selected' : ''; ?>>Mwanza</option>
                            <option value="Dodoma" <?php echo $restaurant['region'] == 'Dodoma' ? 'selected' : ''; ?>>Dodoma</option>
                            <option value="Mbeya" <?php echo $restaurant['region'] == 'Mbeya' ? 'selected' : ''; ?>>Mbeya</option>
                            <option value="Morogoro" <?php echo $restaurant['region'] == 'Morogoro' ? 'selected' : ''; ?>>Morogoro</option>
                            <option value="Tanga" <?php echo $restaurant['region'] == 'Tanga' ? 'selected' : ''; ?>>Tanga</option>
                            <option value="Shinyanga" <?php echo $restaurant['region'] == 'Shinyanga' ? 'selected' : ''; ?>>Shinyanga</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Address *</label>
                        <input type="text" name="address" required value="<?php echo htmlspecialchars($restaurant['address']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">City *</label>
                        <input type="text" name="city" required value="<?php echo htmlspecialchars($restaurant['city']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Delivery Fee (TZS)</label>
                        <input type="number" name="delivery_fee" step="0.01" value="<?php echo $restaurant['delivery_fee']; ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Delivery Time</label>
                        <input type="text" name="delivery_time" value="<?php echo htmlspecialchars($restaurant['delivery_time']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Opening Hours</label>
                        <input type="text" name="opening_hours" value="<?php echo htmlspecialchars($restaurant['opening_hours']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Description *</label>
                        <textarea name="description" rows="3" required 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($restaurant['description']); ?></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Current Image</label>
                        <?php if ($restaurant['image'] && file_exists(RESTAURANT_UPLOAD_PATH . $restaurant['image'])): ?>
                            <img src="<?php echo RESTAURANT_IMAGE_URL . $restaurant['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($restaurant['name']); ?>" 
                                 class="w-32 h-32 rounded-lg object-cover mb-2">
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active" <?php echo $restaurant['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $restaurant['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="submit" class="btn-primary text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Restaurant
                    </button>
                    <a href="restaurants.php" class="px-8 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>