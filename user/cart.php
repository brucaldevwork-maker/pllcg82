<?php
// File: C:\xampp\htdocs\pllcg82\user\cart.php

require_once '../config/config.php';

// Check if user is logged in
$user_name = "User";
$user_id = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT username, full_name FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        if ($user_data) {
            $user_name = $user_data['full_name'] ?: $user_data['username'];
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cart_id = $_POST['cart_id'] ?? 0;
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($action === 'update' && $cart_id > 0) {
        if ($quantity > 0) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$quantity, $cart_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
            $stmt->execute([$cart_id]);
        }
    } elseif ($action === 'remove' && $cart_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cart_id]);
    } elseif ($action === 'clear') {
        if ($user_id) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
        } elseif (isset($_SESSION['session_id'])) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
            $stmt->execute([$_SESSION['session_id']]);
        }
    }
    
    // Redirect to refresh the page
    header("Location: cart.php");
    exit;
}

// Get cart items
$cart_items = [];
$subtotal = 0;

if ($user_id) {
    // Get cart for logged-in user
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.* 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
} elseif (isset($_SESSION['session_id'])) {
    // Get cart for guest user
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.* 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.session_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['session_id']]);
    $cart_items = $stmt->fetchAll();
}

// Calculate subtotal
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Calculate shipping (free for orders over ₱10,000)
$shipping = 0;
if ($subtotal > 0 && $subtotal < 10000) {
    $shipping = 150; // ₱150 shipping fee for orders under ₱10,000
}
$total = $subtotal + $shipping;

// Get cart count
$cart_count = count($cart_items);

// Include navbar
include_once 'components/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, follow">
    
    <title>Shopping Cart | PLLC Enterprise</title>
    <meta name="description" content="Review your items and proceed to checkout. Free shipping on orders over ₱10,000!">
    
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

        .cart-container {
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

        .cart-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        /* Cart Items */
        .cart-items {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 20px;
            min-height: 400px;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            background: var(--white);
            border-radius: 16px;
            margin-bottom: 15px;
            transition: all 0.3s;
            border: 1px solid #eef2f6;
        }

        .cart-item:hover {
            box-shadow: 0 5px 15px rgba(10, 61, 98, 0.1);
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 8px;
        }

        .cart-item-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin: 8px 0;
        }

        .cart-item-stock {
            font-size: 12px;
            margin: 5px 0;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-light);
            padding: 5px 10px;
            border-radius: 30px;
        }

        .quantity-input button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 5px 8px;
            color: var(--primary-blue);
            transition: all 0.3s;
        }

        .quantity-input button:hover {
            color: var(--accent-blue);
        }

        .quantity-input span {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
        }

        .remove-btn {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .remove-btn:hover {
            background: rgba(231, 76, 60, 0.1);
        }

        .cart-item-total {
            text-align: right;
            min-width: 120px;
        }

        .cart-item-total-label {
            font-size: 12px;
            color: var(--text-light);
        }

        .cart-item-total-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-blue);
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            font-size: 80px;
            color: var(--accent-blue);
            opacity: 0.5;
            margin-bottom: 20px;
        }

        .empty-cart h2 {
            color: var(--primary-blue);
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .continue-shopping {
            display: inline-block;
            background: var(--accent-blue);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .continue-shopping:hover {
            background: #0080c0;
            transform: translateY(-2px);
        }

        /* Cart Summary */
        .cart-summary {
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
            padding: 12px 0;
            color: var(--text-dark);
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

        .free-shipping-notice {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            margin: 15px 0;
            text-align: center;
        }

        .free-shipping-notice i {
            margin-right: 5px;
        }

        .checkout-btn {
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
            margin-top: 15px;
        }

        .checkout-btn:hover:not(:disabled) {
            background: #0080c0;
            transform: translateY(-2px);
        }

        .checkout-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        .clear-cart-btn {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: var(--danger);
            border: 1px solid var(--danger);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .clear-cart-btn:hover {
            background: rgba(231, 76, 60, 0.1);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            .cart-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0 15px;
                margin: 20px auto;
            }
            .cart-container {
                padding: 20px;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .cart-item {
                flex-direction: column;
            }
            .cart-item-image {
                width: 100%;
                height: 200px;
            }
            .cart-item-total {
                text-align: left;
                margin-top: 10px;
            }
            .cart-item-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar is included from components/navbar.php -->

    <!-- Main Content -->
    <main class="main-content">
        <div class="cart-container">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
                <p class="page-subtitle">Review your items before checkout</p>
            </div>
            
            <div class="cart-content">
                <div class="cart-items">
                    <?php if (count($cart_items) > 0): ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="cart-item-image"
                                     onerror="this.src='https://via.placeholder.com/120x120?text=Product'">
                                
                                <div class="cart-item-details">
                                    <h3 class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="cart-item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                                    <div class="cart-item-stock">
                                        <?php if($item['stock'] > 0): ?>
                                            <span style="color: var(--success);"><i class="fas fa-check-circle"></i> In Stock</span>
                                        <?php else: ?>
                                            <span style="color: var(--danger);"><i class="fas fa-times-circle"></i> Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="cart-item-actions">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <div class="quantity-input">
                                                <button type="button" onclick="updateQuantity(this, -1)">-</button>
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock']; ?>" 
                                                       style="width: 50px; text-align: center; border: none; background: transparent; font-weight: 600;"
                                                       onchange="this.form.submit()">
                                                <button type="button" onclick="updateQuantity(this, 1)">+</button>
                                            </div>
                                        </form>
                                        
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <button type="submit" class="remove-btn" onclick="return confirm('Remove this item from cart?')">
                                                <i class="fas fa-trash-alt"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="cart-item-total">
                                    <div class="cart-item-total-label">Total</div>
                                    <div class="cart-item-total-price">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Clear Cart Button -->
                        <div style="text-align: right; margin-top: 20px;">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="clear-cart-btn" onclick="return confirm('Clear all items from your cart?')">
                                    <i class="fas fa-trash-alt"></i> Clear Cart
                                </button>
                            </form>
                        </div>
                        
                    <?php else: ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h2>Your cart is empty</h2>
                            <p>Add some products to get started!</p>
                            <a href="products.php" class="continue-shopping">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-line">
                            <span>Subtotal:</span>
                            <span>₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-line">
                            <span>Shipping:</span>
                            <span>
                                <?php if ($shipping > 0): ?>
                                    ₱<?php echo number_format($shipping, 2); ?>
                                <?php else: ?>
                                    Free
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <?php if ($subtotal > 0 && $subtotal < 10000): ?>
                            <div class="free-shipping-notice">
                                <i class="fas fa-truck"></i> 
                                Add ₱<?php echo number_format(10000 - $subtotal, 2); ?> more to get FREE shipping!
                            </div>
                        <?php elseif ($subtotal >= 10000 && $subtotal > 0): ?>
                            <div class="free-shipping-notice">
                                <i class="fas fa-gift"></i> 
                                Congratulations! You qualify for FREE shipping!
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="summary-line total">
                            <span>Total:</span>
                            <span>₱<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <form method="POST" action="checkout.php">
                            <button type="submit" class="checkout-btn" <?php echo count($cart_items) == 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-credit-card"></i> Proceed to Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
    // Update quantity function
    function updateQuantity(button, delta) {
        const form = button.closest('form');
        const quantityInput = form.querySelector('input[name="quantity"]');
        let currentValue = parseInt(quantityInput.value);
        let newValue = currentValue + delta;
        let maxValue = parseInt(quantityInput.getAttribute('max'));
        
        if (newValue >= 1 && newValue <= maxValue) {
            quantityInput.value = newValue;
            form.submit();
        }
    }
    
    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
</body>
</html>