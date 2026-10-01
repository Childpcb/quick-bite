<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM restaurants WHERE status = 'active'";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ? OR city LIKE ? OR region LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
}

$query .= " ORDER BY rating DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();

// Get cart count
$cartCount = 0;
if (isLoggedIn()) {
    $cartCount = getCartCount($pdo, $_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Bite - Restaurants</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; color: #1a1a2e; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #e8e8f0; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #6C63FF, #8B83FF); border-radius: 10px; }
        
        /* ===== NAVIGATION ===== */
        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 1px 40px rgba(0,0,0,0.06);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 0 24px;
            height: 76px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        
        .navbar-container {
            max-width: 1280px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        
        .navbar-logo .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #6C63FF, #8B83FF);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            box-shadow: 0 8px 25px rgba(108,99,255,0.3);
        }
        
        .navbar-logo span { font-size: 24px; font-weight: 800; color: #1a1a2e; letter-spacing: -0.5px; }
        .navbar-logo span .highlight { color: #6C63FF; }
        
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 32px;
        }
        
        .navbar-links a {
            color: #4a4a6a;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .navbar-links a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2.5px;
            background: linear-gradient(135deg, #6C63FF, #8B83FF);
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .navbar-links a:hover::after,
        .navbar-links a.active::after { width: 100%; }
        .navbar-links a:hover { color: #6C63FF; }
        .navbar-links a.active { color: #6C63FF; font-weight: 600; }
        
        .navbar-cart {
            position: relative;
            color: #4a4a6a;
            text-decoration: none;
            font-size: 20px;
            transition: color 0.3s ease;
        }
        .navbar-cart:hover { color: #6C63FF; }
        
        .navbar-cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: linear-gradient(135deg, #FF6B6B, #FF4757);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 50%;
            min-width: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(255,71,87,0.4);
        }
        
        .navbar-btn-primary {
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white !important;
            padding: 10px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(108,99,255,0.25);
        }
        .navbar-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(108,99,255,0.35);
            color: white !important;
        }
        .navbar-btn-primary::after { display: none !important; }
        
        .navbar-mobile-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: #1a1a2e;
            cursor: pointer;
            display: none;
            padding: 8px;
        }
        
        .navbar-mobile-menu {
            display: none;
            background: white;
            padding: 24px;
            border-top: 1px solid #f0f0f0;
            flex-direction: column;
            gap: 16px;
            position: absolute;
            top: 76px;
            left: 0;
            right: 0;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }
        .navbar-mobile-menu a {
            color: #4a4a6a;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .navbar-mobile-menu .navbar-btn-primary { text-align: center; border: none; }
        .navbar-mobile-menu.open { display: flex; }
        
        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .navbar-mobile-btn { display: block; }
            .navbar-mobile-menu.open { display: flex; }
        }
        
        /* ===== HERO BANNER ===== */
        .hero-banner {
            padding: 120px 24px 50px;
            background: linear-gradient(135deg, #0f0c29, #1a1a3e, #2d2b55);
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(108,99,255,0.08), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .hero-banner .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1280px;
            margin: 0 auto;
        }
        
        .hero-banner .hero-text h1 {
            font-size: 38px;
            font-weight: 800;
            color: white;
            letter-spacing: -1px;
        }
        
        .hero-banner .hero-text h1 .highlight {
            background: linear-gradient(135deg, #6C63FF, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-banner .hero-text p {
            color: rgba(255,255,255,0.4);
            font-size: 16px;
            margin-top: 8px;
        }
        
        /* ===== SEARCH ===== */
        .search-section {
            padding: 0 24px;
            margin-top: -30px;
            position: relative;
            z-index: 3;
        }
        
        .search-container {
            max-width: 1280px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 6px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .search-container .search-icon { padding: 0 16px; color: #a0a0b8; font-size: 16px; }
        .search-container input {
            flex: 1;
            border: none;
            padding: 14px 0;
            font-size: 14px;
            outline: none;
            background: transparent;
            color: #1a1a2e;
        }
        .search-container input::placeholder { color: #a0a0b8; }
        
        .search-container button {
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white;
            border: none;
            padding: 14px 36px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            white-space: nowrap;
        }
        .search-container button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108,99,255,0.3);
        }
        
        @media (max-width: 768px) {
            .search-container { flex-wrap: wrap; padding: 12px; }
            .search-container input { width: 100%; padding: 12px 0; }
            .search-container button { width: 100%; justify-content: center; }
        }
        
        /* ===== RESTAURANTS GRID ===== */
        .restaurants-section {
            padding: 40px 24px 60px;
            max-width: 1280px;
            margin: 0 auto;
        }
        
        .restaurants-section .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .restaurants-section .section-header h2 { font-size: 24px; font-weight: 700; color: #1a1a2e; }
        .restaurants-section .section-header h2 span { color: #6C63FF; }
        .restaurants-section .section-header .count {
            font-size: 14px;
            color: #6b7280;
            background: #f0f2f5;
            padding: 4px 16px;
            border-radius: 50px;
        }
        
        /* ===== RESTAURANT CARDS - PICHA WAZI ===== */
        .restaurants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 28px;
        }
        
        .restaurant-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .restaurant-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 60px rgba(108,99,255,0.08);
            border-color: rgba(108,99,255,0.2);
        }
        
        /* ---- IMAGE - PICHA WAZI (HAKUNA OVERLAY) ---- */
        .restaurant-card .card-image {
            position: relative;
            overflow: hidden;
            height: 220px;
            background: #e8e8f0;
        }
        
        .restaurant-card .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
            display: block;
        }
        
        .restaurant-card:hover .card-image img {
            transform: scale(1.08);
        }
        
        /* ---- BADGES - ZINAKAA JUU YA PICHA (HAZIFICHII PICHA) ---- */
        .restaurant-card .card-image .rating-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            color: #fbbf24;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(255,255,255,0.08);
            z-index: 5;
        }
        
        .restaurant-card .card-image .rating-badge i { font-size: 12px; }
        
        .restaurant-card .card-image .delivery-badge {
            position: absolute;
            bottom: 16px;
            left: 16px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            color: white;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(255,255,255,0.08);
            z-index: 5;
        }
        
        .restaurant-card .card-image .delivery-badge i {
            font-size: 12px;
            color: #6C63FF;
        }
        
        .restaurant-card .card-image .open-badge {
            position: absolute;
            bottom: 16px;
            right: 16px;
            background: #10b981;
            color: white;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 15px rgba(16,185,129,0.25);
            z-index: 5;
        }
        
        .restaurant-card .card-image .open-badge i { font-size: 12px; }
        
        /* ---- BODY ---- */
        .restaurant-card .card-body {
            padding: 20px 22px 22px;
        }
        
        .restaurant-card .card-body .restaurant-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }
        
        .restaurant-card .card-body .restaurant-name {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .restaurant-card .card-body .restaurant-location {
            font-size: 14px;
            color: #6b7280;
        }
        
        .restaurant-card .card-body .restaurant-location i {
            color: #6C63FF;
            margin-right: 4px;
        }
        
        .restaurant-card .card-body .restaurant-description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin: 8px 0 14px;
        }
        
        .restaurant-card .card-body .restaurant-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            font-size: 14px;
            color: #6b7280;
            padding-top: 14px;
            border-top: 1px solid #f0f2f5;
            margin-bottom: 16px;
        }
        
        .restaurant-card .card-body .restaurant-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .restaurant-card .card-body .restaurant-meta i {
            color: #6C63FF;
            width: 16px;
            font-size: 14px;
        }
        
        .restaurant-card .card-body .btn-view-menu {
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            border: none;
            cursor: pointer;
        }
        
        .restaurant-card .card-body .btn-view-menu:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108,99,255,0.25);
        }
        
        .restaurant-card .card-body .btn-view-menu i {
            transition: transform 0.3s ease;
        }
        
        .restaurant-card .card-body .btn-view-menu:hover i {
            transform: translateX(4px);
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .empty-state .empty-icon { font-size: 64px; margin-bottom: 16px; opacity: 0.4; }
        .empty-state h3 { font-size: 24px; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
        .empty-state p { color: #6b7280; margin-bottom: 24px; }
        .empty-state .btn-reset {
            background: #6C63FF;
            color: white;
            padding: 12px 36px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .empty-state .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108,99,255,0.25);
        }
        
        /* ===== FOOTER ===== */
        .footer {
            background: #0f0c29;
            color: #8888aa;
            padding: 50px 24px 30px;
            border-top: 1px solid rgba(255,255,255,0.04);
        }
        .footer a { color: #8888aa; text-decoration: none; transition: color 0.3s ease; }
        .footer a:hover { color: white; }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .restaurants-grid { grid-template-columns: 1fr; }
            .restaurant-card .card-image { height: 200px; }
            .hero-banner .hero-text h1 { font-size: 28px; }
        }
        
        @media (max-width: 480px) {
            .restaurant-card .card-image { height: 180px; }
            .restaurant-card .card-body { padding: 16px 18px 18px; }
            .restaurant-card .card-body .restaurant-name { font-size: 18px; }
        }
    </style>
</head>
<body>

<!-- ============================================================
     NAVIGATION
     ============================================================ -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo">
            <div class="logo-icon"><i class="fas fa-utensils"></i></div>
            <span>Quick<span class="highlight">Bite</span></span>
        </a>
        
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="restaurants.php" class="active">Restaurants</a>
            <a href="promotions.php">Promotions</a>
            <a href="cart.php" class="navbar-cart">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="navbar-cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></a>
                <a href="logout.php" style="color: #ef4444;">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="navbar-btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
        
        <button class="navbar-mobile-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="navbar-mobile-menu" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="restaurants.php" class="active">Restaurants</a>
        <a href="promotions.php">Promotions</a>
        <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
        <?php if (isLoggedIn()): ?>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" style="color: #ef4444;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="navbar-btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<script>
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.toggle('open');
    });
</script>

<!-- ============================================================
     HERO BANNER
     ============================================================ -->
<section class="hero-banner">
    <div class="hero-content">
        <div class="hero-text">
            <h1>Find <span class="highlight">Restaurants</span></h1>
            <p>Discover the best dining experiences near you</p>
        </div>
    </div>
</section>

<!-- ============================================================
     SEARCH SECTION
     ============================================================ -->
<section class="search-section">
    <div class="search-container">
        <span class="search-icon"><i class="fas fa-search"></i></span>
        <input type="text" id="searchInput" placeholder="Search restaurants by name, cuisine, or location..." value="<?php echo htmlspecialchars($search); ?>">
        <button onclick="searchRestaurants()">Search Now</button>
    </div>
</section>

<!-- ============================================================
     RESTAURANTS GRID - PICHA WAZI
     ============================================================ -->
<section class="restaurants-section">
    <div class="section-header">
        <h2>All <span>Restaurants</span></h2>
        <span class="count"><?php echo count($restaurants); ?> restaurants found</span>
    </div>
    
    <?php if (!empty($restaurants)): ?>
        <div class="restaurants-grid">
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="restaurant-card">
                    <!-- IMAGE - WAZI KABISA (HAKUNA OVERLAY) -->
                    <div class="card-image">
                        <img src="assets/images/restaurants/<?php echo $restaurant['image'] ?: 'restaurant-default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($restaurant['name']); ?>"
                             onerror="this.src='assets/images/restaurants/restaurant-default.jpg'">
                        
                        <!-- BADGES ZINAKAA JUU YA PICHA -->
                        <div class="rating-badge">
                            <i class="fas fa-star"></i> <?php echo number_format($restaurant['rating'], 1); ?>
                        </div>
                        
                        <div class="delivery-badge">
                            <i class="fas fa-clock"></i> <?php echo $restaurant['delivery_time']; ?>
                        </div>
                        
                        <div class="open-badge">
                            <i class="fas fa-check-circle"></i> Open Now
                        </div>
                    </div>
                    
                    <!-- BODY -->
                    <div class="card-body">
                        <div class="restaurant-header">
                            <div>
                                <h3 class="restaurant-name"><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                                <p class="restaurant-location">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($restaurant['city']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <p class="restaurant-description">
                            <?php echo htmlspecialchars(substr($restaurant['description'], 0, 100)) . '...'; ?>
                        </p>
                        
                        <div class="restaurant-meta">
                            <span>
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($restaurant['city']); ?>
                            </span>
                            <span>
                                <i class="fas fa-clock"></i> 
                                <?php echo $restaurant['opening_hours']; ?>
                            </span>
                            <span>
                                <i class="fas fa-truck"></i> 
                                <?php echo formatPrice($restaurant['delivery_fee']); ?>
                            </span>
                        </div>
                        
                        <a href="restaurant-details.php?id=<?php echo $restaurant['id']; ?>" class="btn-view-menu">
                            View Menu <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🏪</div>
            <h3>No Restaurants Found</h3>
            <p>Try adjusting your search criteria</p>
            <a href="restaurants.php" class="btn-reset">
                <i class="fas fa-redo"></i> Reset Search
            </a>
        </div>
    <?php endif; ?>
</section>

<script>
function searchRestaurants() {
    const searchValue = document.getElementById('searchInput').value.trim();
    if (searchValue) {
        window.location.href = 'restaurants.php?search=' + encodeURIComponent(searchValue);
    } else {
        window.location.href = 'restaurants.php';
    }
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') { searchRestaurants(); }
});
</script>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="footer">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">
            <div>
                <h2 style="color: white; font-size: 24px; margin-bottom: 10px;">
                    <i class="fas fa-utensils" style="color: #6C63FF;"></i> QuickBite
                </h2>
                <p style="font-size: 14px; line-height: 1.8;">Delicious food delivered fast.</p>
                <div style="display: flex; gap: 15px; margin-top: 15px;">
                    <a href="#" style="color: #6b7280; transition: all 0.3s ease; font-size: 18px;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" style="color: #6b7280; transition: all 0.3s ease; font-size: 18px;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: #6b7280; transition: all 0.3s ease; font-size: 18px;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #6b7280; transition: all 0.3s ease; font-size: 18px;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div>
                <h4 style="color: white; font-size: 16px; font-weight: 600; margin-bottom: 16px;">Quick Links</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="restaurants.php">Restaurants</a></li>
                    <li><a href="promotions.php">Promotions</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: white; font-size: 16px; font-weight: 600; margin-bottom: 16px;">Support</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                    <li><a href="contact.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="mailto:alisonnaftali2003@gmail.com">Email</a></li>
                    <li><a href="tel:+255754341171">Call</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: white; font-size: 16px; font-weight: 600; margin-bottom: 16px;">Contact</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                    <li style="color: #8888aa;">📞 +255 754 341 171</li>
                    <li style="color: #8888aa;">📧 alisonnaftali2003@gmail.com</li>
                    <li style="color: #8888aa;">📍 Shinyanga, Tanzania</li>
                </ul>
            </div>
        </div>
        <div style="border-top: 1px solid rgba(255,255,255,0.04); margin-top: 40px; padding-top: 20px; text-align: center; font-size: 14px; color: #666688;">
            &copy; <?php echo date('Y'); ?> Quick Bite. All Rights Reserved.
        </div>
    </div>
</footer>

</body>
</html>