<?php
// File: C:\xampp\htdocs\pllcg82\admin\update_order.php

require_once '../config/config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    if ($order_id <= 0 || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Invalid order data']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$status, $payment_status, $notes, $order_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order']);
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>