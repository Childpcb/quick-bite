<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<div class="pt-24 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-utensils text-3xl text-indigo-600"></i>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">About Quick Bite</h1>
                <p class="text-gray-500 mt-2">Delicious Food, Delivered Fast</p>
            </div>
            
            <div class="max-w-none">
                <p class="text-gray-600 leading-relaxed">
                    Quick Bite is a premier food delivery platform that connects hungry customers with the best restaurants in Tanzania. 
                    Our mission is to make ordering food easy, fast, and enjoyable for everyone.
                </p>
                
                <h2 class="text-xl font-bold text-gray-800 mt-6">Our Story</h2>
                <p class="text-gray-600 leading-relaxed">
                    Founded in 2024, Quick Bite was created to solve the problem of finding quality food delivery in Tanzania. 
                    We saw a need for a reliable, user-friendly platform that brings together the best restaurants and hungry customers.
                </p>
                
                <h2 class="text-xl font-bold text-gray-800 mt-6">Why Choose Quick Bite?</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <i class="fas fa-truck-fast text-2xl text-indigo-600 mb-2"></i>
                        <h3 class="font-bold text-gray-800">Fast Delivery</h3>
                        <p class="text-sm text-gray-500">Hot and fresh food delivered to your doorstep in minutes</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <i class="fas fa-star text-2xl text-indigo-600 mb-2"></i>
                        <h3 class="font-bold text-gray-800">Quality Restaurants</h3>
                        <p class="text-sm text-gray-500">Curated selection of the best restaurants in town</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <i class="fas fa-credit-card text-2xl text-indigo-600 mb-2"></i>
                        <h3 class="font-bold text-gray-800">Easy Payment</h3>
                        <p class="text-sm text-gray-500">Multiple payment options for your convenience</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <i class="fas fa-headset text-2xl text-indigo-600 mb-2"></i>
                        <h3 class="font-bold text-gray-800">24/7 Support</h3>
                        <p class="text-sm text-gray-500">Our team is always ready to help you</p>
                    </div>
                </div>
                
                <h2 class="text-xl font-bold text-gray-800 mt-6">Our Values</h2>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    <li><strong>Quality:</strong> We partner only with restaurants that maintain high standards</li>
                    <li><strong>Reliability:</strong> We deliver on time, every time</li>
                    <li><strong>Customer First:</strong> Your satisfaction is our top priority</li>
                    <li><strong>Innovation:</strong> We continuously improve our platform</li>
                </ul>
                
                <div class="bg-indigo-50 p-6 rounded-lg mt-8 text-center">
                    <h3 class="text-lg font-bold text-gray-800">Ready to Order?</h3>
                    <p class="text-gray-600 mt-2">Join thousands of happy customers who love Quick Bite</p>
                    <a href="<?php echo SITE_URL; ?>menu.php" class="inline-block mt-4 btn-primary text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-utensils mr-2"></i> Explore Menu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>