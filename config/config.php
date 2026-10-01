<?php
// ============================================================
// QUICK BITE - CONFIGURATION
// ============================================================

// Site Configuration
define('SITE_NAME', 'Quick Bite');
define('SITE_URL', 'http://localhost/quick-bite/');
define('ADMIN_URL', SITE_URL . 'admin/');

// Upload Paths
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/quick-bite/assets/images/');
define('FOOD_UPLOAD_PATH', UPLOAD_PATH . 'foods/');
define('RESTAURANT_UPLOAD_PATH', UPLOAD_PATH . 'restaurants/');
define('PROMOTION_UPLOAD_PATH', UPLOAD_PATH . 'promotions/');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . 'profiles/');

// Image URLs
define('FOOD_IMAGE_URL', SITE_URL . 'assets/images/foods/');
define('RESTAURANT_IMAGE_URL', SITE_URL . 'assets/images/restaurants/');
define('PROMOTION_IMAGE_URL', SITE_URL . 'assets/images/promotions/');
define('PROFILE_IMAGE_URL', SITE_URL . 'assets/images/profiles/');

// Default Images
define('DEFAULT_FOOD_IMAGE', 'food-default.jpg');
define('DEFAULT_RESTAURANT_IMAGE', 'restaurant-default.jpg');
define('DEFAULT_PROFILE_IMAGE', 'default.png');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>