<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$restaurantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($restaurantId <= 0) {
    header('Location: ' . SITE_URL . 'restaurants.php');
    exit();
}

$restaurant = getRestaurantById($pdo, $restaurantId);
if (!$restaurant || $restaurant['status'] !== 'active') {
    header('Location: ' . SITE_URL . 'restaurants.php');
    exit();
}

$foodItems = getRestaurantFood($pdo, $restaurantId);
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<div class="pt-24 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Restaurant Info -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="relative h-64 md:h-80">
                <img src="<?php echo RESTAURANT_IMAGE_URL . ($restaurant['image'] ?: DEFAULT_RESTAURANT_IMAGE); ?>" 
                     alt="<?php echo htmlspecialchars($restaurant['name']); ?>" 
                     class="w-full h-full object-cover">
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-6">
                    <div class="flex items-end justify-between">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold text-white"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
                            <div class="flex items-center text-white/90 mt-2">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                <span><?php echo number_format($restaurant['rating'], 1); ?></span>
                                <span class="mx-2">•</span>
                                <span><i class="fas fa-clock mr-1"></i> <?php echo $restaurant['opening_hours']; ?></span>
                            </div>
                        </div>
                        <a href="<?php echo SITE_URL; ?>menu.php?restaurant=<?php echo $restaurant['id']; ?>" 
                           class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                            View Full Menu
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <h2 class="text-xl font-bold text-gray-800 mb-2">About</h2>
                        <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($restaurant['description'])); ?></p>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-2">Contact & Info</h2>
                        <div class="space-y-2 text-gray-600">
                            <p><i class="fas fa-user mr-2 text-indigo-600"></i> <strong>Manager:</strong> Alison Naftali</p>
                            <p><i class="fas fa-phone mr-2 text-indigo-600"></i> <strong>Phone:</strong> +255 754 341 171 / 619 811 171</p>
                            <p><i class="fas fa-envelope mr-2 text-indigo-600"></i> <strong>Email:</strong> alisonnaftali2003@gmail.com</p>
                            <p><i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i> <strong>Address:</strong> <?php echo htmlspecialchars($restaurant['address']); ?></p>
                            <p><i class="fas fa-city mr-2 text-indigo-600"></i> <strong>City:</strong> <?php echo htmlspecialchars($restaurant['city'] . ', ' . $restaurant['region']); ?></p>
                            <p><i class="fas fa-truck mr-2 text-indigo-600"></i> <strong>Delivery:</strong> <?php echo formatPrice($restaurant['delivery_fee']); ?> • <?php echo $restaurant['delivery_time']; ?></p>
                            <p><i class="fas fa-clock mr-2 text-indigo-600"></i> <strong>Hours:</strong> <?php echo $restaurant['opening_hours']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Menu Items -->
        <div>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Our Menu</h2>
                <span class="text-gray-500"><?php echo count($foodItems); ?> items</span>
            </div>
            
            <?php if (!empty($foodItems)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($foodItems as $food): ?>
                <div class="food-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                    <div class="relative">
                        <div class="food-image w-full flex items-center justify-center text-6xl" style="background: linear-gradient(135deg, <?php 
                            $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#FF8A5C', '#A29BFE'];
                            echo $colors[$food['id'] % count($colors)];
                        ?> 0%, #2D3436 100%);">
                            <?php 
                            $icons = [
                                1 => '🍕', 2 => '🍔', 3 => '🍗', 4 => '🍟', 5 => '🍛', 6 => '🍛', 7 => '🥤', 8 => '🍰', 9 => '🍚', 10 => '🥗'
                            ];
                            echo $icons[$food['category_id']] ?? '🍽️';
                            ?>
                        </div>
                        <?php if ($food['is_popular']): ?>
                            <span class="absolute top-2 left-2 bg-red-500 text-white text-xs px-3 py-1 rounded-full">🔥 Popular</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($food['name']); ?></h3>
                                <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full">
                                    <?php echo htmlspecialchars($food['category_name']); ?>
                                </span>
                            </div>
                            <span class="text-indigo-600 font-bold"><?php echo formatPrice($food['price']); ?></span>
                        </div>
                        <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?php echo htmlspecialchars(substr($food['description'], 0, 60)) . '...'; ?></p>
                        <div class="flex gap-2 mt-4">
                            <a href="<?php echo SITE_URL; ?>food-details.php?id=<?php echo $food['id']; ?>" 
                               class="flex-1 text-center bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                                Order Now
                            </a>
                            <button onclick="addToCart(<?php echo $food['id']; ?>)" 
                                    class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12 bg-white rounded-xl shadow-md">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-xl font-bold text-gray-800">No Menu Items Available</h3>
                <p class="text-gray-500 mt-2">This restaurant hasn't added any items yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function addToCart(foodId) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = '<?php echo SITE_URL; ?>login.php?redirect=restaurant-details.php?id=<?php echo $restaurantId; ?>';
        return;
    <?php endif; ?>
    
    fetch('<?php echo SITE_URL; ?>cart-action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&food_id=' + foodId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to add to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>