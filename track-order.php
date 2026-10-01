<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin(); // Hakikisha mteja ameingia

$userId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: orders.php'); // Kama hana order ID, mpeleke kwenye orders zake
    exit();
}

// Chukua taarifa za order
$stmt = $pdo->prepare("
    SELECT o.*, r.name as restaurant_name 
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    // Kama order haipo au si ya mteja huyu
    header('Location: orders.php');
    exit();
}

// Chukua items za order
$stmt = $pdo->prepare("
    SELECT oi.*, f.name as food_name 
    FROM order_items oi
    JOIN food_items f ON oi.food_id = f.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();

// Chukua notification za hii order maalum (kama zipo)
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND message LIKE ? 
    ORDER BY created_at DESC LIMIT 5
");
$stmt->execute([$userId, '%' . $order['order_number'] . '%']);
$notifications = $stmt->fetchAll();

// Function ya kuonyesha badge ya status
function getStatusBadgeCustomer($status) {
    $badges = [
        'pending' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">⏳ Inasubiri</span>',
        'confirmed' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">✅ Imethibitishwa</span>',
        'preparing' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">👨‍🍳 Inapikwa</span>',
        'out_for_delivery' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 animate-pulse">🚚 Inasafirishwa</span>',
        'delivered' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">🎉 Imefika</span>',
        'cancelled' => '<span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">❌ Imesitishwa</span>',
    ];
    return $badges[$status] ?? '<span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">'.ucfirst($status).'</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuatilia Order - Quick Bite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .pulse-ring {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navbar (Unaweza kutumia yako ya kawaida hapa) -->
    <nav class="bg-white shadow-md p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold text-indigo-600">Quick Bite</a>
            <a href="orders.php" class="text-gray-600 hover:text-indigo-600">Orders Zangu</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        
        <div class="flex items-center gap-4 mb-6">
            <a href="orders.php" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Fuatilia Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
        </div>

        <!-- Status ya Sasa -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 border-l-4 border-indigo-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Hali ya Order</p>
                    <div id="current-status">
                        <?php echo getStatusBadgeCustomer($order['order_status']); ?>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 mb-1">Jumla</p>
                    <p class="text-xl font-bold text-indigo-600"><?php echo formatPrice($order['total']); ?></p>
                </div>
            </div>
        </div>

        <!-- Progress Bar (Hatua) -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Hatua za Order</h2>
            <div class="flex items-center justify-between relative">
                <?php 
                $steps = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'];
                $stepLabels = ['Imepokelewa', 'Imethibitishwa', 'Inapikwa', 'Inasafirishwa', 'Imefika'];
                $currentIndex = array_search($order['order_status'], $steps);
                if ($currentIndex === false) $currentIndex = 0;
                ?>
                
                <?php foreach ($steps as $index => $step): ?>
                    <div class="flex flex-col items-center z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold 
                            <?php echo $index <= $currentIndex ? 'bg-indigo-600' : 'bg-gray-300'; ?>">
                            <?php if ($index < $currentIndex): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                <?php echo $index + 1; ?>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs mt-2 text-center <?php echo $index <= $currentIndex ? 'text-indigo-600 font-semibold' : 'text-gray-400'; ?>">
                            <?php echo $stepLabels[$index]; ?>
                        </span>
                    </div>
                    <?php if ($index < count($steps) - 1): ?>
                        <div class="flex-1 h-1 mx-2 <?php echo $index < $currentIndex ? 'bg-indigo-600' : 'bg-gray-300'; ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Orodha ya Chakula -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Chakula Ulichoagiza</h2>
            <div class="space-y-3">
                <?php foreach ($orderItems as $item): ?>
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <div>
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($item['food_name']); ?></p>
                            <p class="text-sm text-gray-500">Idadi: <?php echo $item['quantity']; ?></p>
                        </div>
                        <p class="font-semibold text-gray-700"><?php echo formatPrice($item['subtotal']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Taarifa za Nyongeza -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Taarifa za Ziada</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Mkahawa</p>
                    <p class="font-medium"><?php echo htmlspecialchars($order['restaurant_name']); ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Muda wa Kuagiza</p>
                    <p class="font-medium"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Njia ya Malipo</p>
                    <p class="font-medium"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- JAVASCRIPT YA AUTO-REFRESH (Inafanya kazi kila sekunde 10) -->
    <!-- ============================================================ -->
    <script>
        // Angalia status kila sekunde 10 (10000ms)
        setInterval(function() {
            fetch('check-order-status.php?id=<?php echo $orderId; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Kama status imebadilika, update UI
                        const statusDiv = document.getElementById('current-status');
                        if (statusDiv && data.html) {
                            statusDiv.innerHTML = data.html;
                            
                            // Kama status ni out_for_delivery, ongeza effect ya pulse
                            if (data.status === 'out_for_delivery') {
                                statusDiv.classList.add('animate-pulse');
                            } else {
                                statusDiv.classList.remove('animate-pulse');
                            }
                        }
                        
                        // Kama kuna ujumbe mpya, unaweza kuonyesha alert (hiari)
                        if (data.new_notification) {
                            // Unaweza kucheza sauti au kuonyesha popup
                            console.log('New notification:', data.new_notification);
                        }
                    }
                })
                .catch(error => console.error('Error checking status:', error));
        }, 10000); // 10000ms = sekunde 10
    </script>

</body>
</html>