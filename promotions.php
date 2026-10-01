<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Get all active promotions
$stmt = $pdo->prepare("
    SELECT * FROM promotions 
    WHERE status = 'active' 
    AND start_date <= NOW() 
    AND end_date >= NOW()
    ORDER BY created_at DESC
");
$stmt->execute();
$promotions = $stmt->fetchAll();

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
    <title>Quick Bite - Promotions</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1a1a2e; }
        
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
        
        /* ===== HERO SECTION ===== */
        .hero-section {
            padding: 120px 24px 50px;
            background: linear-gradient(135deg, #0f0c29, #1a1a3e, #2d2b55);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
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
        
        .hero-section h1 {
            font-size: 38px;
            font-weight: 800;
            color: white;
            letter-spacing: -1px;
        }
        
        .hero-section h1 span {
            background: linear-gradient(135deg, #6C63FF, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-section p {
            color: rgba(255,255,255,0.4);
            font-size: 16px;
            margin-top: 8px;
        }
        
        /* ===== PROMOTIONS GRID ===== */
        .promotions-section {
            padding: 40px 24px 60px;
            max-width: 1280px;
            margin: 0 auto;
        }
        
        .promotions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 28px;
        }
        
        /* ===== PROMO CARD - PICHA ZINAFIT VIZURI ===== */
        .promo-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            transition: all 0.4s ease;
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .promo-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 60px rgba(108,99,255,0.08);
            border-color: rgba(108,99,255,0.2);
        }
        
        /* ---- BANNER - PICHA INAFIT VIZURI ---- */
        .promo-card .promo-banner {
            position: relative;
            height: 180px;
            overflow: hidden;
            background: linear-gradient(135deg, #6C63FF, #8B83FF);
        }
        
        .promo-card .promo-banner .banner-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        
        .promo-card .promo-banner .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15,12,41,0.75), rgba(108,99,255,0.6));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        
        .promo-card .promo-banner .discount-display {
            text-align: center;
            color: white;
            position: relative;
            z-index: 3;
        }
        
        .promo-card .promo-banner .discount-value {
            font-size: 52px;
            font-weight: 900;
            line-height: 1;
            text-shadow: 0 4px 20px rgba(0,0,0,0.4);
            letter-spacing: -2px;
        }
        
        .promo-card .promo-banner .discount-label {
            font-size: 13px;
            font-weight: 700;
            opacity: 0.95;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.4);
        }
        
        .promo-card .promo-banner .tag-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(10px);
            color: white;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            z-index: 4;
            border: 1px solid rgba(255,255,255,0.15);
        }
        
        .promo-card .promo-banner .discount-type-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: rgba(255,107,107,0.9);
            color: white;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            z-index: 4;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* ---- BODY ---- */
        .promo-card .promo-body {
            padding: 24px;
        }
        
        .promo-card .promo-body h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }
        
        .promo-card .promo-body .promo-description {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
            min-height: 44px;
        }
        
        .promo-card .promo-body .promo-code-box {
            background: #f8f9fc;
            border: 1px dashed #c7d2fe;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .promo-card .promo-body .promo-code-box .code-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1.5px;
        }
        
        .promo-card .promo-body .promo-code-box .code-value {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: 700;
            color: #6C63FF;
            letter-spacing: 1px;
        }
        
        .promo-card .promo-body .promo-code-box .copy-btn {
            background: #6C63FF;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .promo-card .promo-body .promo-code-box .copy-btn:hover {
            background: #5A52D5;
            transform: scale(1.05);
        }
        
        .promo-card .promo-body .promo-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 16px;
            padding-top: 16px;
            border-top: 1px solid #f0f2f5;
        }
        
        .promo-card .promo-body .promo-meta i {
            color: #6C63FF;
            margin-right: 6px;
        }
        
        .promo-card .promo-body .btn-use-promo {
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
            gap: 8px;
            width: 100%;
            border: none;
            cursor: pointer;
        }
        
        .promo-card .promo-body .btn-use-promo:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108,99,255,0.25);
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .empty-state .empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.4;
        }
        
        .empty-state h3 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: #6b7280;
            margin-bottom: 24px;
        }
        
        .empty-state .btn-browse {
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white;
            padding: 14px 36px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .empty-state .btn-browse:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108,99,255,0.3);
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
            .promotions-grid { grid-template-columns: 1fr; }
            .hero-section h1 { font-size: 28px; }
            .promo-card .promo-banner { height: 160px; }
            .promo-card .promo-banner .discount-value { font-size: 42px; }
        }
    </style>
</head>
<body>

<!-- ===== NAVIGATION ===== -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo">
            <div class="logo-icon"><i class="fas fa-utensils"></i></div>
            <span>Quick<span class="highlight">Bite</span></span>
        </a>
        
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="restaurants.php">Restaurants</a>
            <a href="promotions.php" class="active">Promotions</a>
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
        <a href="restaurants.php">Restaurants</a>
        <a href="promotions.php" class="active">Promotions</a>
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

<!-- ===== HERO SECTION ===== -->
<section class="hero-section">
    <h1>Special Offers & <span>Promotions</span></h1>
    <p>Get amazing deals on your favorite food</p>
</section>

<!-- ===== PROMOTIONS GRID ===== -->
<section class="promotions-section">
    <?php if (!empty($promotions)): ?>
        <div class="promotions-grid">
            <?php foreach ($promotions as $promo): ?>
                <div class="promo-card">
                    <!-- ===== BANNER WITH IMAGE ===== -->
                    <div class="promo-banner">
                        <?php 
                        $bannerImage = !empty($promo['banner_image']) && file_exists(PROMOTION_UPLOAD_PATH . $promo['banner_image']) 
                            ? PROMOTION_IMAGE_URL . $promo['banner_image'] 
                            : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=400&fit=crop';
                        ?>
                        
                        <!-- Image (fits perfectly with object-fit) -->
                        <img src="<?php echo $bannerImage; ?>" 
                             alt="<?php echo htmlspecialchars($promo['title']); ?>" 
                             class="banner-image"
                             onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=400&fit=crop'">
                        
                        <!-- Dark Overlay for readability -->
                        <div class="banner-overlay">
                            <div class="discount-display">
                                <div class="discount-value">
                                    <?php 
                                    if ($promo['discount_type'] === 'percentage') {
                                        echo number_format($promo['discount_value'], 0) . '%';
                                    } elseif ($promo['discount_type'] === 'fixed') {
                                        echo number_format($promo['discount_value'] / 1000, 0) . 'K';
                                    } elseif ($promo['discount_type'] === 'free_delivery') {
                                        echo 'FREE';
                                    }
                                    ?>
                                </div>
                                <div class="discount-label">
                                    <?php 
                                    if ($promo['discount_type'] === 'percentage') {
                                        echo 'DISCOUNT';
                                    } elseif ($promo['discount_type'] === 'fixed') {
                                        echo 'TZS OFF';
                                    } elseif ($promo['discount_type'] === 'free_delivery') {
                                        echo 'DELIVERY';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Badges -->
                        <span class="tag-badge">🔥 Limited Time</span>
                        <span class="discount-type-badge">
                            <?php 
                            if ($promo['discount_type'] === 'free_delivery') {
                                echo 'Free Shipping';
                            } elseif ($promo['discount_type'] === 'percentage') {
                                echo 'Percent Off';
                            } else {
                                echo 'Fixed Amount';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <!-- ===== BODY ===== -->
                    <div class="promo-body">
                        <h3><?php echo htmlspecialchars($promo['title']); ?></h3>
                        <p class="promo-description"><?php echo htmlspecialchars($promo['description']); ?></p>
                        
                        <?php if ($promo['promo_code']): ?>
                            <div class="promo-code-box">
                                <div>
                                    <div class="code-label">Promo Code</div>
                                    <div class="code-value"><?php echo htmlspecialchars($promo['promo_code']); ?></div>
                                </div>
                                <button class="copy-btn" onclick="copyCode('<?php echo htmlspecialchars($promo['promo_code']); ?>')">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="promo-meta">
                            <span>
                                <i class="fas fa-calendar-alt"></i>
                                Valid until <?php echo date('M d, Y', strtotime($promo['end_date'])); ?>
                            </span>
                        </div>
                        
                        <a href="menu.php" class="btn-use-promo">
                            <i class="fas fa-utensils"></i> Order Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🎉</div>
            <h3>No Active Promotions</h3>
            <p>Check back later for exciting deals and offers!</p>
            <a href="menu.php" class="btn-browse">
                <i class="fas fa-utensils"></i> Browse Menu
            </a>
        </div>
    <?php endif; ?>
</section>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        alert('✅ Promo code "' + code + '" copied to clipboard!');
    }).catch(function() {
        alert('Failed to copy. Please copy manually: ' + code);
    });
}
</script>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">
            <div>
                <h2 style="color: white; font-size: 24px;"><i class="fas fa-utensils" style="color: #6C63FF;"></i> QuickBite</h2>
                <p style="font-size: 14px;">Delicious food delivered fast.</p>
            </div>
            <div>
                <h4 style="color: white; font-size: 16px; font-weight: 600;">Quick Links</h4>
                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="restaurants.php">Restaurants</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: white; font-size: 16px; font-weight: 600;">Contact</h4>
                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
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