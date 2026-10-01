<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle address operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $region = trim($_POST['region'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $additional = trim($_POST['additional_info'] ?? '');
            $latitude = trim($_POST['latitude'] ?? '');
            $longitude = trim($_POST['longitude'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            
            if (empty($address) || empty($city) || empty($region)) {
                $error = 'Please fill in required fields.';
            } else {
                if ($isDefault) {
                    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
                    $stmt->execute([$userId]);
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO addresses (user_id, address, city, region, phone, additional_info, latitude, longitude, is_default)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([$userId, $address, $city, $region, $phone, $additional, $latitude, $longitude, $isDefault])) {
                    $success = 'Address added successfully!';
                } else {
                    $error = 'Failed to add address.';
                }
            }
        } elseif ($_POST['action'] === 'edit') {
            $addressId = (int)$_POST['address_id'];
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $region = trim($_POST['region'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $additional = trim($_POST['additional_info'] ?? '');
            $latitude = trim($_POST['latitude'] ?? '');
            $longitude = trim($_POST['longitude'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            
            if (empty($address) || empty($city) || empty($region)) {
                $error = 'Please fill in required fields.';
            } else {
                if ($isDefault) {
                    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
                    $stmt->execute([$userId]);
                }
                
                $stmt = $pdo->prepare("
                    UPDATE addresses 
                    SET address = ?, city = ?, region = ?, phone = ?, additional_info = ?, 
                        latitude = ?, longitude = ?, is_default = ?
                    WHERE id = ? AND user_id = ?
                ");
                if ($stmt->execute([$address, $city, $region, $phone, $additional, $latitude, $longitude, $isDefault, $addressId, $userId])) {
                    $success = 'Address updated successfully!';
                } else {
                    $error = 'Failed to update address.';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $addressId = (int)$_POST['address_id'];
            $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$addressId, $userId])) {
                $success = 'Address deleted successfully!';
            } else {
                $error = 'Failed to delete address.';
            }
        } elseif ($_POST['action'] === 'set_default') {
            $addressId = (int)$_POST['address_id'];
            
            $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            $stmt = $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$addressId, $userId])) {
                $success = 'Default address updated!';
            } else {
                $error = 'Failed to update default address.';
            }
        }
    }
}

// Get user addresses
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll();

// Get regions and cities for dropdown
$regions = ['Dar es Salaam', 'Arusha', 'Mwanza', 'Dodoma', 'Mbeya', 'Morogoro', 'Tanga', 'Kilimanjaro', 'Zanzibar'];
$cities = [
    'Dar es Salaam' => ['Dar es Salaam', 'Kinondoni', 'Ilala', 'Temeke', 'Ubungo', 'Kigamboni'],
    'Arusha' => ['Arusha', 'Meru'],
    'Mwanza' => ['Mwanza', 'Nyamagana', 'Ilemela'],
    'Dodoma' => ['Dodoma'],
    'Mbeya' => ['Mbeya'],
    'Morogoro' => ['Morogoro'],
    'Tanga' => ['Tanga'],
    'Kilimanjaro' => ['Moshi'],
    'Zanzibar' => ['Zanzibar City', 'Mkokotoni']
];

// Default location (Dar es Salaam)
$defaultLat = -6.792354;
$defaultLng = 39.208328;
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ===== Leaflet CSS (Map Library) ===== -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 16px;
        z-index: 1;
    }
    .map-container {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .map-container .map-overlay {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 13px;
        z-index: 10;
        pointer-events: none;
    }
    .location-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .leaflet-control-attribution {
        font-size: 10px !important;
    }
</style>

<div class="pt-24 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- ===== HEADER ===== -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <i class="fas fa-map-marker-alt text-2xl text-indigo-600"></i>
                <h1 class="text-2xl font-bold text-gray-800">Delivery Locations</h1>
                <span class="text-sm text-gray-500">(<?php echo count($addresses); ?> addresses)</span>
            </div>
            <button onclick="getCurrentLocation()" 
                    class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg font-medium hover:bg-indigo-200 transition flex items-center gap-2">
                <i class="fas fa-location-crosshairs"></i> Use My Location
            </button>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            
            <!-- ===== MAP SECTION (Colspan 3) ===== -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl shadow-md p-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-3">
                        <i class="fas fa-map text-indigo-600 mr-2"></i> Select Location on Map
                    </h2>
                    <div class="map-container">
                        <div id="map"></div>
                        <div class="map-overlay">
                            <i class="fas fa-hand-pointer mr-2"></i> Click on map to select location
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        <i class="fas fa-info-circle"></i> Click anywhere on the map to set your delivery location
                    </p>
                    
                    <!-- Selected Location Display -->
                    <div id="selected-location" class="mt-3 p-3 bg-gray-50 rounded-lg hidden">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-map-pin text-indigo-600 mr-1"></i>
                            <span id="selected-coords">Lat: -6.792354, Lng: 39.208328</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- ===== ADD ADDRESS FORM (Colspan 2) ===== -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-plus-circle text-indigo-600 mr-2"></i> Add New Address
                    </h2>
                    <form method="POST" action="" id="addressForm">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="latitude" id="latitude" value="">
                        <input type="hidden" name="longitude" id="longitude" value="">
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                            <input type="text" name="address" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="e.g., Plot 14, Kinondoni Road">
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Region *</label>
                            <select name="region" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    id="region-select" onchange="updateCities(this.value)">
                                <option value="">Select Region</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?php echo $region; ?>"><?php echo $region; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                            <select name="city" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    id="city-select">
                                <option value="">Select City</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="phone" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="e.g., 0712345678">
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Info</label>
                            <input type="text" name="additional_info" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="e.g., Near the market, Gate number 5">
                        </div>
                        
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_default" value="1" 
                                       <?php echo empty($addresses) ? 'checked' : ''; ?> class="mr-2">
                                Set as default address
                            </label>
                        </div>
                        
                        <button type="submit" class="w-full btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                            <i class="fas fa-save mr-2"></i> Save Address
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- ===== ADDRESS LIST ===== -->
        <div class="mt-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-list text-indigo-600 mr-2"></i> Saved Addresses
                </h2>
                
                <?php if (!empty($addresses)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($addresses as $address): ?>
                            <div class="location-card bg-gray-50 rounded-xl p-4 transition border border-transparent hover:border-indigo-200">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($address['address']); ?></h3>
                                            <?php if ($address['is_default']): ?>
                                                <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">
                                                    <i class="fas fa-check-circle mr-1"></i> Default
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($address['city'] . ', ' . $address['region']); ?>
                                        </p>
                                        <?php if ($address['phone']): ?>
                                            <p class="text-xs text-gray-500">
                                                <i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($address['phone']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($address['latitude'] && $address['longitude']): ?>
                                            <p class="text-xs text-gray-400">
                                                <i class="fas fa-map-pin mr-1"></i> 
                                                <?php echo number_format($address['latitude'], 6); ?>, <?php echo number_format($address['longitude'], 6); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex flex-col gap-1 ml-4">
                                        <?php if (!$address['is_default']): ?>
                                            <form method="POST" action="" class="inline">
                                                <input type="hidden" name="action" value="set_default">
                                                <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                                <button type="submit" class="text-green-600 hover:text-green-700 text-xs font-medium">
                                                    <i class="fas fa-check-circle mr-1"></i> Default
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="" class="inline" onsubmit="return confirm('Delete this address?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="text-5xl mb-3">📍</div>
                        <h3 class="text-lg font-bold text-gray-800">No Addresses Saved</h3>
                        <p class="text-gray-500 text-sm mt-1">Add your delivery addresses to make checkout faster.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// LEAFET MAP - INTERACTIVE MAP
// ============================================================

// Default location (Dar es Salaam)
const defaultLat = <?php echo $defaultLat; ?>;
const defaultLng = <?php echo $defaultLng; ?>;

// Initialize map
const map = L.map('map').setView([defaultLat, defaultLng], 13);

// Load tile layer (OpenStreetMap)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Custom marker icon
const customIcon = L.icon({
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

// Add marker on click
let marker = null;

map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;
    
    // Update hidden fields
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    
    // Show selected location
    document.getElementById('selected-location').classList.remove('hidden');
    document.getElementById('selected-coords').textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
    
    // Add or move marker
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
    }
    
    // Show popup
    marker.bindPopup('📍 Selected Location').openPopup();
});

// ============================================================
// GET CURRENT LOCATION
// ============================================================
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Update map
                map.setView([lat, lng], 15);
                
                // Update hidden fields
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                // Update display
                document.getElementById('selected-location').classList.remove('hidden');
                document.getElementById('selected-coords').textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                
                // Add marker
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
                }
                marker.bindPopup('📍 Your Current Location').openPopup();
                
                // Reverse geocode (optional)
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            const addressInput = document.querySelector('input[name="address"]');
                            if (addressInput && !addressInput.value) {
                                addressInput.value = data.display_name;
                            }
                        }
                    })
                    .catch(err => console.log('Geocoding error:', err));
                
                alert('✅ Location detected successfully!');
            },
            function(error) {
                alert('❌ Unable to get your location. Please select on map manually.\n\nError: ' + error.message);
            }
        );
    } else {
        alert('❌ Geolocation is not supported by your browser. Please select on map manually.');
    }
}

// ============================================================
// CITY DROPDOWN
// ============================================================
function updateCities(region) {
    const cities = <?php echo json_encode($cities); ?>;
    const citySelect = document.getElementById('city-select');
    citySelect.innerHTML = '<option value="">Select City</option>';
    
    if (region && cities[region]) {
        cities[region].forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    }
}

// ============================================================
// AUTO DETECT CITY FROM REGION ON MAP CLICK
// ============================================================
// Set default map view with marker
setTimeout(() => {
    // Add initial marker at default location
    marker = L.marker([defaultLat, defaultLng], { icon: customIcon }).addTo(map);
    marker.bindPopup('📍 Dar es Salaam').openPopup();
    
    // Set default coordinates
    document.getElementById('latitude').value = defaultLat;
    document.getElementById('longitude').value = defaultLng;
    document.getElementById('selected-location').classList.remove('hidden');
    document.getElementById('selected-coords').textContent = `Lat: ${defaultLat.toFixed(6)}, Lng: ${defaultLng.toFixed(6)}`;
}, 500);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>