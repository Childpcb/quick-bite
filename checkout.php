<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = $_SESSION['user_id'];
$cartItems = getCartItems($pdo, $userId);

if (empty($cartItems)) {
    header('Location: ' . SITE_URL . 'cart.php');
    exit();
}

// Get user's addresses
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll();

// Calculate totals
$subtotal = getCartTotal($pdo, $userId);
$deliveryFee = 3000;
$discount = 0;
$discountCode = '';
$grandTotal = $subtotal + $deliveryFee - $discount;

$error = '';
$success = '';

// Handle promo code
if (isset($_POST['apply_promo'])) {
    $code = trim($_POST['promo_code'] ?? '');
    
    if (!empty($code)) {
        $stmt = $pdo->prepare("
            SELECT * FROM promotions 
            WHERE promo_code = ? 
            AND status = 'active' 
            AND start_date <= NOW() 
            AND end_date >= NOW()
        ");
        $stmt->execute([$code]);
        $promo = $stmt->fetch();
        
        if ($promo) {
            if ($promo['discount_type'] === 'percentage') {
                $discount = ($subtotal * $promo['discount_value']) / 100;
            } elseif ($promo['discount_type'] === 'fixed') {
                $discount = min($promo['discount_value'], $subtotal);
            } elseif ($promo['discount_type'] === 'free_delivery') {
                $discount = $deliveryFee;
            }
            
            $discountCode = $code;
            $grandTotal = $subtotal + $deliveryFee - $discount;
            $success = '✅ Promo code applied! You saved TZS ' . number_format($discount, 0);
        } else {
            $error = '❌ Invalid or expired promo code.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Quick Bite</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1a1a2e; }
        
        .checkout-container {
            max-width: 1100px;
            margin: 100px auto 60px;
            padding: 0 24px;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }
        
        .checkout-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.04);
            margin-bottom: 20px;
        }
        
        .checkout-card h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkout-card h2 i { color: #6C63FF; }
        
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; color: #374151; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
            background: #f9fafb;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #6C63FF;
            background: white;
            box-shadow: 0 0 0 4px rgba(108,99,255,0.06);
        }
        
        .address-option {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .address-option:hover { border-color: #c7d2fe; background: #fafbff; }
        .address-option.selected { border-color: #6C63FF; background: #f5f3ff; }
        
        .payment-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-option:hover { border-color: #c7d2fe; background: #fafbff; }
        .payment-option.selected { border-color: #6C63FF; background: #f5f3ff; }
        
        .order-summary {
            position: sticky;
            top: 100px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: 800;
            color: #6C63FF;
            padding-top: 14px;
            border-top: 2px solid #f0f2f5;
            margin-top: 14px;
        }
        
        .promo-input-group {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .promo-input-group input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
        }
        
        .promo-input-group input:focus {
            border-color: #6C63FF;
            box-shadow: 0 0 0 4px rgba(108,99,255,0.06);
        }
        
        .btn-apply {
            background: #6C63FF;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 13px;
        }
        
        .btn-apply:hover { background: #5A52D5; }
        
        .btn-place-order {
            width: 100%;
            background: linear-gradient(135deg, #6C63FF, #5A52D5);
            color: white;
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
            margin-top: 16px;
        }
        
        .btn-place-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108,99,255,0.3);
        }
        
        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        @media (max-width: 900px) {
            .checkout-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<div class="checkout-container">
    <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 30px;">
        <i class="fas fa-shopping-bag" style="color: #6C63FF;"></i> Checkout
    </h1>
    
    <form method="POST" action="place-order.php">
        <div class="checkout-grid">
            
            <!-- ===== LEFT: ORDER FORM ===== -->
            <div>
                <!-- Delivery Address -->
                <div class="checkout-card">
                    <h2><i class="fas fa-map-marker-alt"></i> Delivery Address</h2>
                    
                    <?php if (!empty($addresses)): ?>
                        <?php foreach ($addresses as $addr): ?>
                            <label class="address-option <?php echo $addr['is_default'] ? 'selected' : ''; ?>">
                                <input type="radio" name="address_id" value="<?php echo $addr['id']; ?>" 
                                       <?php echo $addr['is_default'] ? 'checked' : ''; ?> required>
                                <div>
                                    <p style="font-weight: 600; color: #1a1a2e;">
                                        <?php echo htmlspecialchars($addr['address']); ?>
                                        <?php if ($addr['is_default']): ?>
                                            <span style="font-size: 11px; background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 20px; margin-left: 8px;">Default</span>
                                        <?php endif; ?>
                                    </p>
                                    <p style="font-size: 13px; color: #6b7280;">
                                        <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['region']); ?>
                                    </p>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #dc2626; font-size: 14px; margin-bottom: 10px;">
                            No addresses found. Please add one.
                        </p>
                        <a href="profile.php" style="color: #6C63FF; font-weight: 600;">+ Add Address</a>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Method -->
                <div class="checkout-card">
                    <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                    
                    <label class="payment-option selected">
                        <input type="radio" name="payment_method" value="cash" checked required>
                        <div>
                            <p style="font-weight: 600;">💵 Cash on Delivery</p>
                            <p style="font-size: 13px; color: #6b7280;">Pay when you receive your order</p>
                        </div>
                    </label>
                    
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="mobile_money" required>
                        <div>
                            <p style="font-weight: 600;">📱 Mobile Money</p>
                            <p style="font-size: 13px; color: #6b7280;">M-Pesa, Tigo Pesa, Airtel Money</p>
                        </div>
                    </label>
                    
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="card" required>
                        <div>
                            <p style="font-weight: 600;">💳 Card Payment</p>
                            <p style="font-size: 13px; color: #6b7280;">Visa, Mastercard</p>
                        </div>
                    </label>
                </div>
                
                <!-- Order Notes -->
                <div class="checkout-card">
                    <h2><i class="fas fa-comment"></i> Order Notes (Optional)</h2>
                    <textarea name="notes" rows="3" 
                              placeholder="Any special instructions..."
                              style="width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; outline: none; resize: none;"></textarea>
                </div>
            </div>
            
            <!-- ===== RIGHT: ORDER SUMMARY ===== -->
            <div>
                <div class="checkout-card order-summary">
                    <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                    
                    <!-- Cart Items -->
                    <div style="max-height: 200px; overflow-y: auto; margin-bottom: 16px;">
                        <?php foreach ($cartItems as $item): ?>
                            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px;">
                                <span style="color: #6b7280;">
                                    <?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?>
                                </span>
                                <span style="font-weight: 600;">
                                    <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="border-top: 1px solid #f0f2f5; padding-top: 14px;">
                        <!-- Subtotal -->
                        <div class="summary-item">
                            <span style="color: #6b7280;">Subtotal</span>
                            <span style="font-weight: 600;"><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <!-- Delivery -->
                        <div class="summary-item">
                            <span style="color: #6b7280;">Delivery Fee</span>
                            <span style="font-weight: 600;"><?php echo formatPrice($deliveryFee); ?></span>
                        </div>
                        
                        <!-- Discount (if applied) -->
                        <?php if ($discount > 0): ?>
                            <div class="summary-item" style="color: #10b981;">
                                <span>Discount</span>
                                <span style="font-weight: 600;">-<?php echo formatPrice($discount); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Total -->
                        <div class="summary-total">
                            <span>Total</span>
                            <span><?php echo formatPrice($grandTotal); ?></span>
                        </div>
                    </div>
                    
                    <!-- ===== PROMO CODE SECTION ===== -->
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f2f5;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">
                            <i class="fas fa-tag" style="color: #6C63FF;"></i> Have a promo code?
                        </label>
                        
                        <?php if ($error): ?>
                            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <div class="promo-input-group">
                            <input type="text" name="promo_code" id="promoCode" 
                                   placeholder="Enter code" 
                                   value="<?php echo htmlspecialchars($discountCode); ?>">
                            <button type="submit" name="apply_promo" class="btn-apply">
                                Apply
                            </button>
                        </div>
                        
                        <p style="font-size: 12px; color: #9ca3af;">
                            <i class="fas fa-info-circle"></i> 
                            Paste code (Ctrl + V) and click Apply
                        </p>
                    </div>
                    
                    <!-- Hidden fields -->
                    <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                    <input type="hidden" name="delivery_fee" value="<?php echo $deliveryFee; ?>">
                    <input type="hidden" name="discount" value="<?php echo $discount; ?>">
                    <input type="hidden" name="total" value="<?php echo $grandTotal; ?>">
                    <input type="hidden" name="promo_code_applied" value="<?php echo htmlspecialchars($discountCode); ?>">
                    
                    <!-- Place Order Button -->
                    <button type="submit" name="place_order" class="btn-place-order" 
                            <?php echo empty($addresses) ? 'disabled' : ''; ?>>
                        <i class="fas fa-check-circle"></i> Place Order
                    </button>
                    
                    <?php if (empty($addresses)): ?>
                        <p style="font-size: 12px; color: #dc2626; text-align: center; margin-top: 8px;">
                            Please add a delivery address first
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Add visual feedback when address/payment is selected
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove selected class from siblings
        const name = this.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            r.closest('label').classList.remove('selected');
        });
        // Add selected class to parent
        this.closest('label').classList.add('selected');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>