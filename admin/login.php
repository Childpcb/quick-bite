<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// If already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginAdmin($pdo, $email, $password);
        if ($result['success']) {
            header('Location: index.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Quick Bite</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        
        .login-container .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-container .logo .icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6C63FF, #8B83FF);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 28px;
            color: white;
            box-shadow: 0 8px 25px rgba(108, 99, 255, 0.2);
        }
        
        .login-container .logo h1 {
            font-size: 26px;
            font-weight: 800;
            color: #1a1a2e;
            letter-spacing: -0.5px;
        }
        
        .login-container .logo h1 span {
            color: #6C63FF;
        }
        
        .login-container .logo p {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }
        
        .login-container .error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .login-container .error i {
            font-size: 16px;
        }
        
        .login-container .form-group {
            margin-bottom: 18px;
        }
        
        .login-container .form-group label {
            display: block;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        
        .login-container .form-group .input-wrapper {
            position: relative;
        }
        
        .login-container .form-group .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
        }
        
        .login-container .form-group input {
            width: 100%;
            padding: 13px 46px 13px 44px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            color: #1a1a2e;
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .login-container .form-group input::placeholder {
            color: #9ca3af;
        }
        
        .login-container .form-group input:focus {
            border-color: #6C63FF;
            background: white;
            box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.06);
        }
        
        /* ===== TOGGLE PASSWORD BUTTON - SAHIHI ===== */
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 18px;
            padding: 6px 8px;
            transition: all 0.3s ease;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
        }
        
        .toggle-password:hover {
            color: #6C63FF;
            background: rgba(108, 99, 255, 0.06);
        }
        
        .toggle-password:focus {
            outline: none;
        }
        
        .toggle-password i {
            pointer-events: none;
        }
        
        .login-container .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .login-container .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108, 99, 255, 0.25);
        }
        
        .login-container .btn-login:active {
            transform: translateY(0);
        }
        
        .login-container .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-container .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .login-container .back-link a:hover {
            color: #6C63FF;
        }
        
        .login-container .extra-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
        }
        
        .login-container .extra-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.3s ease;
        }
        
        .login-container .extra-links a:hover {
            color: #6C63FF;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
            }
            
            .login-container .logo h1 {
                font-size: 22px;
            }
            
            .login-container .form-group input {
                padding: 12px 40px 12px 40px;
                font-size: 13px;
            }
            
            .toggle-password {
                font-size: 16px;
                padding: 4px 6px;
            }
        }
    </style>
</head>
<body>

<!-- Login Container -->
<div class="login-container">
    <!-- Logo -->
    <div class="logo">
        <div class="icon">
            <i class="fas fa-utensils"></i>
        </div>
        <h1>Quick<span>Bite</span></h1>
        <p>Admin Dashboard</p>
    </div>
    
    <!-- Error Message -->
    <?php if ($error): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Login Form -->
    <form method="POST" action="" id="loginForm">
        <div class="form-group">
            <label>Email Address</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock input-icon"></i>
                
                <!-- Password Input -->
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                
                <!-- Show/Hide Password Button -->
                <button type="button" class="toggle-password" onclick="togglePassword()" id="toggleBtn" aria-label="Toggle password visibility">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>
        
        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i>
            Login
        </button>
    </form>
    
    <!-- Back Link -->
    <div class="back-link">
        <a href="<?php echo SITE_URL; ?>">
            <i class="fas fa-arrow-left"></i>
            Back to Website
        </a>
    </div>
</div>

<!-- ============================================================
     TOGGLE PASSWORD JAVASCRIPT
     ============================================================ -->
<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

// Allow Enter key to submit form
document.getElementById('password').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('loginForm').submit();
    }
});

document.querySelector('input[name="email"]').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('loginForm').submit();
    }
});
</script>

</body>
</html>