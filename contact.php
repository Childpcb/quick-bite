<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Get cart count
$cartCount = 0;
if (isLoggedIn()) {
    $cartCount = getCartCount($pdo, $_SESSION['user_id']);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if table exists, if not create it
            $stmt = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS contact_messages (
                        id INT(11) AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(100) NOT NULL,
                        subject VARCHAR(200) NOT NULL,
                        message TEXT NOT NULL,
                        status ENUM('unread', 'read') DEFAULT 'unread',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }
            
            // Save to database
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message, status, created_at) 
                VALUES (?, ?, ?, ?, 'unread', NOW())
            ");
            
            if ($stmt->execute([$name, $email, $subject, $message])) {
                $success = 'Your message has been sent successfully! We\'ll get back to you soon.';
            } else {
                $error = 'Failed to save message. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Quick Bite</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1a1a2e; }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.06);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 0 24px;
            height: 70px;
            display: flex;
            align-items: center;
        }
        
        .navbar-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .navbar-logo i { color: #6C63FF; font-size: 28px; }
        .navbar-logo span { font-size: 24px; font-weight: 800; color: #1a1a2e; }
        .navbar-logo span .highlight { color: #6C63FF; }
        
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .navbar-links a {
            color: #4a4a6a;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .navbar-links a:hover { color: #6C63FF; }
        .navbar-links a.active { color: #6C63FF; font-weight: 600; }
        
        .navbar-cart {
            position: relative;
            color: #4a4a6a;
            text-decoration: none;
            font-size: 20px;
        }
        .navbar-cart:hover { color: #6C63FF; }
        
        .navbar-cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #FF4757;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 50%;
            min-width: 20px;
            text-align: center;
        }
        
        .navbar-btn-primary {
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white !important;
            padding: 8px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .navbar-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(108,99,255,0.3);
            color: white !important;
        }
        
        .navbar-mobile-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: #1a1a2e;
            cursor: pointer;
        }
        
        .navbar-mobile-menu {
            display: none;
            background: white;
            padding: 20px;
            border-top: 1px solid #eee;
            flex-direction: column;
            gap: 12px;
            position: absolute;
            top: 70px;
            left: 0;
            right: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .navbar-mobile-menu a {
            color: #4a4a6a;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 0;
        }
        .navbar-mobile-menu.open { display: flex; }
        
        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .navbar-mobile-btn { display: block; }
            .navbar-mobile-menu.open { display: flex; }
        }
        
        .hero-section {
            padding: 100px 24px 50px;
            background: linear-gradient(135deg, #0f0c29, #1a1a3e, #2d2b55);
            text-align: center;
            color: white;
        }
        .hero-section h1 { font-size: 38px; font-weight: 800; }
        .hero-section h1 span { color: #6C63FF; }
        .hero-section p { color: rgba(255,255,255,0.4); font-size: 16px; margin-top: 8px; }
        
        .contact-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.06);
        }
        .contact-card .icon {
            width: 60px;
            height: 60px;
            background: rgba(108,99,255,0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 24px;
            color: #6C63FF;
        }
        .contact-card h3 { font-weight: 700; font-size: 18px; color: #1a1a2e; }
        .contact-card p { color: #6b7280; font-size: 14px; margin-top: 4px; }
        .contact-card a { color: #6C63FF; text-decoration: none; }
        
        .form-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.04);
        }
        .form-container h2 { font-size: 24px; font-weight: 800; color: #1a1a2e; margin-bottom: 24px; }
        .form-container h2 span { color: #6C63FF; }
        
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; color: #374151; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
            background: #f9fafb;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #6C63FF;
            background: white;
            box-shadow: 0 0 0 4px rgba(108,99,255,0.06);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white;
            padding: 14px 36px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
            width: 100%;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108,99,255,0.25);
        }
        
        .footer {
            background: #0f0c29;
            color: #8888aa;
            padding: 50px 24px 30px;
            border-top: 1px solid rgba(255,255,255,0.04);
        }
        .footer a { color: #8888aa; text-decoration: none; transition: color 0.3s ease; }
        .footer a:hover { color: white; }
        
        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .map-container {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        }
        .map-container iframe { width: 100%; height: 400px; border: none; }
    </style>
</head>
<body>

<!-- ===== NAVIGATION ===== -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo">
            <i class="fas fa-utensils"></i>
            <span>Quick<span class="highlight">Bite</span></span>
        </a>
        
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="restaurants.php">Restaurants</a>
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
        
        <button class="navbar-mobile-btn" id="mobileBtn">
            <i class="fas fa-bars"></i>
        </button>
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
            <a href="register.php" class="navbar-btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<script>
    document.getElementById('mobileBtn').addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.toggle('open');
    });
</script>

<!-- ===== HERO ===== -->
<section class="hero-section">
    <h1>Get in <span>Touch</span></h1>
    <p>We'd love to hear from you. Reach out to us anytime.</p>
</section>

<!-- ===== CONTACT CARDS ===== -->
<section style="max-width: 1200px; margin: -30px auto 40px; padding: 0 24px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div class="contact-card">
            <div class="icon"><i class="fas fa-phone"></i></div>
            <h3>Call Us</h3>
            <p><a href="tel:+255754341171">+255 754 341 171</a></p>
            <p style="font-size: 12px; color: #9ca3af;">Mon-Sat: 8AM - 10PM</p>
        </div>
        <div class="contact-card">
            <div class="icon"><i class="fas fa-envelope"></i></div>
            <h3>Email Us</h3>
            <p><a href="mailto:alisonnaftali2003@gmail.com">alisonnaftali2003@gmail.com</a></p>
            <p style="font-size: 12px; color: #9ca3af;">We reply within 24h</p>
        </div>
        <div class="contact-card">
            <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
            <h3>Visit Us</h3>
            <p>Shinyanga, Tanzania</p>
            <p style="font-size: 12px; color: #9ca3af;">Find us on the map</p>
        </div>
        <div class="contact-card">
            <div class="icon"><i class="fas fa-share-alt"></i></div>
            <h3>Follow Us</h3>
            <div style="display: flex; gap: 12px; justify-content: center; margin-top: 8px;">
                <a href="#" style="color: #6b7280; font-size: 18px;"><i class="fab fa-facebook-f"></i></a>
                <a href="#" style="color: #6b7280; font-size: 18px;"><i class="fab fa-twitter"></i></a>
                <a href="#" style="color: #6b7280; font-size: 18px;"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- ===== FORM + MAP ===== -->
<section style="max-width: 1200px; margin: 0 auto 60px; padding: 0 24px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        
        <!-- FORM -->
        <div class="form-container">
            <h2>Send a <span>Message</span></h2>
            
            <?php if ($success): ?>
                <div class="alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Your full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="Your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="What is this about?" required>
                </div>
                
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="5" placeholder="Write your message here..." required></textarea>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
        
        <!-- MAP -->
        <div>
            <div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.04);">
                <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">
                    <i class="fas fa-map" style="color: #6C63FF;"></i> Find Us
                </h2>
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d253682.6220498085!2d39.219057!3d-6.792354!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1844c8f8f8f8f8f8%3A0x8f8f8f8f8f8f8f8f!2sDar%20es%20Salaam%2C%20Tanzania!5e0!3m2!1sen!2stz!4v1730000000000"
                        allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
                <p style="font-size: 13px; color: #6b7280; margin-top: 12px; text-align: center;">
                    <i class="fas fa-map-pin" style="color: #6C63FF;"></i> Shinyanga, Tanzania
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ===== FAQS ===== -->
<section style="max-width: 1200px; margin: 0 auto 60px; padding: 0 24px;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h2 style="font-size: 28px; font-weight: 800;">FAQs</h2>
        <p style="color: #6b7280;">Quick answers to common questions</p>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04);">
            <h3 style="font-weight: 700;"><i class="fas fa-clock" style="color: #6C63FF;"></i> Delivery Time?</h3>
            <p style="color: #6b7280; font-size: 14px;">30-45 minutes depending on location.</p>
        </div>
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04);">
            <h3 style="font-weight: 700;"><i class="fas fa-credit-card" style="color: #6C63FF;"></i> Payment Options?</h3>
            <p style="color: #6b7280; font-size: 14px;">Cash on Delivery, Mobile Money, Card.</p>
        </div>
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04);">
            <h3 style="font-weight: 700;"><i class="fas fa-headset" style="color: #6C63FF;"></i> Customer Support?</h3>
            <p style="color: #6b7280; font-size: 14px;">Contact via phone or email anytime.</p>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div style="max-width: 1200px; margin: 0 auto;">
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