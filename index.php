<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Get dynamic data from database
$featuredFood = getFeaturedFood($pdo, 8);
$popularFood = getPopularFood($pdo, 8);
$restaurants = getActiveRestaurants($pdo, 6);
$categories = getCategories($pdo, 8);
$promotions = getActivePromotions($pdo);
$featuredPromotion = !empty($promotions) ? $promotions[array_rand($promotions)] : null;

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
    <title>Quick Bite - Delicious Food, Delivered Fast</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f8fafc;
        }
        
        /* ===== HERO SECTION ===== */
        .hero-section {
            position: relative;
            padding: 140px 20px 100px;
            min-height: 650px;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, rgba(15,12,41,0.92), rgba(48,43,99,0.88), rgba(36,36,62,0.95)), 
                        url('assets/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }
        
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            width: 100%;
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.8rem;
            font-weight: 900;
            line-height: 1.1;
            color: white;
        }
        
        .hero-title span {
            background: linear-gradient(135deg, #6C63FF, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-subtitle {
            color: #c4b5d4;
            font-size: 1.2rem;
            max-width: 480px;
            line-height: 1.8;
            margin-top: 20px;
        }
        
        .hero-badge {
            display: inline-block;
            background: rgba(108,99,255,0.2);
            color: #a78bfa;
            padding: 6px 20px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(108,99,255,0.15);
        }
        
        .btn-hero-primary {
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(108,99,255,0.3);
        }
        
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(108,99,255,0.5);
        }
        
        .btn-hero-secondary {
            background: rgba(255,255,255,0.08);
            color: white;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
        }
        
        .btn-hero-secondary:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-3px);
        }
        
        .hero-image-wrapper {
            position: relative;
        }
        
        .hero-image-wrapper img {
            width: 100%;
            max-width: 550px;
            border-radius: 24px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5);
        }
        
        .hero-floating-card {
            position: absolute;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 14px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .hero-floating-card-bottom {
            bottom: -15px;
            left: -15px;
        }
        
        .hero-floating-card-top {
            top: -15px;
            right: -15px;
        }
        
        .hero-floating-card .fc-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .fc-icon-green { background: #10b981; }
        .fc-icon-orange { background: #f59e0b; }
        
        .hero-floating-card .fc-title {
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            font-size: 15px;
        }
        
        .hero-floating-card .fc-sub {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }
        
        .hero-stats {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-top: 40px;
        }
        
        .hero-stats .stat-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .hero-stats .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #a78bfa;
            border: 1px solid rgba(255,255,255,0.06);
        }
        
        .hero-stats .stat-number {
            font-weight: 700;
            font-size: 18px;
            color: white;
        }
        
        .hero-stats .stat-label {
            font-size: 13px;
            color: #9ca3af;
        }
        
        /* ===== SECTION STYLES ===== */
        .section-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 800;
            color: #1f2937;
        }
        
        .section-subtitle {
            color: #6b7280;
            margin-top: 4px;
        }
        
        .view-all {
            color: #6C63FF;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .view-all:hover {
            color: #5A52D5;
        }
        
        /* ===== CATEGORY CARDS ===== */
        .category-card {
            background: white;
            padding: 25px 15px 20px;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            cursor: pointer;
            text-decoration: none;
            display: block;
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(108,99,255,0.12);
            border-color: #6C63FF;
        }
        
        .category-card img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            margin: 0 auto 12px;
            border: 3px solid #f3f4f6;
            transition: all 0.3s ease;
        }
        
        .category-card:hover img {
            border-color: #6C63FF;
            transform: scale(1.05);
        }
        
        .category-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            display: block;
        }
        
        /* ===== FOOD CARDS ===== */
        .food-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .food-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            border-color: #6C63FF;
        }
        
        .food-card .food-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #f3f4f6;
        }
        
        .food-card-body {
            padding: 20px;
        }
        
        .food-card-title {
            font-weight: 700;
            font-size: 18px;
            color: #1f2937;
        }
        
        .food-card-restaurant {
            font-size: 14px;
            color: #6b7280;
        }
        
        .food-card-price {
            font-weight: 700;
            color: #6C63FF;
            font-size: 18px;
        }
        
        .food-card-rating {
            color: #f59e0b;
            font-size: 14px;
        }
        
        .btn-order {
            background: #6C63FF;
            color: white;
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            flex: 1;
        }
        
        .btn-order:hover {
            background: #5A52D5;
        }
        
        .btn-cart {
            background: #f3f4f6;
            color: #4b5563;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-cart:hover {
            background: #e5e7eb;
        }
        
        /* ===== RESTAURANT CARDS ===== */
        .restaurant-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            text-decoration: none;
            display: block;
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .restaurant-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            border-color: #6C63FF;
        }
        
        .restaurant-card .restaurant-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f3f4f6;
        }
        
        .restaurant-card-body {
            padding: 20px;
        }
        
        .rating-badge {
            background: #10b981;
            color: white;
            padding: 3px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
        }
        
        /* ===== PROMO BANNER ===== */
        .promo-banner {
            background: linear-gradient(135deg, #FF6B6B, #ee5a24);
            border-radius: 24px;
            padding: 50px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .promo-banner .promo-code {
            background: rgba(255,255,255,0.15);
            padding: 8px 24px;
            border-radius: 12px;
            font-family: monospace;
            font-size: 22px;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 1px;
        }
        
        .promo-banner .promo-btn {
            background: white;
            color: #1f2937;
            padding: 14px 40px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .promo-banner .promo-btn:hover {
            transform: scale(1.05);
        }
        
        /* ===== STEPS ===== */
        .step-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin: 0 auto 16px;
            box-shadow: 0 10px 30px rgba(108,99,255,0.25);
        }
        
        .step-card {
            text-align: center;
        }
        
        .step-card h3 {
            font-weight: 700;
            font-size: 18px;
            color: #1f2937;
        }
        
        .step-card p {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }
        
        /* ===== CTA SECTION ===== */
        .cta-section {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            padding: 70px 20px;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 34px;
            font-weight: 800;
            color: white;
            margin-bottom: 15px;
        }
        
        .cta-section p {
            color: #c4b5d4;
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        /* ===== FOOTER ===== */
        .footer {
            background: #0f0c29;
            color: #9ca3af;
            padding: 50px 20px 30px;
        }
        
        .footer a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer a:hover {
            color: white;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                max-width: 100%;
                margin-left: auto;
                margin-right: auto;
            }
            
            .hero-stats {
                justify-content: center;
            }
            
            .hero-image-wrapper {
                max-width: 450px;
                margin: 0 auto;
            }
            
            .hero-floating-card-bottom {
                display: none;
            }
            
            .hero-floating-card-top {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 120px 20px 60px;
                min-height: 500px;
            }
            
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .hero-stats {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .section-title {
                font-size: 22px;
            }
            
            .promo-banner {
                padding: 30px;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }
            
            .btn-hero-primary, .btn-hero-secondary {
                padding: 12px 24px;
                font-size: 14px;
            }
            
            .promo-banner {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<!-- ===== NAVIGATION ===== -->
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ===== HERO SECTION ===== -->
<section class="hero-section">
    <div class="hero-content">
        <div>
            <div class="hero-badge">
                <i class="fas fa-bolt"></i> Fast Delivery • 24/7 Service
            </div>
            <h1 class="hero-title">
                Delicious Food, <br><span>Delivered Fast</span>
            </h1>
            <p class="hero-subtitle">
                Order your favorite meals from the best restaurants near you. Fresh, tasty, and delivered to your doorstep in minutes.
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 35px;">
                <a href="menu.php" class="btn-hero-primary">
                    <i class="fas fa-utensils"></i> Explore Food
                </a>
                <a href="menu.php" class="btn-hero-secondary">
                    <i class="fas fa-shopping-bag"></i> Order Now
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="stat-number">2,000+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-star"></i></div>
                    <div>
                        <div class="stat-number">4.8/5</div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="stat-number">45 min</div>
                        <div class="stat-label">Avg. Delivery Time</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-image-wrapper">
            <img src="assets/images/hero-food.jpg" alt="Delicious Food" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22550%22 height=%22350%22%3E%3Crect fill=%22%23302b63%22 width=%22550%22 height=%22350%22/%3E%3Ctext x=%22275%22 y=%22175%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2240%22 font-family=%22Arial%22%3E🍔%3C/text%3E%3C/svg%3E'">
            
            <div class="hero-floating-card hero-floating-card-bottom">
                <div class="fc-icon fc-icon-green"><i class="fas fa-check"></i></div>
                <div>
                    <p class="fc-title">100% Fresh</p>
                    <p class="fc-sub">Quality guaranteed</p>
                </div>
            </div>
            
            <div class="hero-floating-card hero-floating-card-top">
                <div class="fc-icon fc-icon-orange"><i class="fas fa-clock"></i></div>
                <div>
                    <p class="fc-title">Fast Delivery</p>
                    <p class="fc-sub">Under 45 min</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== PROMO BANNER ===== -->
<?php if ($featuredPromotion): ?>
<section style="padding: 40px 20px;">
    <div class="section-container">
        <div class="promo-banner">
            <div style="position: relative; z-index: 2; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px;">
                <div>
                    <span style="display: inline-block; background: rgba(255,255,255,0.15); padding: 4px 16px; border-radius: 50px; font-size: 13px; font-weight: 600;">🔥 <?php echo htmlspecialchars($featuredPromotion['title']); ?></span>
                    <h2 style="font-size: 28px; font-weight: 800; margin: 10px 0 5px;"><?php echo htmlspecialchars($featuredPromotion['description']); ?></h2>
                    <?php if ($featuredPromotion['promo_code']): ?>
                        <div style="margin-top: 10px;">
                            <span class="promo-code"><?php echo htmlspecialchars($featuredPromotion['promo_code']); ?></span>
                            <span style="margin-left: 12px; font-size: 14px; opacity: 0.8;">Use code at checkout</span>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="menu.php" class="promo-btn">
                    Order Now <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== CATEGORIES ===== -->
<section style="padding: 40px 20px;">
    <div class="section-container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Food Categories</h2>
                <p class="section-subtitle">Explore our delicious food categories</p>
            </div>
            <a href="menu.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px;">
            <?php foreach ($categories as $category): 
                $catImage = strtolower(str_replace(' ', '-', $category['name'])) . '-cat.jpg';
                $catImage = !empty($category['image']) ? $category['image'] : $catImage;
            ?>
            <a href="menu.php?category=<?php echo $category['id']; ?>" class="category-card">
                <img src="<?php echo SITE_URL; ?>assets/images/categories/<?php echo $catImage; ?>" 
                     alt="<?php echo htmlspecialchars($category['name']); ?>"
                     onerror="this.src='<?php echo SITE_URL; ?>assets/images/categories/category-default.jpg'">
                <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== POPULAR FOOD ===== -->
<section style="padding: 40px 20px; background: #f8fafc;">
    <div class="section-container">
        <div class="section-header">
            <div>
                <h2 class="section-title">🔥 Popular Food</h2>
                <p class="section-subtitle">Most loved dishes by our customers</p>
            </div>
            <a href="menu.php?sort=popular" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 25px;">
            <?php foreach ($popularFood as $food): ?>
            <div class="food-card">
                <img class="food-image" src="<?php echo FOOD_IMAGE_URL . ($food['image'] ?: DEFAULT_FOOD_IMAGE); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22300%22 height=%22200%22/%3E%3Ctext x=%22150%22 y=%22105%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-size=%2240%22 font-family=%22Arial%22%3E🍽️%3C/text%3E%3C/svg%3E'">
                <div class="food-card-body">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h3 class="food-card-title"><?php echo htmlspecialchars($food['name']); ?></h3>
                            <p class="food-card-restaurant"><?php echo htmlspecialchars($food['restaurant_name']); ?></p>
                        </div>
                        <span class="food-card-price"><?php echo formatPrice($food['price']); ?></span>
                    </div>
                    <p style="font-size: 14px; color: #6b7280; margin: 10px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($food['description']); ?></p>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <span class="food-card-rating">
                            <?php echo getStarRating($food['rating']); ?>
                            <span style="color: #6b7280; margin-left: 4px;">(<?php echo number_format($food['rating'], 1); ?>)</span>
                        </span>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="food-details.php?id=<?php echo $food['id']; ?>" class="btn-order">Order Now</a>
                        <button onclick="addToCart(<?php echo $food['id']; ?>)" class="btn-cart"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== FEATURED RESTAURANTS ===== -->
<section style="padding: 40px 20px;">
    <div class="section-container">
        <div class="section-header">
            <div>
                <h2 class="section-title">🏪 Featured Restaurants</h2>
                <p class="section-subtitle">Top restaurants near you</p>
            </div>
            <a href="restaurants.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
            <?php foreach ($restaurants as $restaurant): ?>
            <a href="restaurant-details.php?id=<?php echo $restaurant['id']; ?>" class="restaurant-card">
                <img class="restaurant-image" src="<?php echo RESTAURANT_IMAGE_URL . ($restaurant['image'] ?: DEFAULT_RESTAURANT_IMAGE); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22180%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22300%22 height=%22180%22/%3E%3Ctext x=%22150%22 y=%2290%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-size=%2230%22 font-family=%22Arial%22%3E🏪%3C/text%3E%3C/svg%3E'">
                <div class="restaurant-card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-weight: 700; font-size: 18px; color: #1f2937;"><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                        <span class="rating-badge"><i class="fas fa-star" style="color: #fbbf24;"></i> <?php echo number_format($restaurant['rating'], 1); ?></span>
                    </div>
                    <p style="font-size: 14px; color: #6b7280; margin: 8px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($restaurant['description']); ?></p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #6b7280; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f3f4f6;">
                        <span><i class="fas fa-map-marker-alt" style="color: #6C63FF;"></i> <?php echo htmlspecialchars($restaurant['city']); ?></span>
                        <span><i class="fas fa-clock" style="color: #6C63FF;"></i> <?php echo $restaurant['delivery_time']; ?></span>
                        <span style="font-weight: 600; color: #6C63FF;"><?php echo formatPrice($restaurant['delivery_fee']); ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section style="padding: 60px 20px; background: #f8fafc;">
    <div class="section-container" style="text-align: center;">
        <h2 class="section-title">How Quick Bite Works</h2>
        <p class="section-subtitle">Order food in 4 simple steps</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-top: 40px;">
            <div class="step-card">
                <div class="step-circle"><i class="fas fa-utensils"></i></div>
                <h3>1. Choose Your Food</h3>
                <p>Browse through our delicious menu items</p>
            </div>
            <div class="step-card">
                <div class="step-circle"><i class="fas fa-shopping-cart"></i></div>
                <h3>2. Add to Cart</h3>
                <p>Add your favorite items to the cart</p>
            </div>
            <div class="step-card">
                <div class="step-circle"><i class="fas fa-credit-card"></i></div>
                <h3>3. Place Your Order</h3>
                <p>Checkout and confirm your order</p>
            </div>
            <div class="step-card">
                <div class="step-circle"><i class="fas fa-smile"></i></div>
                <h3>4. Enjoy Your Meal</h3>
                <p>Get your food delivered hot and fresh</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== CALL TO ACTION ===== -->
<section class="cta-section">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2>Ready to Order?</h2>
        <p>Get your favorite food delivered to your doorstep in minutes</p>
        <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
            <a href="menu.php" style="background: linear-gradient(135deg, #6C63FF, #5A52D5); color: white; padding: 16px 45px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease;">
                <i class="fas fa-utensils"></i> Order Now
            </a>
            <a href="register.php" style="background: rgba(255,255,255,0.08); color: white; padding: 16px 45px; border-radius: 50px; font-weight: 700; text-decoration: none; border: 1px solid rgba(255,255,255,0.15); transition: all 0.3s ease;">
                <i class="fas fa-user-plus"></i> Sign Up Free
            </a>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">
            <div>
                <h2 style="color: white; font-size: 24px; margin-bottom: 10px;">
                    <i class="fas fa-utensils" style="color: #6C63FF;"></i> Quick Bite                </h2>
                <p style="font-size: 14px; color: #9ca3af; line-height: 1.6;">Delicious food delivered fast. Order your favorite meals from the best restaurants near you.</p>
                <div style="display: flex; gap: 15px; margin-top: 15px;">
                    <a href="#" style="color: #6b7280; transition: all 0.3s ease; font-size: 18px;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" style="color: #6b7280; transition: all 0.3s ease; font-size: 18px;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: #6b7280; transition: all 0.3s ease; font-size: 18px;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #6b7280; transition: all 0.3s ease; font-size: 18px;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div>
                <h3 style="color: white; font-size: 16px; font-weight: 600; margin-bottom: 15px;">Quick Links</h3>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="restaurants.php">Restaurants</a></li>
                    <li><a href="promotions.php">Promotions</a></li>
                </ul>
            </div>
            <div>
                <h3 style="color: white; font-size: 16px; font-weight: 600; margin-bottom: 15px;">Customer Support</h3>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    <li><a href="contact.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="mailto:alisonnaftali2003@gmail.com">Email Support</a></li>
                    <li><a href="tel:+255754341171">Call Support</a></li>
                </ul>
            </div>
            <div>
                <h3 style="color: white; font-size: 16px; font-weight: 600; margin-bottom: 15px;">Contact Info</h3>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    <li style="color: #9ca3af;">📞 +255 754 341 171</li>
                    <li style="color: #9ca3af;">📧 alisonnaftali2003@gmail.com</li>
                    <li style="color: #9ca3af;">📍 Shinyanga, Tanzania</li>
                </ul>
            </div>
        </div>
        <div style="border-top: 1px solid #1f1f3a; margin-top: 40px; padding-top: 20px; text-align: center; font-size: 14px; color: #6b7280;">
            &copy; <?php echo date('Y'); ?> Quick Bite. All Rights Reserved.
        </div>
    </div>
</footer>

<script>
function addToCart(foodId) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = 'login.php?redirect=index.php';
        return;
    <?php endif; ?>
    
    fetch('cart-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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

</body>
</html>