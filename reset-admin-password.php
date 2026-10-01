<?php
require_once __DIR__ . '/config/database.php';

// New password
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update admin password
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$result = $stmt->execute([$hashedPassword, 'admin@quickbite.com']);

if ($result) {
    echo "✅ Password for admin@quickbite.com has been reset to: <strong>$password</strong><br>";
    echo "<a href='admin/login.php'>Go to Admin Login</a>";
} else {
    echo "❌ Failed to reset password. User not found.";
}

// Show all admins
echo "<br><br><strong>All Admins in System:</strong><br>";
$stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE role != 'customer'");
$admins = $stmt->fetchAll();

if (!empty($admins)) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    foreach ($admins as $admin) {
        echo "<tr>";
        echo "<td>" . $admin['id'] . "</td>";
        echo "<td>" . htmlspecialchars($admin['name']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No admins found.";
}
?>