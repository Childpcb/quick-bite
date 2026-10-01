<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h1>🔔 Notification Test</h1>";
echo "<hr>";

// Create a test notification
$title = "✅ TEST NOTIFICATION";
$message = "This is a test notification from your Quick Bite system at " . date('Y-m-d H:i:s');

$result = createAdminNotification($pdo, $title, $message);

if ($result) {
    echo "<p style='color: green; font-size: 18px;'><strong>✅ SUCCESS!</strong></p>";
    echo "<p>Notification created successfully in database!</p>";
} else {
    echo "<p style='color: red;'><strong>❌ FAILED</strong></p>";
    echo "<p>Could not create notification.</p>";
}

echo "<hr>";

// Show all notifications
echo "<h2>📋 All Notifications</h2>";
$notifications = getAdminNotifications($pdo, 10);

if (!empty($notifications)) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Title</th><th>Message</th><th>Read</th><th>Time</th></tr>";
    foreach ($notifications as $notif) {
        echo "<tr>";
        echo "<td>" . $notif['id'] . "</td>";
        echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
        echo "<td>" . htmlspecialchars($notif['message']) . "</td>";
        echo "<td>" . ($notif['is_read'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', strtotime($notif['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total:</strong> " . count($notifications) . " notifications</p>";
} else {
    echo "<p>No notifications found.</p>";
}

echo "<hr>";
echo "<p><a href='admin/index.php'>🔙 Go to Admin Dashboard</a></p>";
?>