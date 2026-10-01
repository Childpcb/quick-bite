<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$cartCount = 0;
if (isLoggedIn()) {
    $cartCount = getCartCount($pdo, $_SESSION['user_id']);
}
$isLoggedIn = isLoggedIn();
?>
<nav style="background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: fixed; top: 0; left: 0; right: 0; z-index: 1000; padding: 0 20px; height: 70px; display: flex; align-items: center;">
    <div style="max-width: 1200px; width: 100%; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
        
        <!-- LOGO -->
        <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; font-size: 24px; font-weight: 800; color: #1a1a2e;">
            <i class="fas fa-utensils" style="color: #6C63FF;"></i> Quick<span style="color: #6C63FF;">Bite</span>
        </a>
        
        <!-- LINKS -->
        <div style="display: flex; align-items: center; gap: 25px;">
            <a href="index.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; font-size: 14px;">Home</a>
            <a href="menu.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; font-size: 14px;">Menu</a>
            <a href="restaurants.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; font-size: 14px;">Restaurants</a>
            <a href="promotions.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; font-size: 14px;">Promotions</a>
            
            <a href="cart.php" style="position: relative; color: #4a4a6a; text-decoration: none; font-size: 20px;">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cartCount > 0): ?>
                    <span style="position: absolute; top: -8px; right: -12px; background: #FF4757; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 50%;"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            
            <?php if ($isLoggedIn): ?>
                <a href="profile.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; font-size: 14px;"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></a>
                <a href="logout.php" style="color: #ef4444; text-decoration: none; font-weight: 500; font-size: 14px;">Logout</a>
            <?php else: ?>
                <a href="login.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; font-size: 14px;">Login</a>
                <a href="register.php" style="background: linear-gradient(135deg, #6C63FF, #5A52D5); color: white; padding: 8px 25px; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 14px;">Sign Up</a>
            <?php endif; ?>
        </div>
        
        <!-- MOBILE BUTTON -->
        <button id="mobileBtn" style="display: none; background: none; border: none; font-size: 24px; color: #1a1a2e; cursor: pointer;">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <!-- MOBILE MENU -->
    <div id="mobileMenu" style="display: none; background: white; padding: 20px; border-top: 1px solid #eee; flex-direction: column; gap: 12px; position: absolute; top: 70px; left: 0; right: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <a href="index.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; padding: 8px 0;">Home</a>
        <a href="menu.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; padding: 8px 0;">Menu</a>
        <a href="restaurants.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; padding: 8px 0;">Restaurants</a>
        <a href="promotions.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; padding: 8px 0;">Promotions</a>
        <a href="cart.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; padding: 8px 0;"><i class="fas fa-shopping-cart"></i> Cart</a>
        <?php if ($isLoggedIn): ?>
            <a href="profile.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; padding: 8px 0;"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" style="color: #ef4444; text-decoration: none; font-weight: 500; padding: 8px 0;">Logout</a>
        <?php else: ?>
            <a href="login.php" style="color: #4a4a6a; text-decoration: none; font-weight: 500; padding: 8px 0;">Login</a>
            <a href="register.php" style="background: linear-gradient(135deg, #6C63FF, #5A52D5); color: white; padding: 10px; border-radius: 50px; text-align: center; text-decoration: none; font-weight: 600;">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<style>
    @media (max-width: 768px) {
        nav .navbar-links { display: none !important; }
        #mobileBtn { display: block !important; }
        #mobileMenu.open { display: flex !important; }
    }
</style>

<script>
    document.getElementById('mobileBtn').addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.toggle('open');
    });
</script>