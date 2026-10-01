<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'popular';

// Build query
$query = "
    SELECT f.*, r.name as restaurant_name, r.id as restaurant_id, c.name as category_name
    FROM food_items f
    JOIN restaurants r ON f.restaurant_id = r.id
    JOIN categories c ON f.category_id = c.id
    WHERE f.status = 'active' AND f.is_available = 1 AND r.status = 'active'
";
$params = [];

if (!empty($search)) {
    $query .= " AND (f.name LIKE ? OR f.description LIKE ? OR r.name LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params = [$searchParam, $searchParam, $searchParam];
}
if ($category > 0) {
    $query .= " AND f.category_id = ?";
    $params[] = $category;
}
switch ($sort) {
    case 'price_low': $query .= " ORDER BY f.price ASC"; break;
    case 'price_high': $query .= " ORDER BY f.price DESC"; break;
    case 'rating': $query .= " ORDER BY f.rating DESC"; break;
    default: $query .= " ORDER BY f.is_popular DESC, f.rating DESC"; break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$foodItems = $stmt->fetchAll();
$categories = getCategories($pdo, 20);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Bite - Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; }
        .food-card { background: white; border-radius: 16px; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .food-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.12); }
        .food-card .food-image { width: 100%; height: 200px; object-fit: cover; background: #f3f4f6; }
        .food-card-body { padding: 18px; }
        .food-card-title { font-weight: 700; font-size: 18px; color: #1f2937; }
        .food-card-price { font-weight: 700; color: #6C63FF; font-size: 18px; }
        .btn-order { background: #6C63FF; color: white; padding: 8px 20px; border-radius: 10px; font-weight: 600; font-size: 14px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; flex: 1; }
        .btn-order:hover { background: #5A52D5; }
        .btn-cart { background: #f3f4f6; color: #4b5563; padding: 8px 16px; border-radius: 10px; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-cart:hover { background: #e5e7eb; }
        .btn-primary { background: linear-gradient(135deg, #6C63FF, #5A52D5); color: white; padding: 10px 25px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(108,99,255,0.3); }
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
        @media (max-width: 768px) { .navbar-links { display: none; } .navbar-mobile-btn { display: block; } }
        .section-title { font-size: 28px; font-weight: 800; color: #1f2937; }
        .section-subtitle { color: #6b7280; }
        .food-card-rating { color: #f59e0b; font-size: 14px; }
    </style>
</head>
<body>

<!-- NAVIGATION -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo"><i class="fas fa-utensils"></i><span>Quick Bite</span></a>
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="menu.php" style="color: #6C63FF; font-weight: 600;">Menu</a>
            <a href="restaurants.php">Restaurants</a>
            <a href="promotions.php">Promotions</a>
            <a href="cart.php" class="navbar-cart"><i class="fas fa-shopping-cart"></i></a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></a>
                <a href="logout.php" style="color: #ef4444;">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
        <button class="navbar-mobile-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
    </div>
    <div class="navbar-mobile-menu" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="menu.php" style="color: #6C63FF; font-weight: 600;">Menu</a>
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

<!-- ===== MENU CONTENT ===== -->
<div style="max-width: 1200px; margin: 100px auto 40px; padding: 0 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 class="section-title">Our Menu</h1>
            <p class="section-subtitle">Discover delicious food from our restaurants</p>
        </div>
        <span style="color: #6b7280;"><?php echo count($foodItems); ?> items</span>
    </div>

    <!-- Filters -->
    <div style="background: white; border-radius: 16px; padding: 20px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
        <form method="GET" action="" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search food..." style="padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; outline: none; font-size: 14px;">
            <select name="category" style="padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; outline: none; font-size: 14px;">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="sort" style="padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; outline: none; font-size: 14px;">
                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
            </select>
            <button type="submit" style="background: #6C63FF; color: white; padding: 12px 24px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Apply</button>
        </form>
    </div>

    <!-- Food Grid -->
    <?php if (!empty($foodItems)): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px;">
        <?php foreach ($foodItems as $food): ?>
        <div class="food-card">
            <img class="food-image" src="<?php echo FOOD_IMAGE_URL . ($food['image'] ?: DEFAULT_FOOD_IMAGE); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
            <div class="food-card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 class="food-card-title"><?php echo htmlspecialchars($food['name']); ?></h3>
                        <p style="font-size: 14px; color: #6b7280;"><?php echo htmlspecialchars($food['restaurant_name']); ?></p>
                        <span style="font-size: 12px; color: #6C63FF; background: #f3f4f6; padding: 2px 10px; border-radius: 20px; display: inline-block; margin-top: 4px;"><?php echo htmlspecialchars($food['category_name']); ?></span>
                    </div>
                    <span class="food-card-price"><?php echo formatPrice($food['price']); ?></span>
                </div>
                <p style="font-size: 14px; color: #6b7280; margin: 10px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($food['description']); ?></p>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <span class="food-card-rating"><?php echo getStarRating($food['rating']); ?> <span style="color: #6b7280; margin-left: 4px;">(<?php echo number_format($food['rating'], 1); ?>)</span></span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="food-details.php?id=<?php echo $food['id']; ?>" class="btn-order">Order Now</a>
                    <button onclick="addToCart(<?php echo $food['id']; ?>)" class="btn-cart"><i class="fas fa-plus"></i></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 16px;">
        <div style="font-size: 60px; margin-bottom: 20px;">🍽️</div>
        <h3 style="font-size: 24px; font-weight: 700; color: #1f2937;">No Food Found</h3>
        <p style="color: #6b7280; margin-top: 10px;">Try adjusting your search or filters</p>
        <a href="menu.php" style="display: inline-block; margin-top: 20px; background: #6C63FF; color: white; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600;">Reset Filters</a>
    </div>
    <?php endif; ?>
</div>

<script>
function addToCart(foodId) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = 'login.php?redirect=menu.php';
        return;
    <?php endif; ?>
    fetch('cart-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add&food_id=' + foodId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) { location.reload(); } 
        else { alert(data.message || 'Failed to add to cart'); }
    })
    .catch(error => { console.error('Error:', error); alert('Something went wrong. Please try again.'); });
}
</script>

</body>
</html>