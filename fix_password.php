<?php
// ============================================================
// QUICK BITE - FIX ADMIN PASSWORD
// ============================================================

// Database connection
$host = 'localhost';
$dbname = 'quick_bite';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create password hash for admin123
    $newPassword = 'admin123';
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'alisonnaftali2003@gmail.com'");
    $stmt->execute([$hash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Password imebadilishwa kikamilifu kwa alisonnaftali2003@gmail.com!<br>";
        echo "🔑 Password mpya: admin123<br>";
    } else {
        echo "⚠️ Hakuna admin aliye na email hiyo. Tunaendelea...<br>";
        
        // Kama haipo, ingiza admin mpya
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'alisonnaftali2003@gmail.com'");
        $stmt->execute();
        $existingUser = $stmt->fetch();
        
        if (!$existingUser) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
            $stmt->execute(['Admin', 'alisonnaftali2003@gmail.com', '+255754341171', $hash]);
            echo "✅ Admin mpya ameingizwa!<br>";
        } else {
            // Badilisha role kuwa admin
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin', password = ? WHERE email = ?");
            $stmt->execute([$hash, 'alisonnaftali2003@gmail.com']);
            echo "✅ Admin amerekebishwa!<br>";
        }
    }
    
    echo "<br>🔗 <a href='login.php'>Bofya hapa kuingia</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>