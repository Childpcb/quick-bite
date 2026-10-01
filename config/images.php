<?php
// ============================================================
// QUICK BITE - IMAGE GENERATOR
// Run this script ONCE to generate all placeholder images
// ============================================================

// Define upload path
$basePath = __DIR__ . '/../uploads/';

// Create all directories
$directories = [
    $basePath . 'foods/',
    $basePath . 'restaurants/',
    $basePath . 'promotions/',
    $basePath . 'profiles/'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "✅ Created directory: " . str_replace(__DIR__ . '/', '', $dir) . "<br>";
    }
}

// Function to create placeholder image
function createPlaceholderImage($path, $text, $width = 400, $height = 300, $bgColor = '#6C63FF') {
    $image = imagecreatetruecolor($width, $height);
    
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
    
    imagejpeg($image, $path, 90);
    imagedestroy($image);
    
    echo "✅ Created: " . str_replace(__DIR__ . '/', '', $path) . "<br>";
}

// Generate default images
$foodImages = [
    'food-default.jpg' => 'Food',
    'chicken-burger.jpg' => 'Chicken Burger',
    'beef-burger.jpg' => 'Beef Burger',
    'french-fries.jpg' => 'French Fries',
    'margherita.jpg' => 'Margherita',
];

$restaurantImages = [
    'restaurant-default.jpg' => 'Restaurant',
    'quick-grill.jpg' => 'Quick Grill',
    'pizza-palace.jpg' => 'Pizza Palace',
];

$promotionImages = [
    'promotion-default.jpg' => 'Promotion',
    'weekend-special.jpg' => 'Weekend Special',
];

// Generate food images
foreach ($foodImages as $filename => $label) {
    $path = $basePath . 'foods/' . $filename;
    if (!file_exists($path)) {
        createPlaceholderImage($path, $label, 400, 300, '#FF6B6B');
    }
}

// Generate restaurant images
foreach ($restaurantImages as $filename => $label) {
    $path = $basePath . 'restaurants/' . $filename;
    if (!file_exists($path)) {
        createPlaceholderImage($path, $label, 600, 400, '#6C63FF');
    }
}

// Generate promotion images
foreach ($promotionImages as $filename => $label) {
    $path = $basePath . 'promotions/' . $filename;
    if (!file_exists($path)) {
        createPlaceholderImage($path, $label, 800, 400, '#FF4757');
    }
}

// Create default profile image (PNG)
$profilePath = $basePath . 'profiles/default.png';
if (!file_exists($profilePath)) {
    $image = imagecreatetruecolor(200, 200);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    $purple = imagecolorallocate($image, 108, 99, 255);
    imagefilledellipse($image, 100, 100, 190, 190, $purple);
    
    $white = imagecolorallocate($image, 255, 255, 255);
    imagestring($image, 5, 78, 95, 'User', $white);
    
    imagepng($image, $profilePath);
    imagedestroy($image);
    echo "✅ Created: uploads/profiles/default.png<br>";
}

echo "<br>🎉 All placeholder images created successfully!<br>";
echo "You can now delete or rename this file.<br>";
?>