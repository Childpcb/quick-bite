<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: promotions.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = ?");
$stmt->execute([$id]);
$promotion = $stmt->fetch();

if (!$promotion) {
    header('Location: promotions.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $promo_code = trim($_POST['promo_code'] ?? '');
    $discount_type = $_POST['discount_type'] ?? 'percentage';
    $discount_value = (float)($_POST['discount_value'] ?? 0);
    $start_date = $_POST['start_date'] ?? date('Y-m-d H:i:s');
    $end_date = $_POST['end_date'] ?? date('Y-m-d H:i:s', strtotime('+30 days'));
    $status = $_POST['status'] ?? 'active';
    
    if (empty($title) || empty($promo_code) || $discount_value <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if promo code already exists for another promotion
        $stmt = $pdo->prepare("SELECT id FROM promotions WHERE promo_code = ? AND id != ?");
        $stmt->execute([$promo_code, $id]);
        if ($stmt->fetch()) {
            $error = 'Promo code already exists. Please use a different code.';
        } else {
            $banner_image = $promotion['banner_image'];
            
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                $uploaded = uploadImage($_FILES['banner_image'], PROMOTION_UPLOAD_PATH, 'promo');
                if ($uploaded) {
                    if ($banner_image && file_exists(PROMOTION_UPLOAD_PATH . $banner_image)) {
                        unlink(PROMOTION_UPLOAD_PATH . $banner_image);
                    }
                    $banner_image = $uploaded;
                }
            }
            
            if (empty($error)) {
                $stmt = $pdo->prepare("
                    UPDATE promotions 
                    SET title = ?, description = ?, promo_code = ?, discount_type = ?, 
                        discount_value = ?, start_date = ?, end_date = ?, banner_image = ?, status = ?
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$title, $description, $promo_code, $discount_type, $discount_value, $start_date, $end_date, $banner_image, $status, $id])) {
                    header('Location: promotions.php?updated=1');
                    exit();
                } else {
                    $error = 'Failed to update promotion.';
                }
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
            <a href="promotions.php" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Edit Promotion</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Promotion Title *</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($promotion['title']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($promotion['description']); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Promo Code *</label>
                        <input type="text" name="promo_code" required value="<?php echo htmlspecialchars($promotion['promo_code']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Discount Type</label>
                        <select name="discount_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="percentage" <?php echo $promotion['discount_type'] == 'percentage' ? 'selected' : ''; ?>>Percentage (%)</option>
                            <option value="fixed" <?php echo $promotion['discount_type'] == 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                            <option value="free_delivery" <?php echo $promotion['discount_type'] == 'free_delivery' ? 'selected' : ''; ?>>Free Delivery</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Discount Value *</label>
                        <input type="number" name="discount_value" required step="0.01" min="0" value="<?php echo $promotion['discount_value']; ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active" <?php echo $promotion['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $promotion['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Start Date</label>
                        <input type="datetime-local" name="start_date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($promotion['start_date'])); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">End Date</label>
                        <input type="datetime-local" name="end_date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($promotion['end_date'])); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Current Banner Image</label>
                        <?php if ($promotion['banner_image'] && file_exists(PROMOTION_UPLOAD_PATH . $promotion['banner_image'])): ?>
                            <img src="<?php echo PROMOTION_IMAGE_URL . $promotion['banner_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($promotion['title']); ?>" 
                                 class="w-32 h-32 rounded-lg object-cover mb-2">
                        <?php else: ?>
                            <p class="text-gray-500 text-sm mb-2">No image uploaded</p>
                        <?php endif; ?>
                        <input type="file" name="banner_image" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="submit" class="btn-primary text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Promotion
                    </button>
                    <a href="promotions.php" class="px-8 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>