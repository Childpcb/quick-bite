<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

$error = '';
$success = '';

// Get restaurants and categories for dropdowns
$restaurants = $pdo->query("SELECT id, name FROM restaurants WHERE status = 'active' ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $restaurant_id = (int)($_POST['restaurant_id'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($name) || empty($description) || $price <= 0 || $restaurant_id <= 0 || $category_id <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['image'], FOOD_UPLOAD_PATH, 'food');
            if ($uploaded) {
                $image = $uploaded;
            } else {
                $error = 'Failed to upload image. Please try again.';
            }
        }
        
        if (empty($error)) {
            $stmt = $pdo->prepare("
                INSERT INTO food_items (restaurant_id, category_id, name, description, ingredients, price, image, is_available, is_featured, is_popular, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$restaurant_id, $category_id, $name, $description, $ingredients, $price, $image, $is_available, $is_featured, $is_popular, $status])) {
                header('Location: foods.php?added=1');
                exit();
            } else {
                $error = 'Failed to add food. Please try again.';
            }
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
            <a href="foods.php" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Add New Food</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Form -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Food Name -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Food Name *</label>
                        <input type="text" name="name" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="e.g., Chicken Burger">
                    </div>
                    
                    <!-- Price -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Price (TZS) *</label>
                        <input type="number" name="price" required step="0.01" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="e.g., 10000">
                    </div>
                    
                    <!-- Restaurant -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Restaurant *</label>
                        <select name="restaurant_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Restaurant</option>
                            <?php foreach ($restaurants as $restaurant): ?>
                                <option value="<?php echo $restaurant['id']; ?>">
                                    <?php echo htmlspecialchars($restaurant['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Category -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Category *</label>
                        <select name="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Description *</label>
                        <textarea name="description" rows="3" required 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="Describe the food item..."></textarea>
                    </div>
                    
                    <!-- Ingredients -->
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Ingredients</label>
                        <textarea name="ingredients" rows="2" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="List ingredients separated by commas"></textarea>
                    </div>
                    
                    <!-- Image -->
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Food Image</label>
                        <input type="file" name="image" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-sm text-gray-500 mt-1">JPG, PNG, WEBP (Max 5MB)</p>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <!-- Options -->
                    <div class="space-y-2 pt-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_available" checked class="mr-2">
                            Available for ordering
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" class="mr-2">
                            Featured on homepage
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_popular" class="mr-2">
                            Mark as popular
                        </label>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="submit" class="btn-primary text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i> Add Food
                    </button>
                    <a href="foods.php" class="px-8 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>