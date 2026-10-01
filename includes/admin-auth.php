<?php
// ============================================================
// QUICK BITE - ADMIN AUTHENTICATION
// ============================================================

// Hakikisha config.php imepakiwa
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

session_start();

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . 'login.php');
        exit();
    }
}

function getCurrentAdmin($pdo) {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin' AND status = 'active'");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function loginAdmin($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        if ($admin['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is inactive.'];
        }
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        
        return ['success' => true, 'admin' => $admin];
    }
    
    return ['success' => false, 'message' => 'Invalid admin credentials.'];
}

function logoutAdmin() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_email']);
    session_destroy();
    header('Location: ' . ADMIN_URL . 'login.php');
    exit();
}
?>