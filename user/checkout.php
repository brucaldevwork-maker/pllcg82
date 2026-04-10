<?php
// File: C:\xampp\htdocs\pllcg82\user\checkout.php

require_once '../config/config.php';

session_start();

// Check if user is logged in
$user_name = "User";
$user_id = null;
$user_email = "";
$user_phone = "";
$user_fullname = "";
$user_address = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT username, full_name, email, phone, address FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        if ($user_data) {
            $user_name = $user_data['full_name'] ?: $user_data['username'];
            $user_fullname = $user_data['full_name'] ?: $user_data['username'];
            $user_email = $user_data['email'] ?? '';
            $user_phone = $user_data['phone'] ?? '';
            $user_address = $user_data['address'] ?? '';
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
}

// Get cart items
$cart_items = [];
$subtotal = 0;
$session_id = session_id();

if ($user_id) {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.* 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.* 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.session_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$session_id]);
    $cart_items = $stmt->fetchAll();
}

// Calculate subtotal
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Calculate shipping (free for orders over ₱10,000)
$shipping = 0;
if ($subtotal > 0 && $subtotal < 10000) {
    $shipping = 150;
}
$total = $subtotal + $shipping;

// Redirect if cart is empty
if (count($cart_items) == 0) {
    header("Location: cart.php");
    exit;
}

// Get cart count
$cart_count = count($cart_items);

// Handle order submission
$order_placed = false;
$order_number = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $delivery_type = $_POST['delivery_type'] ?? 'delivery';
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $pickup_branch = trim($_POST['pickup_branch'] ?? '');
    
    // Validation
    $errors = [];
    
    if ($delivery_type == 'delivery') {
        if (empty($first_name)) $errors[] = "First name is required";
        if (empty($last_name)) $errors[] = "Last name is required";
        if (empty($email)) $errors[] = "Email address is required";
        if (empty($phone)) $errors[] = "Phone number is required";
        if (empty($address)) $errors[] = "Address is required";
        if (empty($city)) $errors[] = "City is required";
        if (empty($postal_code)) $errors[] = "Postal code is required";
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }
        
        $shipping_address = "$address, $city, $postal_code";
    } else {
        if (empty($pickup_branch)) $errors[] = "Please select a pickup branch";
        $shipping_address = "Store Pickup - $pickup_branch";
    }
    
    if (empty($errors)) {
        try {
            // Generate unique order number
            $order_number = 'PLLC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Insert order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method, created_at) 
                VALUES (?, ?, ?, 'pending', ?, ?, NOW())
            ");
            $stmt->execute([$user_id, $order_number, $total, $shipping_address, $payment_method]);
            $order_id = $pdo->lastInsertId();
            
            // Insert order items
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            foreach ($cart_items as $item) {
                $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $update_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $update_stock->execute([$item['quantity'], $item['id']]);
            }
            
            // Clear cart
            if ($user_id) {
                $clear_cart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $clear_cart->execute([$user_id]);
            } else {
                $clear_cart = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
                $clear_cart->execute([$session_id]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            $order_placed = true;
            
            // Store order details in session for confirmation page
            $_SESSION['last_order'] = [
                'order_number' => $order_number,
                'total' => $total,
                'payment_method' => $payment_method,
                'delivery_type' => $delivery_type
            ];
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            error_log("Checkout error: " . $e->getMessage());
            $error_message = "Unable to process your order. Please try again.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Include navbar
include_once 'components/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, follow">
    
    <title>Checkout | PLLC Enterprise</title>
    <meta name="description" content="Complete your order securely. Choose delivery or pickup, select payment method, and review your order.">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #0A3D62;
            --accent-blue: #00A8E8;
            --dark-blue: #062c48;
            --text-dark: #1A2C3E;
            --text-light: #6C757D;
            --bg-light: #F8F9FA;
            --white: #FFFFFF;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
        }

        .main-content {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .checkout-container {
            background: var(--white);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(10, 61, 98, 0.08);
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eef2f6;
        }

        .page-title {
            font-size: 2rem;
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .page-title i {
            color: var(--accent-blue);
            margin-right: 10px;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }

        /* Checkout Form */
        .checkout-form {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .checkout-section {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 25px;
        }

        .checkout-section h2 {
            color: var(--primary-blue);
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eef2f6;
        }

        /* Delivery Options */
        .delivery-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .delivery-option {
            display: flex;
            cursor: pointer;
            padding: 15px;
            border: 2px solid #e0e4e8;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .delivery-option:hover {
            border-color: var(--accent-blue);
        }

        .delivery-option input[type="radio"] {
            margin-right: 12px;
            accent-color: var(--accent-blue);
        }

        .option-content h3 {
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .option-content p {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .delivery-fee {
            font-size: 12px;
            font-weight: 600;
            color: var(--accent-blue);
        }

        /* Form Styles */
        .address-section, .pickup-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e4e8;
        }

        .address-section h3, .pickup-section h3 {
            color: var(--primary-blue);
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 5px;
            font-size: 13px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e0e4e8;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 168, 232, 0.1);
        }

        /* Payment Options */
        .payment-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-option {
            display: flex;
            cursor: pointer;
            padding: 15px;
            border: 2px solid #e0e4e8;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .payment-option:hover {
            border-color: var(--accent-blue);
        }

        .payment-option input[type="radio"] {
            margin-right: 12px;
            accent-color: var(--accent-blue);
        }

        .payment-note {
            font-size: 11px;
            color: var(--text-light);
            display: block;
            margin-top: 5px;
        }

        .payment-details {
            margin-top: 20px;
            padding: 20px;
            background: rgba(0, 168, 232, 0.05);
            border-radius: 12px;
        }

        .payment-details h3 {
            color: var(--primary-blue);
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .payment-instructions p {
            margin-bottom: 8px;
            font-size: 13px;
        }

        /* Order Items */
        #orderItems {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: var(--white);
            border-radius: 12px;
        }

        .order-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .order-item-price {
            color: var(--primary-blue);
            font-weight: 500;
        }

        .order-item-quantity {
            color: var(--text-light);
            font-size: 12px;
        }

        /* Order Summary */
        .order-summary {
            position: sticky;
            top: 100px;
        }

        .summary-card {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 25px;
        }

        .summary-card h3 {
            color: var(--primary-blue);
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eef2f6;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }

        .summary-line.total {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-blue);
        }

        .summary-card hr {
            margin: 10px 0;
            border: none;
            border-top: 1px solid #e0e4e8;
        }

        .place-order-btn {
            width: 100%;
            padding: 14px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .place-order-btn:hover {
            background: #0080c0;
            transform: translateY(-2px);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Order Confirmation */
        .order-confirmation {
            text-align: center;
            padding: 40px;
        }

        .order-confirmation i {
            font-size: 80px;
            color: var(--success);
            margin-bottom: 20px;
        }

        .order-confirmation h2 {
            color: var(--primary-blue);
            margin-bottom: 10px;
        }

        .order-confirmation .order-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent-blue);
            margin: 15px 0;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0 15px;
                margin: 20px auto;
            }
            .checkout-container {
                padding: 20px;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .delivery-options,
            .payment-options {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar is included from components/navbar.php -->

    <!-- Main Content -->
    <main class="main-content">
        <div class="checkout-container">
            <?php if ($order_placed): ?>
                <!-- Order Confirmation -->
                <div class="order-confirmation">
                    <i class="fas fa-check-circle"></i>
                    <h2>Order Placed Successfully!</h2>
                    <p>Thank you for your order. We'll send you a confirmation email shortly.</p>
                    <div class="order-number">
                        Order #<?php echo htmlspecialchars($order_number); ?>
                    </div>
                    <div class="confirmation-details">
                        <p><strong>Total Amount:</strong> ₱<?php echo number_format($total, 2); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $_POST['payment_method'] ?? 'COD')); ?></p>
                        <p><strong>Delivery Type:</strong> <?php echo ucfirst($_POST['delivery_type'] ?? 'Delivery'); ?></p>
                    </div>
                    <div style="margin-top: 30px;">
                        <a href="transaction-history.php" class="place-order-btn" style="display: inline-block; width: auto; padding: 12px 30px; text-decoration: none;">
                            <i class="fas fa-history"></i> View Order History
                        </a>
                        <a href="products.php" class="place-order-btn" style="display: inline-block; width: auto; padding: 12px 30px; text-decoration: none; background: var(--primary-blue); margin-left: 10px;">
                            <i class="fas fa-shopping-bag"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
            
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-credit-card"></i> Checkout</h1>
                <p class="page-subtitle">Complete your order securely</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="checkoutForm">
                <div class="checkout-content">
                    <div class="checkout-form">
                        <!-- Delivery Options -->
                        <section class="checkout-section">
                            <h2><i class="fas fa-truck"></i> Delivery Options</h2>
                            <div class="delivery-options">
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_type" value="delivery" checked onchange="toggleDeliveryOptions()">
                                    <div class="option-content">
                                        <h3>🚚 Home Delivery</h3>
                                        <p>Deliver to your address</p>
                                        <span class="delivery-fee">Delivery Fee: ₱150</span>
                                        <span class="delivery-fee" style="display: block; color: var(--success);">Free on orders over ₱10,000</span>
                                    </div>
                                </label>
                                
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_type" value="pickup" onchange="toggleDeliveryOptions()">
                                    <div class="option-content">
                                        <h3>🏪 Store Pickup</h3>
                                        <p>Pick up from our branch</p>
                                        <span class="delivery-fee">Free</span>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Delivery Address -->
                            <div id="deliveryAddress" class="address-section">
                                <h3>Delivery Address</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars(explode(' ', $user_fullname)[0] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars(explode(' ', $user_fullname)[1] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Complete Address *</label>
                                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user_address); ?></textarea>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city">City *</label>
                                        <input type="text" id="city" name="city">
                                    </div>
                                    <div class="form-group">
                                        <label for="postal_code">Postal Code *</label>
                                        <input type="text" id="postal_code" name="postal_code">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pickup Location -->
                            <div id="pickupLocation" class="pickup-section" style="display: none;">
                                <h3>Select Pickup Branch</h3>
                                <div class="form-group">
                                    <select id="pickup_branch" name="pickup_branch">
                                        <option value="">Select a branch</option>
                                        <option value="Calamba">Calamba - National H-way Corner, Carnation St. Brgy. 1 Crossing</option>
                                        <option value="CSC">Calamba - Lot 21 Block 6 National Highway, Real</option>
                                        <option value="SPC">San Pablo - Main Road, National Highway Brgy. 6A Rizal Ave.</option>
                                        <option value="Los Baños">Los Baños - Brgy. San Antonio</option>
                                        <option value="Lbsc">Los Baños Service Center - Brgy. San Antonio</option>
                                        <option value="tanuan">Tanauan, Batangas - #29 Pres. Laurel Highway Poblacion Brgy. 3</option>
                                        <option value="Lucena">Lucena City - H.R Building Quezon Ave. Corner Gomez St.</option>
                                        <option value="STC">Santa Cruz - Doña Lolita Bidg, Sampaguita Circle Brgy. Bubukal</option>
                                        <option value="Gumaca">Gumaca Quezon - JT2 Building, Maharlika Highway, Brgy. Peñafrancia</option>
                                    </select>
                                </div>
                                <div class="pickup-info" style="margin-top: 15px;">
                                    <p><strong>Pickup Hours:</strong> Monday to Saturday, 9:00 AM - 6:00 PM</p>
                                    <p><strong>Contact:</strong> Please call the branch 1 hour before pickup</p>
                                </div>
                            </div>
                        </section>

                        <!-- Payment Information -->
                        <section class="checkout-section">
                            <h2><i class="fas fa-wallet"></i> Payment Method</h2>
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentDetails()">
                                    <div class="option-content">
                                        <h3>💰 Cash on Delivery</h3>
                                        <p>Pay when your order arrives</p>
                                        <span class="payment-note">Available for delivery only</span>
                                    </div>
                                </label>
                                
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="gcash" onchange="togglePaymentDetails()">
                                    <div class="option-content">
                                        <h3>📱 GCash</h3>
                                        <p>Pay via GCash mobile payment</p>
                                        <span class="payment-note">GCash Number: 0936-303-9587</span>
                                    </div>
                                </label>
                                
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="bank" onchange="togglePaymentDetails()">
                                    <div class="option-content">
                                        <h3>🏦 Bank Transfer</h3>
                                        <p>Pay via bank transfer</p>
                                        <span class="payment-note">RCBC: 0000007591419912</span>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- GCash Payment Details -->
                            <div id="gcashDetails" class="payment-details" style="display: none;">
                                <h3>GCash Payment Instructions</h3>
                                <div class="payment-instructions">
                                    <p>1. Send payment to GCash Number: <strong>0936-303-9587</strong></p>
                                    <p>2. Use your order number as reference</p>
                                    <p>3. Send screenshot of payment confirmation to support@pllc-enterprise.com</p>
                                    <div class="gcash-info" style="margin-top: 15px; padding: 10px; background: var(--white); border-radius: 8px;">
                                        <p><strong>GCash Name:</strong> PLLC Enterprise</p>
                                        <p><strong>Amount to Pay:</strong> <span id="gcashAmount">₱0.00</span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Transfer Details -->
                            <div id="bankDetails" class="payment-details" style="display: none;">
                                <h3>Bank Transfer Instructions</h3>
                                <div class="payment-instructions">
                                    <p>1. Transfer to RCBC Account: <strong>0000007591419912</strong></p>
                                    <p>2. Account Name: <strong>PLLC Enterprise</strong></p>
                                    <p>3. Use your order number as reference</p>
                                    <p>4. Send proof of payment to support@pllc-enterprise.com</p>
                                    <div class="bank-info" style="margin-top: 15px; padding: 10px; background: var(--white); border-radius: 8px;">
                                        <p><strong>Bank:</strong> RCBC</p>
                                        <p><strong>Account Number:</strong> 0000007591419912</p>
                                        <p><strong>Amount to Pay:</strong> <span id="bankAmount">₱0.00</span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cash on Delivery Details -->
                            <div id="codDetails" class="payment-details">
                                <h3>Cash on Delivery Information</h3>
                                <div class="payment-instructions">
                                    <p>• Pay the exact amount when your order arrives</p>
                                    <p>• Have exact change ready for faster service</p>
                                    <p>• Our delivery personnel will provide receipt</p>
                                    <div class="cod-info" style="margin-top: 15px; padding: 10px; background: var(--white); border-radius: 8px;">
                                        <p><strong>Payment Amount:</strong> <span id="codAmount">₱0.00</span></p>
                                        <p><strong>Delivery Time:</strong> 2-3 business days</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Order Review -->
                        <section class="checkout-section">
                            <h2><i class="fas fa-shopping-cart"></i> Order Review</h2>
                            <div id="orderItems">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="order-item">
                                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="order-item-image"
                                             onerror="this.src='https://via.placeholder.com/80x80?text=Product'">
                                        <div class="order-item-details">
                                            <div class="order-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div class="order-item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                                            <div class="order-item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                        </div>
                                        <div class="order-item-total" style="text-align: right;">
                                            <div class="order-item-price">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-card">
                            <h3>Order Summary</h3>
                            <div class="summary-line">
                                <span>Subtotal:</span>
                                <span id="subtotal">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Shipping:</span>
                                <span id="shippingFee">₱<?php echo number_format($shipping, 2); ?></span>
                            </div>
                            <hr>
                            <div class="summary-line total">
                                <span>Total:</span>
                                <span id="totalAmount">₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <input type="hidden" name="place_order" value="1">
                            <button type="submit" class="place-order-btn">
                                <i class="fas fa-check-circle"></i> Place Order
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </main>

    <script>
    // Toggle delivery options
    function toggleDeliveryOptions() {
        const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
        const deliveryAddress = document.getElementById('deliveryAddress');
        const pickupLocation = document.getElementById('pickupLocation');
        const shippingFee = document.getElementById('shippingFee');
        const subtotal = <?php echo $subtotal; ?>;
        
        if (deliveryType === 'delivery') {
            deliveryAddress.style.display = 'block';
            pickupLocation.style.display = 'none';
            // Update shipping fee
            let shipping = subtotal < 10000 ? 150 : 0;
            shippingFee.innerHTML = shipping === 0 ? 'Free' : '₱' + shipping.toFixed(2);
            document.getElementById('totalAmount').innerHTML = '₱' + (subtotal + shipping).toFixed(2);
            
            // Update payment amounts
            updatePaymentAmounts(subtotal + shipping);
        } else {
            deliveryAddress.style.display = 'none';
            pickupLocation.style.display = 'block';
            // Free shipping for pickup
            shippingFee.innerHTML = 'Free';
            document.getElementById('totalAmount').innerHTML = '₱' + subtotal.toFixed(2);
            
            // Update payment amounts
            updatePaymentAmounts(subtotal);
        }
    }
    
    // Toggle payment details
    function togglePaymentDetails() {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const gcashDetails = document.getElementById('gcashDetails');
        const bankDetails = document.getElementById('bankDetails');
        const codDetails = document.getElementById('codDetails');
        
        gcashDetails.style.display = 'none';
        bankDetails.style.display = 'none';
        codDetails.style.display = 'none';
        
        if (paymentMethod === 'gcash') {
            gcashDetails.style.display = 'block';
        } else if (paymentMethod === 'bank') {
            bankDetails.style.display = 'block';
        } else {
            codDetails.style.display = 'block';
        }
    }
    
    // Update payment amounts
    function updatePaymentAmounts(amount) {
        const gcashAmount = document.getElementById('gcashAmount');
        const bankAmount = document.getElementById('bankAmount');
        const codAmount = document.getElementById('codAmount');
        
        if (gcashAmount) gcashAmount.innerHTML = '₱' + amount.toFixed(2);
        if (bankAmount) bankAmount.innerHTML = '₱' + amount.toFixed(2);
        if (codAmount) codAmount.innerHTML = '₱' + amount.toFixed(2);
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleDeliveryOptions();
        togglePaymentDetails();
        
        // Set min date for appointment if exists
        const dateInput = document.getElementById('appointmentDate');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        }
    });
    </script>
</body>
</html>