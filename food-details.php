<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$foodId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($foodId <= 0) {
    header('Location: ' . SITE_URL . 'menu.php');
    exit();
}

$food = getFoodById($pdo, $foodId);
if (!$food) {
    header('Location: ' . SITE_URL . 'menu.php');
    exit();
}

$relatedFood = getRestaurantFood($pdo, $food['restaurant_id']);
$relatedFood = array_filter($relatedFood, function($item) use ($foodId) {
    return $item['id'] != $foodId;
});
$relatedFood = array_slice($relatedFood, 0, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($food['name']); ?> - Quick Bite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; }
        .food-image-detail { width: 100%; height: 400px; object-fit: cover; background: #f3f4f6; }
        .food-card { background: white; border-radius: 16px; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .food-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.12); }
        .food-card img { width: 100%; height: 180px; object-fit: cover; background: #f3f4f6; }
        .food-card-body { padding: 16px; }
        .btn-primary { background: #6C63FF; color: white; padding: 12px 30px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary:hover { background: #5A52D5; transform: scale(1.02); }
        .btn-success { background: #10b981; color: white; padding: 12px 30px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-success:hover { background: #059669; transform: scale(1.02); }
        .quantity-btn { width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb; background: white; cursor: pointer; transition: all 0.3s ease; font-size: 18px; }
        .quantity-btn:hover { background: #6C63FF; color: white; border-color: #6C63FF; }
        .navbar { background: white; box-shadow: 0 2px 20px rgba(0,0,0,0.08); position: fixed; top: 0; left: 0; right: 0; z-index: 1000; padding: 0 20px; height: 70px; display: flex; align-items: center; }
        .navbar-container { max-width: 1200px; width: 100%; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .navbar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .navbar-logo i { color: #6C63FF; font-size: 28px; }
        .navbar-logo span { font-size: 24px; font-weight: 800; color: #1f2937; }
        .navbar-links { display: flex; align-items: center; gap: 30px; }
        .navbar-links a { color: #4b5563; text-decoration: none; font-weight: 500; transition: color 0.3s ease; }
        .navbar-links a:hover { color: #6C63FF; }
        .navbar-cart { position: relative; color: #4b5563; text-decoration: none; font-size: 20px; }
        .navbar-cart-badge { position: absolute; top: -8px; right: -12px; background: #FF4757; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 50%; min-width: 20px; text-align: center; }
        .navbar-mobile-btn { background: none; border: none; font-size: 24px; color: #1f2937; cursor: pointer; display: none; }
        .navbar-mobile-menu { display: none; background: white; padding: 20px; border-top: 1px solid #e5e7eb; flex-direction: column; gap: 15px; position: absolute; top: 70px; left: 0; right: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .navbar-mobile-menu a { color: #4b5563; text-decoration: none; font-weight: 500; padding: 8px 0; }
        .navbar-mobile-menu.open { display: flex; }
        @media (max-width: 768px) { .navbar-links { display: none; } .navbar-mobile-btn { display: block; } .food-image-detail { height: 250px; } }
    </style>
</head>
<body>

<!-- NAVIGATION -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo"><i class="fas fa-utensils"></i><span>Quick Bite</span></a>
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="restaurants.php">Restaurants</a>
            <a href="promotions.php">Promotions</a>
            <a href="cart.php" class="navbar-cart"><i class="fas fa-shopping-cart"></i></a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></a>
                <a href="logout.php" style="color: #ef4444;">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" style="background: linear-gradient(135deg, #6C63FF, #5A52D5); color: white; padding: 10px 25px; border-radius: 50px; text-decoration: none; font-weight: 600;">Sign Up</a>
            <?php endif; ?>
        </div>
        <button class="navbar-mobile-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
    </div>
    <div class="navbar-mobile-menu" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="restaurants.php">Restaurants</a>
        <a href="promotions.php">Promotions</a>
        <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
        <?php if (isLoggedIn()): ?>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" style="color: #ef4444;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" style="background: linear-gradient(135deg, #6C63FF, #5A52D5); color: white; padding: 12px; border-radius: 50px; text-align: center; text-decoration: none; font-weight: 600;">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<script>
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.toggle('open');
    });
</script>

<!-- ===== FOOD DETAILS ===== -->
<div style="max-width: 1200px; margin: 100px auto 40px; padding: 0 20px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        
        <!-- Image -->
        <div>
            <img class="food-image-detail" src="<?php echo FOOD_IMAGE_URL . ($food['image'] ?: DEFAULT_FOOD_IMAGE); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
        </div>
        
        <!-- Details -->
        <div style="padding: 40px 40px 40px 0;">
            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <span style="background: #f3f4f6; padding: 4px 14px; border-radius: 20px; font-size: 13px; color: #6C63FF; font-weight: 600;"><?php echo htmlspecialchars($food['category_name']); ?></span>
                <?php if ($food['is_popular']): ?>
                    <span style="background: #fef3c7; color: #d97706; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">🔥 Popular</span>
                <?php endif; ?>
            </div>
            <h1 style="font-size: 32px; font-weight: 800; color: #1f2937;"><?php echo htmlspecialchars($food['name']); ?></h1>
            <a href="restaurant-details.php?id=<?php echo $food['restaurant_id']; ?>" style="color: #6C63FF; text-decoration: none; font-weight: 500;">
                <i class="fas fa-store"></i> <?php echo htmlspecialchars($food['restaurant_name']); ?>
            </a>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding: 20px 0; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;">
                <div>
                    <span style="font-size: 32px; font-weight: 800; color: #6C63FF;"><?php echo formatPrice($food['price']); ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #f59e0b;"><?php echo getStarRating($food['rating']); ?></span>
                    <span style="color: #6b7280;">(<?php echo number_format($food['rating'], 1); ?>)</span>
                </div>
            </div>
            
            <div style="margin-top: 25px;">
                <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 8px;">Description</h3>
                <p style="color: #6b7280; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($food['description'])); ?></p>
            </div>
            
            <?php if ($food['ingredients']): ?>
            <div style="margin-top: 20px;">
                <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 8px;">Ingredients</h3>
                <p style="color: #6b7280;"><?php echo nl2br(htmlspecialchars($food['ingredients'])); ?></p>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f3f4f6;">
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #1f2937;">Quantity:</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button onclick="updateQuantity(-1)" class="quantity-btn">-</button>
                        <span id="quantity-display" style="font-size: 20px; font-weight: 700; min-width: 40px; text-align: center;">1</span>
                        <button onclick="updateQuantity(1)" class="quantity-btn">+</button>
                    </div>
                </div>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <button onclick="addToCart(<?php echo $food['id']; ?>)" class="btn-primary"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
                    <a href="checkout.php?food=<?php echo $food['id']; ?>" class="btn-success"><i class="fas fa-bolt"></i> Order Now</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Food -->
    <?php if (!empty($relatedFood)): ?>
    <div style="margin-top: 50px;">
        <h2 style="font-size: 24px; font-weight: 800; color: #1f2937; margin-bottom: 20px;">More from <?php echo htmlspecialchars($food['restaurant_name']); ?></h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
            <?php foreach ($relatedFood as $item): ?>
            <div class="food-card">
                <img src="<?php echo FOOD_IMAGE_URL . ($item['image'] ?: DEFAULT_FOOD_IMAGE); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="food-card-body">
                    <h4 style="font-weight: 700; color: #1f2937;"><?php echo htmlspecialchars($item['name']); ?></h4>
                    <p style="font-size: 13px; color: #6b7280;"><?php echo htmlspecialchars($item['category_name']); ?></p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <span style="font-weight: 700; color: #6C63FF;"><?php echo formatPrice($item['price']); ?></span>
                        <a href="food-details.php?id=<?php echo $item['id']; ?>" style="color: #6C63FF; text-decoration: none; font-weight: 600; font-size: 14px;">View <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
let quantity = 1;

function updateQuantity(change) {
    quantity = Math.max(1, quantity + change);
    document.getElementById('quantity-display').textContent = quantity;
}

function addToCart(foodId) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = 'login.php?redirect=food-details.php?id=<?php echo $foodId; ?>';
        return;
    <?php endif; ?>
    
    fetch('cart-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add&food_id=' + foodId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Added to cart successfully!');
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

</body>
</html>