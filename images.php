<?php
// ============================================================
// QUICK BITE - IMAGE GENERATOR (SAHIHI)
// Run: http://localhost/quick-bite/images.php
// ============================================================

// Check if GD Library is enabled
if (!extension_loaded('gd')) {
    die("❌ GD Library is not enabled! Please enable it in php.ini\n");
}

echo "✅ GD Library is enabled!\n\n";

$basePath = __DIR__ . '/assets/images/';

// Create all directories
$directories = [
    $basePath . 'foods/',
    $basePath . 'restaurants/',
    $basePath . 'promotions/',
    $basePath . 'profiles/',
    $basePath . 'categories/'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "Created directory: " . str_replace(__DIR__ . '/', '', $dir) . "\n";
    }
}

// ============================================================
// FUNCTION: CREATE JPEG IMAGE
// ============================================================
function createPlaceholderImage($path, $text, $width = 400, $height = 300, $bgColor = '#6C63FF') {
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }
    
    $image = imagecreatetruecolor($width, $height);
    if (!$image) return false;
    
    list($r, $g, $b) = sscanf($bgColor, '#%02x%02x%02x');
    $bg = imagecolorallocate($image, $r, $g, $b);
    imagefill($image, 0, 0, $bg);
    
    $white = imagecolorallocate($image, 255, 255, 255);
    $whiteAlpha = imagecolorallocatealpha($image, 255, 255, 255, 50);
    
    for ($i = -$height; $i < $width + $height; $i += 30) {
        imageline($image, $i, 0, $i + $height, $height, $whiteAlpha);
    }
    
    imagefilledellipse($image, $width/2, $height/2 - 20, 100, 100, $whiteAlpha);
    
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $fontSize = 5;
    $textWidth = imagefontwidth($fontSize) * strlen($text);
    $x = ($width - $textWidth) / 2;
    $y = ($height - imagefontheight($fontSize)) / 2 + 10;
    
    imagestring($image, $fontSize, $x + 1, $y + 1, $text, imagecolorallocate($image, 0, 0, 0));
    imagestring($image, $fontSize, $x, $y, $text, $textColor);
    
    $result = imagejpeg($image, $path, 90);
    imagedestroy($image);
    
    if ($result) {
        echo "Created: " . str_replace(__DIR__ . '/', '', $path) . "\n";
        return true;
    }
    return false;
}

// ============================================================
// FUNCTION: CREATE PNG IMAGE (SAHIHI)
// ============================================================
function createPlaceholderPNG($path, $text, $size = 200) {
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }
    
    $image = imagecreatetruecolor($size, $size);
    if (!$image) return false;
    
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    $purple = imagecolorallocate($image, 108, 99, 255);
    imagefilledellipse($image, $size/2, $size/2, $size - 10, $size - 10, $purple);
    
    $white = imagecolorallocate($image, 255, 255, 255);
    $fontSize = 5;
    $textWidth = imagefontwidth($fontSize) * strlen($text);
    $x = ($size - $textWidth) / 2;
    $y = ($size - imagefontheight($fontSize)) / 2;
    
    imagestring($image, $fontSize, $x, $y, $text, $white);
    
    $result = imagepng($image, $path);
    imagedestroy($image);
    
    if ($result) {
        echo "Created: " . str_replace(__DIR__ . '/', '', $path) . "\n";
        return true;
    }
    return false;
}

// ============================================================
// FOOD IMAGES
// ============================================================
echo "\n📁 Generating Food Images...\n";

$foodImages = [
    'chicken-burger.jpg' => 'Chicken Burger',
    'beef-burger.jpg' => 'Beef Burger',
    'double-cheese-burger.jpg' => 'Double Cheese',
    'french-fries.jpg' => 'French Fries',
    'margherita.jpg' => 'Margherita',
    'chicken-pizza.jpg' => 'Chicken Pizza',
    'pepperoni-pizza.jpg' => 'Pepperoni Pizza',
    'garlic-bread.jpg' => 'Garlic Bread',
    'chicken-biryani.jpg' => 'Chicken Biryani',
    'butter-chicken.jpg' => 'Butter Chicken',
    'naan.jpg' => 'Naan Bread',
    'pilau-rice.jpg' => 'Pilau Rice',
    'grilled-salmon.jpg' => 'Grilled Salmon',
    'quinoa-salad.jpg' => 'Quinoa Salad',
    'fresh-juice.jpg' => 'Fresh Juice',
    'smoothie-bowl.jpg' => 'Smoothie Bowl',
    'fried-chicken.jpg' => 'Fried Chicken',
    'chicken-wings.jpg' => 'Chicken Wings',
    'soft-drink.jpg' => 'Soft Drink',
    'milkshake.jpg' => 'Milkshake',
    'food-default.jpg' => 'Food'
];

$colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#FF8A5C', '#A29BFE'];

$i = 0;
foreach ($foodImages as $filename => $label) {
    $path = $basePath . 'foods/' . $filename;
    if (!file_exists($path)) {
        $color = $colors[$i % count($colors)];
        createPlaceholderImage($path, $label, 400, 300, $color);
        $i++;
    }
}

// ============================================================
// RESTAURANT IMAGES
// ============================================================
echo "\n📁 Generating Restaurant Images...\n";

$restaurantImages = [
    'quick-grill.jpg' => 'Quick Grill',
    'pizza-palace.jpg' => 'Pizza Palace',
    'spice-corner.jpg' => 'Spice Corner',
    'fresh-bites.jpg' => 'Fresh Bites',
    'chicken-express.jpg' => 'Chicken Express',
    'restaurant-default.jpg' => 'Restaurant'
];

$restaurantColors = ['#2D3436', '#0984E3', '#00B894', '#FDCB6E', '#E17055', '#6C5CE7'];

$i = 0;
foreach ($restaurantImages as $filename => $label) {
    $path = $basePath . 'restaurants/' . $filename;
    if (!file_exists($path)) {
        $color = $restaurantColors[$i % count($restaurantColors)];
        createPlaceholderImage($path, $label, 600, 400, $color);
        $i++;
    }
}

// ============================================================
// PROMOTION IMAGES
// ============================================================
echo "\n📁 Generating Promotion Images...\n";

$promotionImages = [
    'weekend-special.jpg' => 'Weekend Special',
    'free-delivery.jpg' => 'Free Delivery',
    'first-order.jpg' => 'First Order',
    'pizza-wednesday.jpg' => 'Pizza Wednesday',
    'promotion-default.jpg' => 'Promotion'
];

$promoColors = ['#FF4757', '#2ED573', '#1E90FF', '#FF6B81', '#FFA502'];

$i = 0;
foreach ($promotionImages as $filename => $label) {
    $path = $basePath . 'promotions/' . $filename;
    if (!file_exists($path)) {
        $color = $promoColors[$i % count($promoColors)];
        createPlaceholderImage($path, $label, 800, 400, $color);
        $i++;
    }
}

// ============================================================
// CATEGORY IMAGES
// ============================================================
echo "\n📁 Generating Category Images...\n";

$categoryImages = [
    'pizza-cat.jpg' => 'Pizza',
    'burgers-cat.jpg' => 'Burgers',
    'chicken-cat.jpg' => 'Chicken',
    'fast-food-cat.jpg' => 'Fast Food',
    'african-food-cat.jpg' => 'African Food',
    'indian-food-cat.jpg' => 'Indian Food',
    'drinks-cat.jpg' => 'Drinks',
    'desserts-cat.jpg' => 'Desserts',
    'rice-dishes-cat.jpg' => 'Rice Dishes',
    'healthy-food-cat.jpg' => 'Healthy Food',
    'category-default.jpg' => 'Category'
];

$catColors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#FF8A5C', '#A29BFE'];

$i = 0;
foreach ($categoryImages as $filename => $label) {
    $path = $basePath . 'categories/' . $filename;
    if (!file_exists($path)) {
        $color = $catColors[$i % count($catColors)];
        createPlaceholderImage($path, $label, 200, 200, $color);
        $i++;
    }
}

// ============================================================
// PROFILE IMAGES
// ============================================================
echo "\n📁 Generating Profile Images...\n";

$profilePath = $basePath . 'profiles/default.png';
if (!file_exists($profilePath)) {
    createPlaceholderPNG($profilePath, 'User');
}

// ============================================================
// HERO IMAGES
// ============================================================
echo "\n📁 Generating Hero Images...\n";

// Hero Background
$heroBgPath = $basePath . 'hero-bg.jpg';
if (!file_exists($heroBgPath)) {
    $image = imagecreatetruecolor(1920, 1080);
    if ($image) {
        $color1 = imagecolorallocate($image, 15, 12, 41);
        $color2 = imagecolorallocate($image, 22, 33, 62);
        imagefill($image, 0, 0, $color1);
        
        $white = imagecolorallocatealpha($image, 255, 255, 255, 8);
        for ($i = 0; $i < 20; $i++) {
            $x = rand(0, 1920);
            $y = rand(0, 1080);
            $radius = rand(50, 300);
            imagefilledellipse($image, $x, $y, $radius, $radius, $white);
        }
        
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $text = 'Quick Bite';
        $fontSize = 8;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $x = (1920 - $textWidth) / 2;
        $y = (1080 - imagefontheight($fontSize)) / 2 - 30;
        imagestring($image, $fontSize, $x, $y, $text, $textColor);
        
        $subText = 'Delicious Food, Delivered Fast';
        $subWidth = imagefontwidth(5) * strlen($subText);
        $subX = (1920 - $subWidth) / 2;
        $subY = $y + 50;
        imagestring($image, 5, $subX, $subY, $subText, imagecolorallocate($image, 200, 200, 200));
        
        imagejpeg($image, $heroBgPath, 90);
        imagedestroy($image);
        echo "Created: assets/images/hero-bg.jpg\n";
    }
}

// Hero Food
$heroFoodPath = $basePath . 'hero-food.jpg';
if (!file_exists($heroFoodPath)) {
    $image = imagecreatetruecolor(550, 350);
    if ($image) {
        $color = imagecolorallocate($image, 48, 43, 99);
        imagefill($image, 0, 0, $color);
        
        $white = imagecolorallocatealpha($image, 255, 255, 255, 15);
        imagefilledellipse($image, 275, 175, 200, 200, $white);
        
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $text = '🍔🍕🍗';
        $fontSize = 8;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $x = (550 - $textWidth) / 2;
        $y = (350 - imagefontheight($fontSize)) / 2;
        imagestring($image, $fontSize, $x, $y, $text, $textColor);
        
        imagejpeg($image, $heroFoodPath, 90);
        imagedestroy($image);
        echo "Created: assets/images/hero-food.jpg\n";
    }
}

// ============================================================
// DEFAULT PROFILE IMAGE
// ============================================================
$defaultProfilePath = $basePath . 'default-profile.png';
if (!file_exists($defaultProfilePath)) {
    if (file_exists($basePath . 'profiles/default.png')) {
        copy($basePath . 'profiles/default.png', $defaultProfilePath);
        echo "Created: assets/images/default-profile.png\n";
    }
}

echo "\n========================================\n";
echo "✅ All placeholder images created successfully!\n";
echo "========================================\n";
echo "📌 Replace these with real images when ready.\n";
echo "📍 Images are in: assets/images/\n";
echo "========================================\n";
?>