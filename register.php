<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit();
}

$error = '';
$success = '';
$name = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($pdo, $name, $email, $phone, $password);
        if ($result['success']) {
            $success = $result['message'];
            $name = $email = $phone = '';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 pt-20 pb-12 px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-2xl text-indigo-600"></i>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Create Account</h2>
            <p class="text-gray-500 mt-2">Join Quick Bite and start ordering</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
                <p class="mt-2">
                    <a href="<?php echo SITE_URL; ?>login.php" class="text-green-700 font-semibold hover:underline">Click here to login</a>
                </p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium mb-2">Full Name</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name); ?>"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Enter your full name">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-3 text-gray-400"></i>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Enter your email">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="phone" class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <div class="relative">
                    <i class="fas fa-phone absolute left-3 top-3 text-gray-400"></i>
                    <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($phone); ?>"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Enter your phone number">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                    <input type="password" id="password" name="password" required
                           class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Create a password (min 6 characters)">
                    <button type="button" onclick="togglePassword('password', 'togglePasswordIcon')" 
                            class="absolute right-3 top-3 text-gray-400 hover:text-indigo-600 transition">
                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                <div class="relative">
                    <i class="fas fa-check-circle absolute left-3 top-3 text-gray-400"></i>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Confirm your password">
                    <button type="button" onclick="togglePassword('confirm_password', 'toggleConfirmIcon')" 
                            class="absolute right-3 top-3 text-gray-400 hover:text-indigo-600 transition">
                        <i class="fas fa-eye" id="toggleConfirmIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="w-full btn-primary text-white py-3 rounded-lg font-semibold hover:shadow-lg transition">
                <i class="fas fa-user-plus mr-2"></i> Create Account
            </button>
        </form>
        
        <div class="text-center mt-6">
            <p class="text-gray-600">Already have an account? <a href="<?php echo SITE_URL; ?>login.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">Sign In</a></p>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>