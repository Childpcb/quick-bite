<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Get cart items
$cartItems = getCartItems($pdo, $userId);
$total = getCartTotal($pdo, $userId);
$deliveryFee = 3000;
$grandTotal = $total + $deliveryFee;
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<div class="pt-24 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-3 mb-8">
            <i class="fas fa-shopping-cart text-2xl text-indigo-600"></i>
            <h1 class="text-2xl font-bold text-gray-800">Your Cart</h1>
            <span class="text-sm text-gray-500">(<?php echo count($cartItems); ?> items)</span>
        </div>
        
        <?php if (!empty($cartItems)): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="p-4 flex items-center gap-4" id="cart-item-<?php echo $item['id']; ?>">
                                    <div class="w-20 h-20 rounded-lg flex items-center justify-center text-4xl" style="background: linear-gradient(135deg, <?php 
                                        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#FF8A5C', '#A29BFE'];
                                        echo $colors[$item['id'] % count($colors)];
                                    ?> 0%, #2D3436 100%);">
                                        <?php 
                                        $icons = [1 => '🍕', 2 => '🍔', 3 => '🍗', 4 => '🍟', 5 => '🍛', 6 => '🍛', 7 => '🥤', 8 => '🍰', 9 => '🍚', 10 => '🥗'];
                                        echo $icons[$item['food_id'] % 10] ?? '🍽️';
                                        ?>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['restaurant_name']); ?></p>
                                        <p class="text-indigo-600 font-semibold"><?php echo formatPrice($item['price']); ?></p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="updateCart(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)" 
                                                class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 transition">-</button>
                                        <span class="w-8 text-center font-medium" id="qty-<?php echo $item['id']; ?>">
                                            <?php echo $item['quantity']; ?>
                                        </span>
                                        <button onclick="updateCart(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)" 
                                                class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 transition">+</button>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-800"><?php echo formatPrice($item['price'] * $item['quantity']); ?></p>
                                        <button onclick="removeFromCart(<?php echo $item['id']; ?>)" 
                                                class="text-red-500 hover:text-red-700 text-sm transition">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="p-4 bg-gray-50">
                            <button onclick="clearCart()" class="text-red-500 hover:text-red-700 text-sm font-medium transition">
                                <i class="fas fa-trash-alt mr-1"></i> Clear Cart
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?php echo SITE_URL; ?>menu.php" class="text-indigo-600 hover:text-indigo-700 font-medium transition">
                            <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                        </a>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Order Summary</h2>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium"><?php echo formatPrice($total); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="font-medium"><?php echo formatPrice($deliveryFee); ?></span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total</span>
                                    <span class="text-indigo-600"><?php echo formatPrice($grandTotal); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <a href="<?php echo SITE_URL; ?>checkout.php" 
                           class="w-full btn-primary text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition text-center block mt-4">
                            <i class="fas fa-credit-card mr-2"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <div class="text-6xl mb-4">🛒</div>
                <h3 class="text-xl font-bold text-gray-800">Your Cart is Empty</h3>
                <p class="text-gray-500 mt-2">Looks like you haven't added any items to your cart yet.</p>
                <a href="<?php echo SITE_URL; ?>menu.php" class="inline-block mt-4 btn-primary text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                    <i class="fas fa-utensils mr-2"></i> Browse Menu
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateCart(itemId, quantity) {
    if (quantity < 1) return;
    
    fetch('<?php echo SITE_URL; ?>cart-action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update&item_id=' + itemId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    });
}

function removeFromCart(itemId) {
    if (!confirm('Are you sure you want to remove this item?')) return;
    
    fetch('<?php echo SITE_URL; ?>cart-action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=remove&item_id=' + itemId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your cart?')) return;
    
    fetch('<?php echo SITE_URL; ?>cart-action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=clear'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to clear cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>