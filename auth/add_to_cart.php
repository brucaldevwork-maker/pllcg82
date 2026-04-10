<?php
// File: C:\xampp\htdocs\pllcg82\auth\add_to_cart.php

session_start();
require_once '../config/config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0,
    'redirect' => ''
];

// Check if product_id is provided
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    $response['message'] = 'No product specified.';
    echo json_encode($response);
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Validate quantity
if ($quantity < 1) {
    $quantity = 1;
}

try {
    // Check if product exists and has stock
    $stmt = $pdo->prepare("SELECT id, name, price, stock, is_available FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $response['message'] = 'Product not found.';
        echo json_encode($response);
        exit;
    }
    
    if ($product['is_available'] != 1) {
        $response['message'] = 'This product is currently not available.';
        echo json_encode($response);
        exit;
    }
    
    if ($product['stock'] <= 0) {
        $response['message'] = 'Sorry, this product is out of stock.';
        echo json_encode($response);
        exit;
    }
    
    // Check if quantity exceeds stock
    if ($quantity > $product['stock']) {
        $quantity = $product['stock'];
        $response['message'] = 'Only ' . $product['stock'] . ' items available. Quantity adjusted.';
    }
    
    // Determine user identifier
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $session_id = !$user_id && isset($_SESSION['session_id']) ? $_SESSION['session_id'] : null;
    
    // Generate session_id for guest users if not exists
    if (!$user_id && !$session_id) {
        $session_id = session_id();
        $_SESSION['session_id'] = $session_id;
    }
    
    // Check if product already exists in cart
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$session_id, $product_id]);
    }
    
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing cart item
        $new_quantity = $existing['quantity'] + $quantity;
        
        // Check if new quantity exceeds stock
        if ($new_quantity > $product['stock']) {
            $new_quantity = $product['stock'];
            $response['message'] = 'Only ' . $product['stock'] . ' items available. Quantity adjusted.';
        }
        
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $existing['id']]);
        
        if (empty($response['message'])) {
            $response['message'] = $product['name'] . ' quantity updated in your cart.';
        } else {
            $response['message'] = $product['name'] . ' - ' . $response['message'];
        }
    } else {
        // Add new item to cart
        if ($user_id) {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $product_id, $quantity]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$session_id, $product_id, $quantity]);
        }
        
        $response['message'] = $product['name'] . ' added to your cart!';
    }
    
    // Get updated cart count
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        $response['cart_count'] = $result['total'] ?? 0;
    } elseif ($session_id) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $result = $stmt->fetch();
        $response['cart_count'] = $result['total'] ?? 0;
    }
    
    $response['success'] = true;
    
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Return JSON for AJAX requests
        echo json_encode($response);
        exit;
    } else {
        // Redirect back for regular form submissions
        $redirect_to = isset($_POST['redirect']) ? $_POST['redirect'] : '../user/products.php';
        
        // Store success message in session for display
        $_SESSION['cart_message'] = $response['message'];
        $_SESSION['cart_message_type'] = 'success';
        
        header("Location: " . $redirect_to);
        exit;
    }
    
} catch(PDOException $e) {
    error_log("Add to cart error: " . $e->getMessage());
    $response['message'] = 'Unable to add product to cart. Please try again.';
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode($response);
    } else {
        $_SESSION['cart_message'] = $response['message'];
        $_SESSION['cart_message_type'] = 'error';
        $redirect_to = isset($_POST['redirect']) ? $_POST['redirect'] : '../user/products.php';
        header("Location: " . $redirect_to);
    }
    exit;
}
?>