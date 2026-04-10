<?php
// File: C:\xampp\htdocs\pllcg82\admin\delete_product.php

require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if product ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$product_id = intval($_POST['id']);

try {
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Check if product is in any active orders
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $order_items = $stmt->fetch();
    
    if ($order_items['count'] > 0) {
        // Instead of hard delete, mark as unavailable
        $stmt = $pdo->prepare("UPDATE products SET is_available = 0 WHERE id = ?");
        $stmt->execute([$product_id]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Product marked as unavailable (has existing orders)',
            'product_name' => $product['name']
        ]);
        exit;
    }
    
    // Check if product is in any active cart
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $cart_items = $stmt->fetch();
    
    if ($cart_items['count'] > 0) {
        // Remove from carts first
        $stmt = $pdo->prepare("DELETE FROM cart WHERE product_id = ?");
        $stmt->execute([$product_id]);
    }
    
    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Product deleted successfully',
        'product_name' => $product['name']
    ]);
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>