<?php
// File: C:\xampp\htdocs\pllcg82\admin\admin_dashboard.php

require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../auth/admin_login.php');
    exit;
}

$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role'];

// Fetch statistics from database
try {
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $total_orders = $stmt->fetch()['total'] ?? 0;
    
    // Pending appointments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE status = 'pending'");
    $pending_appointments = $stmt->fetch()['total'] ?? 0;
    
    // Total feedback
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM feedback");
    $total_feedback = $stmt->fetch()['total'] ?? 0;
    
    // Average rating
    $stmt = $pdo->query("SELECT AVG(rating) as avg_rating FROM feedback WHERE rating IS NOT NULL");
    $avg_rating = $stmt->fetch()['avg_rating'] ?? 0;
    $avg_rating = round($avg_rating, 1);
    
    // Low stock items (stock <= min_stock)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock <= min_stock AND stock > 0");
    $low_stock_items = $stmt->fetch()['total'] ?? 0;
    
    // Out of stock items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock = 0 OR stock IS NULL");
    $out_of_stock_items = $stmt->fetch()['total'] ?? 0;
    
    // Stock alerts
    $stmt = $pdo->query("SELECT id, name, stock, min_stock FROM products WHERE stock <= min_stock ORDER BY stock ASC LIMIT 10");
    $stock_alerts = $stmt->fetchAll();
    
    // Fetch orders
    $stmt = $pdo->query("SELECT o.*, u.username, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 50");
    $orders = $stmt->fetchAll();
    
    // Fetch appointments with more details
    $stmt = $pdo->query("SELECT a.*, u.username, u.full_name, u.email, u.phone FROM appointments a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 50");
    $appointments = $stmt->fetchAll();
    
    // Fetch feedback with user and product info
    $stmt = $pdo->query("SELECT f.*, u.username, u.full_name, u.email, p.name as product_name, p.price as product_price FROM feedback f LEFT JOIN users u ON f.user_id = u.id LEFT JOIN products p ON f.product_id = p.id ORDER BY f.created_at DESC LIMIT 50");
    $feedback_list = $stmt->fetchAll();
    
    // Fetch products
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
    
    // Analytics Data
    // Monthly orders for last 12 months
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, SUM(total_amount) as revenue FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month ASC");
    $monthly_orders = $stmt->fetchAll();
    
    // Feedback distribution by rating
    $stmt = $pdo->query("SELECT rating, COUNT(*) as count FROM feedback WHERE rating IS NOT NULL GROUP BY rating ORDER BY rating DESC");
    $rating_distribution = $stmt->fetchAll();
    
    // Sentiment analysis summary
    $stmt = $pdo->query("SELECT 
        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive,
        SUM(CASE WHEN rating >= 2.5 AND rating < 4 THEN 1 ELSE 0 END) as neutral,
        SUM(CASE WHEN rating < 2.5 THEN 1 ELSE 0 END) as negative
        FROM feedback WHERE rating IS NOT NULL");
    $sentiment_summary = $stmt->fetch();
    
    // Top rated products
    $stmt = $pdo->query("SELECT p.id, p.name, p.rating, p.reviews, COUNT(f.id) as total_feedback, AVG(f.rating) as avg_rating FROM products p LEFT JOIN feedback f ON p.id = f.product_id GROUP BY p.id ORDER BY avg_rating DESC LIMIT 5");
    $top_products = $stmt->fetchAll();
    
    // Recent feedback trends (last 7 days)
    $stmt = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count, AVG(rating) as avg_rating FROM feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date ASC");
    $weekly_feedback = $stmt->fetchAll();
    
    // Order status distribution
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $order_status_dist = $stmt->fetchAll();
    
    // Appointment status distribution
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
    $appointment_status_dist = $stmt->fetchAll();
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'");
    $total_revenue = $stmt->fetch()['total_revenue'] ?? 0;
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    $total_orders = $pending_appointments = $total_feedback = $low_stock_items = $out_of_stock_items = 0;
    $avg_rating = 0;
    $total_revenue = 0;
    $orders = $appointments = $feedback_list = $products = $stock_alerts = [];
    $monthly_orders = $rating_distribution = $top_products = $weekly_feedback = $order_status_dist = $appointment_status_dist = [];
    $sentiment_summary = ['positive' => 0, 'neutral' => 0, 'negative' => 0];
}

// Helper function for sentiment analysis
function getSentiment($rating) {
    if ($rating >= 4) return 'positive';
    if ($rating >= 2.5) return 'neutral';
    return 'negative';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PLLC Enterprise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --star-gold: #FFA41C;
            --purple: #8B5CF6;
            --pink: #EC4899;
            --orange: #F97316;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-light);
            overflow-x: hidden;
        }
        
        /* Header Styles */
        .amazon-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-top {
            background: rgba(0,0,0,0.2);
            padding: 8px 0;
            font-size: 13px;
        }
        
        .header-top-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .header-main {
            padding: 12px 0;
        }
        
        .header-main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .logo i {
            color: var(--accent-blue);
        }
        
        .logo:hover {
            color: var(--accent-blue);
            transform: scale(1.05);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-link:hover {
            background: rgba(0,168,232,0.2);
            color: var(--accent-blue);
        }
        
        .logout-btn {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logout-btn:hover {
            background: #0080c0;
            transform: translateY(-2px);
        }
        
        /* Hamburger Menu Button */
        .hamburger-menu {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
            transition: all 0.3s;
        }
        
        .hamburger-menu:hover {
            color: var(--accent-blue);
        }
        
        /* Main Layout */
        .main-content {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .admin-dashboard {
            display: flex;
            gap: 20px;
            min-height: calc(100vh - 150px);
        }
        
        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(10, 61, 98, 0.08);
            padding: 20px 0;
            height: fit-content;
            position: sticky;
            top: 100px;
            border: 1px solid rgba(10, 61, 98, 0.1);
            transition: all 0.3s ease;
        }
        
        .admin-sidebar.hide {
            display: none;
        }
        
        .admin-nav {
            display: flex;
            flex-direction: column;
        }
        
        .admin-nav-btn {
            background: none;
            border: none;
            padding: 14px 20px;
            text-align: left;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            margin: 2px 10px;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .admin-nav-btn i {
            width: 24px;
            color: var(--text-light);
        }
        
        .admin-nav-btn:hover {
            background: rgba(0,168,232,0.1);
            transform: translateX(5px);
        }
        
        .admin-nav-btn.active {
            background: linear-gradient(135deg, var(--accent-blue), #0080c0);
            color: white;
        }
        
        .admin-nav-btn.active i {
            color: white;
        }
        
        /* Content Area */
        .admin-content {
            flex: 1;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(10, 61, 98, 0.08);
            padding: 30px;
            border: 1px solid rgba(10, 61, 98, 0.1);
            transition: all 0.3s ease;
        }
        
        .admin-section {
            display: none;
        }
        
        .admin-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,168,232,0.1) 0%, rgba(0,168,232,0) 100%);
            pointer-events: none;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(10, 61, 98, 0.2);
        }
        
        .stat-card h3 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
        }
        
        .stat-card.low-stock-alert {
            background: linear-gradient(135deg, var(--warning) 0%, #e67e22 100%);
        }
        
        .stat-card.out-of-stock-alert {
            background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);
        }
        
        .stat-card.revenue-card {
            background: linear-gradient(135deg, var(--purple) 0%, #6B46C0 100%);
        }
        
        /* Analytics Grid */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .analytics-card {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        
        .analytics-card h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-blue);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 15px;
        }
        
        canvas {
            max-height: 300px;
            width: 100% !important;
        }
        
        .sentiment-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            gap: 15px;
        }
        
        .sentiment-item {
            text-align: center;
            flex: 1;
            padding: 15px;
            border-radius: 12px;
            transition: transform 0.3s;
        }
        
        .sentiment-item:hover {
            transform: translateY(-3px);
        }
        
        .sentiment-positive {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }
        
        .sentiment-neutral {
            background: linear-gradient(135deg, #fff3cd, #ffeeba);
            color: #856404;
        }
        
        .sentiment-negative {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }
        
        .sentiment-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .sentiment-label {
            font-size: 14px;
            font-weight: 600;
        }
        
        .top-product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #eef2f6;
        }
        
        .top-product-item:last-child {
            border-bottom: none;
        }
        
        .product-rating {
            color: var(--star-gold);
        }
        
        /* Alerts Section */
        .alerts-section {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            border-left: 4px solid var(--warning);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        
        .alerts-section h3 {
            color: var(--warning);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-item {
            padding: 12px;
            border-bottom: 1px solid #ffe0b3;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert-item:last-child {
            border-bottom: none;
        }
        
        .alert-badge {
            background: var(--warning);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Tables */
        .admin-table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #eef2f6;
        }
        
        .admin-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--primary-blue);
        }
        
        .admin-table tr:hover {
            background: rgba(0,168,232,0.05);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-confirmed {
            background: #cce5ff;
            color: #004085;
        }
        
        /* Buttons */
        .action-btn {
            padding: 6px 12px;
            margin: 0 3px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background: var(--accent-blue);
            color: white;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .btn-view {
            background: var(--success);
            color: white;
        }
        
        .btn-add {
            background: var(--success);
            color: white;
            padding: 10px 20px;
            font-size: 14px;
        }
        
        .btn-save {
            background: var(--success);
            color: white;
        }
        
        .btn-cancel {
            background: var(--text-light);
            color: white;
        }
        
        /* Edit Modal */
        .edit-form-group {
            margin-bottom: 20px;
        }
        
        .edit-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-blue);
        }
        
        .edit-form-group input,
        .edit-form-group select,
        .edit-form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e4e8;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .edit-form-group input:focus,
        .edit-form-group select:focus,
        .edit-form-group textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
        }
        
        /* Stock Level Indicators */
        .stock-level {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .stock-high {
            background: #d4edda;
            color: #155724;
        }
        
        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-low {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Rating Stars */
        .rating-stars {
            color: var(--star-gold);
            font-size: 14px;
        }
        
        /* Status Select */
        .status-select {
            padding: 6px 10px;
            border: 1px solid #e0e4e8;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .status-select:focus {
            outline: none;
            border-color: var(--accent-blue);
        }
        
        /* Section Headers */
        h2 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--accent-blue);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        /* Enhanced Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 3% auto;
            padding: 0;
            border-radius: 24px;
            width: 90%;
            max-width: 650px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 25px 30px;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(0,168,232,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }
        
        .modal-header h3 i {
            color: var(--accent-blue);
            font-size: 28px;
        }
        
        .close-modal {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            transition: all 0.3s;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        .close-modal:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 30px;
            max-height: 60vh;
            overflow-y: auto;
            background: var(--white);
        }
        
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: var(--bg-light);
            border-radius: 10px;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: var(--accent-blue);
            border-radius: 10px;
        }
        
        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #eef2f6;
            text-align: right;
            background: var(--bg-light);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .modal-footer button {
            padding: 10px 30px;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 14px;
            border: none;
        }
        
        .detail-card {
            background: var(--bg-light);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .detail-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 700;
            width: 140px;
            color: var(--primary-blue);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            flex: 1;
            color: var(--text-dark);
            font-size: 15px;
        }
        
        .detail-value i {
            margin-right: 8px;
            color: var(--accent-blue);
            width: 20px;
        }
        
        .feedback-full {
            background: linear-gradient(135deg, #F8F9FA 0%, #FFFFFF 100%);
            padding: 20px;
            border-radius: 16px;
            margin-top: 10px;
            line-height: 1.8;
            border-left: 4px solid var(--accent-blue);
            font-style: italic;
        }
        
        .sentiment-badge-large {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 20px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .rating-large {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
        }
        
        .rating-large .rating-stars {
            font-size: 24px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .admin-dashboard {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
                position: static;
                display: block;
            }
            
            .admin-sidebar.hide {
                display: none;
            }
            
            .admin-nav {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .admin-nav-btn {
                flex: 1;
                text-align: center;
                justify-content: center;
                min-width: 150px;
            }
            
            .hamburger-menu {
                display: block;
            }
            
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header-main-content {
                flex-direction: row;
                justify-content: space-between;
            }
            
            .header-right {
                display: none;
            }
            
            .header-right.show {
                display: flex;
                flex-direction: column;
                width: 100%;
                margin-top: 15px;
            }
            
            .admin-content {
                padding: 20px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            .header-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 8px;
            }
            
            .modal-body {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .admin-nav-btn {
                min-width: 120px;
                font-size: 13px;
                padding: 10px 12px;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 10px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <header class="amazon-header">
        <div class="header-top">
            <div class="header-top-content">
                <span><i class="fas fa-store"></i> PLLC Enterprise Admin Panel</span>
                <span><i class="fas fa-user-shield"></i> Welcome, <?php echo htmlspecialchars($admin_username); ?> (<?php echo htmlspecialchars($admin_role); ?>)</span>
            </div>
        </div>
        
        <div class="header-main">
            <div class="header-main-content">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="hamburger-menu" id="hamburgerBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a href="admin_dashboard.php" class="logo">
                        <i class="fas fa-bicycle"></i> PLLC Admin
                    </a>
                </div>
                
                <div class="header-right" id="headerRight">
                    <a href="../index.php" class="nav-link" target="_blank">
                        <i class="fas fa-globe"></i> View Site
                    </a>
                    <a href="../auth/admin_register.php" class="nav-link">
                        <i class="fas fa-user-plus"></i> Add Admin
                    </a>
                    <button onclick="location.href='../auth/admin_logout.php'" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="admin-dashboard">
            <div class="admin-sidebar" id="sidebar">
                <nav class="admin-nav">
                    <button class="admin-nav-btn active" data-section="overview">
                        <i class="fas fa-chart-line"></i> Dashboard Overview
                    </button>
                    <button class="admin-nav-btn" data-section="orders">
                        <i class="fas fa-box"></i> Orders Management
                    </button>
                    <button class="admin-nav-btn" data-section="appointments">
                        <i class="fas fa-calendar-check"></i> Appointments
                    </button>
                    <button class="admin-nav-btn" data-section="feedback">
                        <i class="fas fa-comment-dots"></i> Customer Feedback
                    </button>
                    <button class="admin-nav-btn" data-section="products">
                        <i class="fas fa-tools"></i> Products Management
                    </button>
                    <button class="admin-nav-btn" data-section="analytics">
                        <i class="fas fa-chart-pie"></i> Analytics
                    </button>
                </nav>
            </div>
            
            <div class="admin-content" id="adminContent">
                <!-- Overview Section -->
                <div id="overview" class="admin-section active">
                    <h2><i class="fas fa-chart-line"></i> Dashboard Overview</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><i class="fas fa-shopping-cart"></i> Total Orders</h3>
                            <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3><i class="fas fa-calendar-alt"></i> Pending Appointments</h3>
                            <div class="stat-number"><?php echo number_format($pending_appointments); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3><i class="fas fa-star"></i> Total Feedback</h3>
                            <div class="stat-number"><?php echo number_format($total_feedback); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3><i class="fas fa-chart-simple"></i> Average Rating</h3>
                            <div class="stat-number">⭐ <?php echo number_format($avg_rating, 1); ?></div>
                        </div>
                        <div class="stat-card revenue-card">
                            <h3><i class="fas fa-dollar-sign"></i> Total Revenue</h3>
                            <div class="stat-number">₱<?php echo number_format($total_revenue, 2); ?></div>
                        </div>
                        <div class="stat-card low-stock-alert">
                            <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Items</h3>
                            <div class="stat-number"><?php echo number_format($low_stock_items); ?></div>
                        </div>
                        <div class="stat-card out-of-stock-alert">
                            <h3><i class="fas fa-times-circle"></i> Out of Stock</h3>
                            <div class="stat-number"><?php echo number_format($out_of_stock_items); ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($stock_alerts)): ?>
                    <div class="alerts-section">
                        <h3><i class="fas fa-bell"></i> Stock Alerts</h3>
                        <?php foreach ($stock_alerts as $alert): ?>
                        <div class="alert-item">
                            <span><strong><?php echo htmlspecialchars($alert['name']); ?></strong> - Current Stock: <?php echo $alert['stock']; ?> (Min: <?php echo $alert['min_stock']; ?>)</span>
                            <span class="alert-badge"><i class="fas fa-exclamation"></i> Low Stock</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Orders Section -->
                <div id="orders" class="admin-section">
                    <h2><i class="fas fa-box"></i> Order Management</h2>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="7" style="text-align: center;">No orders found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_number'] ?? '#' . $order['id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['full_name'] ?? $order['username'] ?? 'Guest'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                                        <td>
                                            <button class="action-btn btn-view" onclick="viewOrder(<?php echo htmlspecialchars(json_encode($order)); ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="action-btn btn-edit" onclick="editOrder(<?php echo htmlspecialchars(json_encode($order)); ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Appointments Section -->
                <div id="appointments" class="admin-section">
                    <h2><i class="fas fa-calendar-check"></i> Appointment Management</h2>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Service Type</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr><td colspan="7" style="text-align: center;">No appointments found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td>#<?php echo $appointment['id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['full_name'] ?? $appointment['username'] ?? 'Guest'); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="action-btn btn-view" onclick='viewAppointmentDetails(<?php echo json_encode($appointment); ?>)'>
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                            <select class="status-select" onchange="updateAppointmentStatus(<?php echo $appointment['id']; ?>, this.value)">
                                                <option value="pending" <?php echo $appointment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $appointment['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Feedback Section -->
                <div id="feedback" class="admin-section">
                    <h2><i class="fas fa-comment-dots"></i> Customer Feedback</h2>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Rating</th>
                                    <th>Sentiment</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($feedback_list)): ?>
                                    <tr><td colspan="8" style="text-align: center;">No feedback found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($feedback_list as $feedback): ?>
                                    <tr>
                                        <td>#<?php echo $feedback['id']; ?></td>
                                        <td><?php echo htmlspecialchars($feedback['full_name'] ?? $feedback['username'] ?? 'Guest'); ?></td>
                                        <td><?php echo htmlspecialchars($feedback['product_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php 
                                                $rating = $feedback['rating'];
                                                for($i = 1; $i <= 5; $i++) {
                                                    if($i <= $rating) echo '★';
                                                    else echo '☆';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $sentiment = getSentiment($feedback['rating']);
                                            $sentiment_class = $sentiment == 'positive' ? '#d4edda' : ($sentiment == 'negative' ? '#f8d7da' : '#fff3cd');
                                            $sentiment_color = $sentiment == 'positive' ? '#155724' : ($sentiment == 'negative' ? '#721c24' : '#856404');
                                            ?>
                                            <span class="status-badge" style="background: <?php echo $sentiment_class; ?>; color: <?php echo $sentiment_color; ?>;">
                                                <i class="fas fa-<?php echo $sentiment == 'positive' ? 'smile' : ($sentiment == 'negative' ? 'frown' : 'meh'); ?>"></i>
                                                <?php echo ucfirst($sentiment); ?>
                                            </span>
                                        </td>
                                        <td style="max-width: 250px;"><?php echo htmlspecialchars(substr($feedback['comment'] ?? '', 0, 50)) . (strlen($feedback['comment'] ?? '') > 50 ? '...' : ''); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                        <td>
                                            <button class="action-btn btn-view" onclick='viewFeedbackDetails(<?php echo json_encode($feedback); ?>)'>
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Products Section -->
                <div id="products" class="admin-section">
                    <div class="header-actions">
                        <h2><i class="fas fa-tools"></i> Product Management</h2>
                        <a href="add_product.php" class="action-btn btn-add">
                            <i class="fas fa-plus-circle"></i> Add New Product
                        </a>
                    </div>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product Details</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Availability</th>
                                    <th>Rating</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr><td colspan="7" style="text-align: center;">No products found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>#<?php echo $product['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                            <small style="color: var(--text-light);"><?php echo htmlspecialchars($product['category'] . ($product['subcategory'] ? ' > ' . $product['subcategory'] : '')); ?></small>
                                        </td>
                                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $stock_class = 'stock-high';
                                            if($product['stock'] <= 0) $stock_class = 'stock-low';
                                            elseif($product['stock'] <= $product['min_stock']) $stock_class = 'stock-medium';
                                            ?>
                                            <span class="stock-level <?php echo $stock_class; ?>">
                                                <i class="fas fa-<?php echo $stock_class == 'stock-high' ? 'check-circle' : ($stock_class == 'stock-medium' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
                                                <?php echo $product['stock'] ?? 0; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge" style="background: <?php echo $product['is_available'] ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $product['is_available'] ? '#155724' : '#721c24'; ?>;">
                                                <i class="fas fa-<?php echo $product['is_available'] ? 'check' : 'times'; ?>"></i>
                                                <?php echo $product['is_available'] ? 'Available' : 'Unavailable'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php 
                                                $rating = $product['rating'];
                                                for($i = 1; $i <= 5; $i++) {
                                                    if($i <= $rating) echo '★';
                                                    else echo '☆';
                                                }
                                                ?>
                                                <small style="color: var(--text-light);">(<?php echo $product['reviews'] ?? 0; ?>)</small>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="action-btn btn-edit" onclick="location.href='edit_product.php?id=<?php echo $product['id']; ?>'">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="action-btn btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Analytics Section -->
                <div id="analytics" class="admin-section">
                    <h2><i class="fas fa-chart-pie"></i> Analytics Dashboard</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><i class="fas fa-smile"></i> Positive Feedback</h3>
                            <div class="stat-number"><?php echo $sentiment_summary['positive']; ?></div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                            <h3><i class="fas fa-meh"></i> Neutral Feedback</h3>
                            <div class="stat-number"><?php echo $sentiment_summary['neutral']; ?></div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                            <h3><i class="fas fa-frown"></i> Negative Feedback</h3>
                            <div class="stat-number"><?php echo $sentiment_summary['negative']; ?></div>
                        </div>
                    </div>
                    
                    <div class="analytics-grid">
                        <!-- Monthly Orders Chart -->
                        <div class="analytics-card">
                            <h3><i class="fas fa-chart-line"></i> Monthly Orders & Revenue</h3>
                            <div class="chart-container">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Rating Distribution -->
                        <div class="analytics-card">
                            <h3><i class="fas fa-star"></i> Feedback Rating Distribution</h3>
                            <div class="chart-container">
                                <canvas id="ratingChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Sentiment Analysis -->
                        <div class="analytics-card">
                            <h3><i class="fas fa-chart-pie"></i> Sentiment Analysis</h3>
                            <div class="chart-container">
                                <canvas id="sentimentChart"></canvas>
                            </div>
                            <div class="sentiment-stats">
                                <div class="sentiment-item sentiment-positive">
                                    <i class="fas fa-smile fa-2x"></i>
                                    <div class="sentiment-number"><?php echo $sentiment_summary['positive']; ?></div>
                                    <div class="sentiment-label">Positive</div>
                                </div>
                                <div class="sentiment-item sentiment-neutral">
                                    <i class="fas fa-meh fa-2x"></i>
                                    <div class="sentiment-number"><?php echo $sentiment_summary['neutral']; ?></div>
                                    <div class="sentiment-label">Neutral</div>
                                </div>
                                <div class="sentiment-item sentiment-negative">
                                    <i class="fas fa-frown fa-2x"></i>
                                    <div class="sentiment-number"><?php echo $sentiment_summary['negative']; ?></div>
                                    <div class="sentiment-label">Negative</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Weekly Feedback Trend -->
                        <div class="analytics-card">
                            <h3><i class="fas fa-chart-line"></i> Weekly Feedback Trend</h3>
                            <div class="chart-container">
                                <canvas id="weeklyTrendChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Order Status Distribution -->
                        <div class="analytics-card">
                            <h3><i class="fas fa-chart-pie"></i> Order Status Distribution</h3>
                            <div class="chart-container">
                                <canvas id="orderStatusChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Top Rated Products -->
                        <div class="analytics-card">
                            <h3><i class="fas fa-trophy"></i> Top Rated Products</h3>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php if (empty($top_products)): ?>
                                    <p style="text-align: center; color: var(--text-light);">No products with ratings yet</p>
                                <?php else: ?>
                                    <?php foreach ($top_products as $product): ?>
                                    <div class="top-product-item">
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                            <div class="product-rating">
                                                <?php 
                                                $avg_rating = round($product['avg_rating'] ?? 0, 1);
                                                for($i = 1; $i <= 5; $i++) {
                                                    if($i <= $avg_rating) echo '★';
                                                    else echo '☆';
                                                }
                                                ?>
                                                <small>(<?php echo $product['total_feedback']; ?> reviews)</small>
                                            </div>
                                        </div>
                                        <div style="font-weight: bold; color: var(--primary-blue);">
                                            <?php echo number_format($avg_rating, 1); ?> ★
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Enhanced Modal for Appointment Details -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-check"></i> Appointment Details</h3>
                <button class="close-modal" onclick="closeModal('appointmentModal')">&times;</button>
            </div>
            <div class="modal-body" id="appointmentModalBody">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('appointmentModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Enhanced Modal for Feedback Details -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-comment-dots"></i> Feedback Details</h3>
                <button class="close-modal" onclick="closeModal('feedbackModal')">&times;</button>
            </div>
            <div class="modal-body" id="feedbackModalBody">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('feedbackModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Modal for Order Edit -->
    <div id="orderEditModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Order</h3>
                <button class="close-modal" onclick="closeModal('orderEditModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm">
                    <input type="hidden" id="edit_order_id" name="order_id">
                    <div class="edit-form-group">
                        <label><i class="fas fa-tag"></i> Order Status</label>
                        <select id="edit_order_status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="edit-form-group">
                        <label><i class="fas fa-money-bill"></i> Payment Status</label>
                        <select id="edit_payment_status" name="payment_status">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="edit-form-group">
                        <label><i class="fas fa-sticky-note"></i> Notes</label>
                        <textarea id="edit_notes" name="notes" rows="3" placeholder="Add notes about this order..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal('orderEditModal')">Cancel</button>
                <button class="btn-save" onclick="saveOrderChanges()">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Modal for Order View -->
    <div id="orderViewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-box"></i> Order Details</h3>
                <button class="close-modal" onclick="closeModal('orderViewModal')">&times;</button>
            </div>
            <div class="modal-body" id="orderViewModalBody">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('orderViewModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Hamburger Menu Toggle for Sidebar
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        let sidebarVisible = true;
        
        hamburgerBtn.addEventListener('click', function() {
            sidebar.classList.toggle('hide');
            sidebarVisible = !sidebarVisible;
            
            const icon = this.querySelector('i');
            if (sidebarVisible) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        });
        
        // Responsive header menu for mobile
        const headerRight = document.getElementById('headerRight');
        
        function checkScreenSize() {
            if (window.innerWidth <= 768) {
                if (!headerRight.hasAttribute('data-toggle')) {
                    headerRight.setAttribute('data-toggle', 'true');
                    const headerMain = document.querySelector('.header-main-content');
                    const toggleBtn = document.createElement('button');
                    toggleBtn.className = 'hamburger-menu';
                    toggleBtn.innerHTML = '<i class="fas fa-user-circle"></i>';
                    toggleBtn.style.marginLeft = 'auto';
                    toggleBtn.onclick = function() {
                        headerRight.classList.toggle('show');
                    };
                    headerMain.appendChild(toggleBtn);
                }
            } else {
                if (headerRight.hasAttribute('data-toggle')) {
                    headerRight.removeAttribute('data-toggle');
                    const extraBtn = document.querySelector('.header-main-content .hamburger-menu:last-child');
                    if (extraBtn) extraBtn.remove();
                    headerRight.classList.remove('show');
                }
            }
        }
        
        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();
        
        // Navigation
        document.querySelectorAll('.admin-nav-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const section = this.dataset.section;
                
                document.querySelectorAll('.admin-nav-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
                document.getElementById(section).classList.add('active');
                
                if (window.innerWidth <= 992) {
                    sidebar.classList.add('hide');
                    sidebarVisible = false;
                    const hamburgerIcon = hamburgerBtn.querySelector('i');
                    hamburgerIcon.classList.remove('fa-bars');
                    hamburgerIcon.classList.add('fa-times');
                }
                
                // Load charts when analytics section is shown
                if (section === 'analytics') {
                    setTimeout(loadCharts, 100);
                }
            });
        });
        
        // Order functions
        function editOrder(order) {
            document.getElementById('edit_order_id').value = order.id;
            document.getElementById('edit_order_status').value = order.status;
            document.getElementById('edit_payment_status').value = order.payment_status || 'pending';
            document.getElementById('edit_notes').value = order.notes || '';
            document.getElementById('orderEditModal').style.display = 'block';
        }
        
        function saveOrderChanges() {
            const orderId = document.getElementById('edit_order_id').value;
            const status = document.getElementById('edit_order_status').value;
            const paymentStatus = document.getElementById('edit_payment_status').value;
            const notes = document.getElementById('edit_notes').value;
            
            fetch('update_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + orderId + '&status=' + status + '&payment_status=' + paymentStatus + '&notes=' + encodeURIComponent(notes)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Order updated successfully!');
                    closeModal('orderEditModal');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Error updating order'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating order');
            });
        }
        
        function viewOrder(order) {
            const modalBody = document.getElementById('orderViewModalBody');
            modalBody.innerHTML = `
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-qrcode"></i> Order ID:</div>
                        <div class="detail-value"><strong>${escapeHtml(order.order_number || '#' + order.id)}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-user"></i> Customer:</div>
                        <div class="detail-value">${escapeHtml(order.full_name || order.username || 'Guest')}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-calendar"></i> Date:</div>
                        <div class="detail-value">${formatDate(order.created_at)}</div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-money-bill"></i> Total Amount:</div>
                        <div class="detail-value"><strong style="color: var(--success); font-size: 18px;">₱${parseFloat(order.total_amount).toFixed(2)}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-credit-card"></i> Payment Method:</div>
                        <div class="detail-value">${escapeHtml(order.payment_method || 'N/A')}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-flag-checkered"></i> Order Status:</div>
                        <div class="detail-value">
                            <span class="status-badge status-${order.status}">
                                ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                            </span>
                        </div>
                    </div>
                </div>
                
                ${order.notes ? `
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-sticky-note"></i> Notes:</div>
                        <div class="detail-value">
                            <div class="feedback-full">${escapeHtml(order.notes)}</div>
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
            document.getElementById('orderViewModal').style.display = 'block';
        }
        
        // Enhanced View Appointment Details
        function viewAppointmentDetails(appointment) {
            const modalBody = document.getElementById('appointmentModalBody');
            
            const statusColors = {
                'pending': '#856404',
                'confirmed': '#004085',
                'completed': '#155724',
                'cancelled': '#721c24'
            };
            
            const statusBgColors = {
                'pending': '#fff3cd',
                'confirmed': '#cce5ff',
                'completed': '#d4edda',
                'cancelled': '#f8d7da'
            };
            
            const statusIcons = {
                'pending': 'fa-clock',
                'confirmed': 'fa-check-circle',
                'completed': 'fa-check-double',
                'cancelled': 'fa-times-circle'
            };
            
            const statusColor = statusColors[appointment.status] || '#6c757d';
            const statusBgColor = statusBgColors[appointment.status] || '#e9ecef';
            const statusIcon = statusIcons[appointment.status] || 'fa-question-circle';
            
            modalBody.innerHTML = `
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-qrcode"></i> Appointment ID:</div>
                        <div class="detail-value"><strong>#${appointment.id}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-user"></i> Customer Name:</div>
                        <div class="detail-value">${escapeHtml(appointment.full_name || appointment.username || 'Guest')}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-envelope"></i> Email:</div>
                        <div class="detail-value">${escapeHtml(appointment.email || 'N/A')}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-phone"></i> Phone:</div>
                        <div class="detail-value">${escapeHtml(appointment.phone || 'N/A')}</div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-wrench"></i> Service Type:</div>
                        <div class="detail-value"><strong>${escapeHtml(appointment.service_type)}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-calendar"></i> Appointment Date:</div>
                        <div class="detail-value">${formatDate(appointment.appointment_date)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-clock"></i> Appointment Time:</div>
                        <div class="detail-value">${formatTime(appointment.appointment_time)}</div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-flag-checkered"></i> Status:</div>
                        <div class="detail-value">
                            <span class="sentiment-badge-large" style="background: ${statusBgColor}; color: ${statusColor};">
                                <i class="fas ${statusIcon}"></i> ${appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1)}
                            </span>
                        </div>
                    </div>
                    ${appointment.notes ? `
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-sticky-note"></i> Notes:</div>
                        <div class="detail-value">
                            <div class="feedback-full" style="border-left-color: var(--accent-blue);">
                                <i class="fas fa-quote-left" style="color: var(--accent-blue); margin-right: 8px;"></i>
                                ${escapeHtml(appointment.notes)}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-calendar-plus"></i> Created:</div>
                        <div class="detail-value">${formatDateTime(appointment.created_at)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-edit"></i> Last Updated:</div>
                        <div class="detail-value">${formatDateTime(appointment.updated_at)}</div>
                    </div>
                </div>
            `;
            
            document.getElementById('appointmentModal').style.display = 'block';
        }
        
        // Enhanced View Feedback Details
        function viewFeedbackDetails(feedback) {
            const modalBody = document.getElementById('feedbackModalBody');
            
            const sentiment = getSentimentFromRating(feedback.rating);
            const sentimentClass = sentiment === 'positive' ? '#d4edda' : (sentiment === 'negative' ? '#f8d7da' : '#fff3cd');
            const sentimentColor = sentiment === 'positive' ? '#155724' : (sentiment === 'negative' ? '#721c24' : '#856404');
            const sentimentIcon = sentiment === 'positive' ? 'smile' : (sentiment === 'negative' ? 'frown' : 'meh');
            const sentimentText = sentiment === 'positive' ? 'Positive Feedback' : (sentiment === 'negative' ? 'Needs Improvement' : 'Neutral Feedback');
            
            modalBody.innerHTML = `
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-qrcode"></i> Feedback ID:</div>
                        <div class="detail-value"><strong>#${feedback.id}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-user"></i> Customer:</div>
                        <div class="detail-value">${escapeHtml(feedback.full_name || feedback.username || 'Guest')}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-envelope"></i> Email:</div>
                        <div class="detail-value">${escapeHtml(feedback.email || 'N/A')}</div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-box"></i> Product:</div>
                        <div class="detail-value"><strong>${escapeHtml(feedback.product_name || 'N/A')}</strong></div>
                    </div>
                    ${feedback.product_price ? `
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-tag"></i> Product Price:</div>
                        <div class="detail-value">₱${parseFloat(feedback.product_price).toFixed(2)}</div>
                    </div>
                    ` : ''}
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-star"></i> Rating:</div>
                        <div class="detail-value">
                            <div class="rating-large">
                                <div class="rating-stars" style="font-size: 28px;">
                                    ${generateStars(feedback.rating)}
                                </div>
                                <span style="font-weight: bold;">${feedback.rating}/5</span>
                            </div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-chart-line"></i> Sentiment:</div>
                        <div class="detail-value">
                            <span class="sentiment-badge-large" style="background: ${sentimentClass}; color: ${sentimentColor};">
                                <i class="fas fa-${sentimentIcon} fa-lg"></i> ${sentimentText}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-comment"></i> Feedback:</div>
                        <div class="detail-value">
                            <div class="feedback-full">
                                <i class="fas fa-quote-left" style="color: var(--accent-blue); margin-right: 8px; font-size: 18px;"></i>
                                ${escapeHtml(feedback.comment || 'No comment provided')}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-calendar"></i> Submitted:</div>
                        <div class="detail-value">${formatDateTime(feedback.created_at)}</div>
                    </div>
                </div>
            `;
            
            document.getElementById('feedbackModal').style.display = 'block';
        }
        
        // Helper functions
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }
        
        function formatTime(timeString) {
            if (!timeString) return 'N/A';
            const date = new Date(`1970-01-01T${timeString}`);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }
        
        function formatDateTime(dateTimeString) {
            if (!dateTimeString) return 'N/A';
            const date = new Date(dateTimeString);
            return date.toLocaleString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function getSentimentFromRating(rating) {
            if (rating >= 4) return 'positive';
            if (rating >= 2.5) return 'neutral';
            return 'negative';
        }
        
        function generateStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) stars += '★';
                else stars += '☆';
            }
            return stars;
        }
        
        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['appointmentModal', 'feedbackModal', 'orderEditModal', 'orderViewModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    closeModal(modalId);
                }
            });
        }
        
        // Update appointment status
        function updateAppointmentStatus(appointmentId, status) {
            if(confirm('Update appointment status to ' + status + '?')) {
                fetch('update_appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + appointmentId + '&status=' + status
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Status updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Error updating status'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating status');
                });
            }
        }
        
        // Delete product function
        function deleteProduct(productId, productName) {
            if(confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                fetch('delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert(data.message || 'Product deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Error deleting product'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting product');
                });
            }
        }
        
        // Chart.js implementation
        function loadCharts() {
            // Monthly Orders and Revenue Chart
            const monthlyData = <?php echo json_encode($monthly_orders); ?>;
            const months = monthlyData.map(item => item.month);
            const orderCounts = monthlyData.map(item => item.count);
            const revenues = monthlyData.map(item => item.revenue || 0);
            
            const ctx1 = document.getElementById('monthlyChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: 'Number of Orders',
                                data: orderCounts,
                                borderColor: '#0A3D62',
                                backgroundColor: 'rgba(10, 61, 98, 0.1)',
                                yAxisID: 'y',
                                tension: 0.4
                            },
                            {
                                label: 'Revenue (₱)',
                                data: revenues,
                                borderColor: '#27ae60',
                                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                                yAxisID: 'y1',
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.dataset.label.includes('Revenue')) {
                                            label += '₱' + context.raw.toFixed(2);
                                        } else {
                                            label += context.raw;
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Number of Orders'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Revenue (₱)'
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                            }
                        }
                    }
                });
            }
            
            // Rating Distribution Chart
            const ratingData = <?php echo json_encode($rating_distribution); ?>;
            const ratings = ratingData.map(item => item.rating + ' Stars');
            const ratingCounts = ratingData.map(item => item.count);
            
            const ctx2 = document.getElementById('ratingChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: ratings,
                        datasets: [{
                            label: 'Number of Feedbacks',
                            data: ratingCounts,
                            backgroundColor: '#00A8E8',
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Feedbacks'
                                }
                            }
                        }
                    }
                });
            }
            
            // Sentiment Analysis Chart
            const sentimentData = <?php echo json_encode($sentiment_summary); ?>;
            const ctx3 = document.getElementById('sentimentChart');
            if (ctx3) {
                new Chart(ctx3, {
                    type: 'doughnut',
                    data: {
                        labels: ['Positive', 'Neutral', 'Negative'],
                        datasets: [{
                            data: [sentimentData.positive, sentimentData.neutral, sentimentData.negative],
                            backgroundColor: ['#27ae60', '#f39c12', '#e74c3c'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
            
            // Weekly Feedback Trend
            const weeklyData = <?php echo json_encode($weekly_feedback); ?>;
            const weekDays = weeklyData.map(item => formatDate(item.date));
            const weeklyCounts = weeklyData.map(item => item.count);
            const weeklyRatings = weeklyData.map(item => item.avg_rating || 0);
            
            const ctx4 = document.getElementById('weeklyTrendChart');
            if (ctx4) {
                new Chart(ctx4, {
                    type: 'line',
                    data: {
                        labels: weekDays,
                        datasets: [
                            {
                                label: 'Number of Feedbacks',
                                data: weeklyCounts,
                                borderColor: '#0A3D62',
                                backgroundColor: 'rgba(10, 61, 98, 0.1)',
                                yAxisID: 'y',
                                tension: 0.4
                            },
                            {
                                label: 'Average Rating',
                                data: weeklyRatings,
                                borderColor: '#FFA41C',
                                backgroundColor: 'rgba(255, 164, 28, 0.1)',
                                yAxisID: 'y1',
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Number of Feedbacks'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Average Rating'
                                },
                                min: 0,
                                max: 5,
                                grid: {
                                    drawOnChartArea: false,
                                }
                            }
                        }
                    }
                });
            }
            
            // Order Status Distribution
            const orderStatusData = <?php echo json_encode($order_status_dist); ?>;
            const orderStatusLabels = orderStatusData.map(item => item.status);
            const orderStatusCounts = orderStatusData.map(item => item.count);
            
            const ctx5 = document.getElementById('orderStatusChart');
            if (ctx5) {
                new Chart(ctx5, {
                    type: 'pie',
                    data: {
                        labels: orderStatusLabels,
                        datasets: [{
                            data: orderStatusCounts,
                            backgroundColor: ['#f39c12', '#3498db', '#27ae60', '#2ecc71', '#e74c3c'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
        }
        
        // Load charts if analytics section is visible on page load
        if (document.getElementById('analytics').classList.contains('active')) {
            setTimeout(loadCharts, 100);
        }
    </script>
</body>
</html>