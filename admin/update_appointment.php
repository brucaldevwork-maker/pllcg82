<?php
// File: C:\xampp\htdocs\pllcg82\admin\update_appointment.php

require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are provided
if (!isset($_POST['id']) || empty($_POST['id']) || !isset($_POST['status']) || empty($_POST['status'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Appointment ID and status are required']);
    exit;
}

$appointment_id = intval($_POST['id']);
$status = $_POST['status'];

// Validate status
$allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

try {
    // Check if appointment exists
    $stmt = $pdo->prepare("SELECT id, service_type, status FROM appointments WHERE id = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit;
    }
    
    // Update appointment status
    $stmt = $pdo->prepare("UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $appointment_id]);
    
    // Log the change (optional)
    error_log("Appointment #{$appointment_id} status changed from {$appointment['status']} to {$status} by admin");
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Appointment status updated successfully',
        'appointment_id' => $appointment_id,
        'old_status' => $appointment['status'],
        'new_status' => $status
    ]);
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>