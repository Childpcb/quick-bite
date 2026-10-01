<?php
// ============================================================
// QUICK BITE - HELPER FUNCTIONS (COMPLETE)
// ============================================================

function formatPrice($price) {
    return 'TZS ' . number_format($price, 0);
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-yellow-500',
        'confirmed' => 'bg-blue-500',
        'preparing' => 'bg-purple-500',
        'out_for_delivery' => 'bg-orange-500',
        'delivered' => 'bg-green-500',
        'cancelled' => 'bg-red-500',
    ];
    
    $labels = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'preparing' => 'Preparing',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];
    
    $color = $badges[$status] ?? 'bg-gray-500';
    $label = $labels[$status] ?? ucfirst($status);
    
    return '<span class="px-2 py-1 text-xs text-white rounded-full ' . $color . '">' . $label . '</span>';
}

function getPaymentBadge($status) {
    $badges = [
        'pending' => 'bg-yellow-500',
        'paid' => 'bg-green-500',
        'failed' => 'bg-red-500',
    ];
    
    $color = $badges[$status] ?? 'bg-gray-500';
    return '<span class="px-2 py-1 text-xs text-white rounded-full ' . $color . '">' . ucfirst($status) . '</span>';
}

function generateOrderNumber() {
    $prefix = 'QB';
    $year = date('y');
    $month = date('m');
    $day = date('d');
    $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return $prefix . '-' . $year . $month . $day . $random;
}

function getCartCount($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT SUM(ci.quantity) as total 
        FROM cart c 
        JOIN cart_items ci ON c.id = ci.cart_id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getCartItems($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT ci.*, f.name, f.price, f.image, f.restaurant_id, r.name as restaurant_name
        FROM cart c
        JOIN cart_items ci ON c.id = ci.cart_id
        JOIN food_items f ON ci.food_id = f.id
        JOIN restaurants r ON f.restaurant_id = r.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getCartTotal($pdo, $userId) {
    $items = getCartItems($pdo, $userId);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// ============================================================
// FOOD & RESTAURANT FUNCTIONS
// ============================================================

function getFeaturedFood($pdo, $limit = 8) {
    $stmt = $pdo->prepare("
        SELECT f.*, r.name as restaurant_name, r.id as restaurant_id
        FROM food_items f
        JOIN restaurants r ON f.restaurant_id = r.id
        WHERE f.status = 'active' AND f.is_available = 1 AND f.is_featured = 1 AND r.status = 'active'
        ORDER BY f.rating DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getPopularFood($pdo, $limit = 8) {
    $stmt = $pdo->prepare("
        SELECT f.*, r.name as restaurant_name, r.id as restaurant_id
        FROM food_items f
        JOIN restaurants r ON f.restaurant_id = r.id
        WHERE f.status = 'active' AND f.is_available = 1 AND f.is_popular = 1 AND r.status = 'active'
        ORDER BY f.rating DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getActiveRestaurants($pdo, $limit = 6) {
    $stmt = $pdo->prepare("
        SELECT * FROM restaurants 
        WHERE status = 'active'
        ORDER BY rating DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getCategories($pdo, $limit = 8) {
    $stmt = $pdo->prepare("
        SELECT * FROM categories 
        WHERE status = 'active'
        ORDER BY name ASC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getActivePromotions($pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM promotions 
        WHERE status = 'active' 
        AND start_date <= NOW() 
        AND end_date >= NOW()
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getRestaurantFood($pdo, $restaurantId) {
    $stmt = $pdo->prepare("
        SELECT f.*, c.name as category_name
        FROM food_items f
        JOIN categories c ON f.category_id = c.id
        WHERE f.restaurant_id = ? AND f.status = 'active' AND f.is_available = 1
        ORDER BY f.name ASC
    ");
    $stmt->execute([$restaurantId]);
    return $stmt->fetchAll();
}

function getFoodById($pdo, $foodId) {
    $stmt = $pdo->prepare("
        SELECT f.*, r.name as restaurant_name, r.id as restaurant_id, c.name as category_name
        FROM food_items f
        JOIN restaurants r ON f.restaurant_id = r.id
        JOIN categories c ON f.category_id = c.id
        WHERE f.id = ?
    ");
    $stmt->execute([$foodId]);
    return $stmt->fetch();
}

function getRestaurantById($pdo, $restaurantId) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    return $stmt->fetch();
}

function getCategoryById($pdo, $categoryId) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    return $stmt->fetch();
}

// ============================================================
// NOTIFICATION FUNCTIONS
// ============================================================

function createNotification($pdo, $userId, $title, $message) {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message) 
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$userId, $title, $message]);
}

function getAdminNotifications($pdo, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = 1 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function markNotificationRead($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    return $stmt->execute([$id]);
}

function getUnreadNotificationCount($pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = 1 AND is_read = 0
    ");
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function createAdminNotification($pdo, $title, $message) {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message) 
        VALUES (1, ?, ?)
    ");
    return $stmt->execute([$title, $message]);
}

// ============================================================
// IMAGE UPLOAD & HELPER FUNCTIONS
// ============================================================

function uploadImage($file, $targetDir, $prefix = '') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes) || $file['size'] > $maxSize) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $filename;
    }
    
    return false;
}

function getStarRating($rating) {
    $full = floor($rating);
    $half = $rating - $full >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    
    $stars = '';
    for ($i = 0; $i < $full; $i++) {
        $stars .= '<i class="fas fa-star text-yellow-400"></i>';
    }
    if ($half) {
        $stars .= '<i class="fas fa-star-half-alt text-yellow-400"></i>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $stars .= '<i class="far fa-star text-yellow-400"></i>';
    }
    return $stars;
}

function isFoodInCart($pdo, $userId, $foodId) {
    $stmt = $pdo->prepare("
        SELECT ci.id FROM cart c
        JOIN cart_items ci ON c.id = ci.cart_id
        WHERE c.user_id = ? AND ci.food_id = ?
    ");
    $stmt->execute([$userId, $foodId]);
    return $stmt->fetch() !== false;
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>