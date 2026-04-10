<?php
// File: C:\xampp\htdocs\pllcg82\admin\view_order.php

require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../auth/admin_login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$order_id = intval($_GET['id']);

// Fetch order details
try {
    $stmt = $pdo->prepare("SELECT o.*, u.username, u.full_name, u.email, u.phone, u.address 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: admin_dashboard.php');
        exit;
    }
    
    // Fetch order items
    $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image, p.sku 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
    // Calculate order summary
    $subtotal = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $order_items));
    
    $shipping_fee = 50.00; // Example shipping fee
    $tax = $subtotal * 0.12; // 12% VAT
    $total = $subtotal + $shipping_fee + $tax;
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = 'Error fetching order details';
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            
            $order['status'] = $new_status;
            $success = 'Order status updated successfully!';
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = 'Error updating order status';
        }
    }
}

// Helper function for status styling
function getStatusStyle($status) {
    $styles = [
        'pending' => ['bg' => '#FEF3C7', 'color' => '#92400E', 'icon' => 'fa-clock', 'text' => 'Pending'],
        'processing' => ['bg' => '#DBEAFE', 'color' => '#1E40AF', 'icon' => 'fa-spinner', 'text' => 'Processing'],
        'shipped' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'icon' => 'fa-truck', 'text' => 'Shipped'],
        'delivered' => ['bg' => '#E0E7FF', 'color' => '#3730A3', 'icon' => 'fa-check-circle', 'text' => 'Delivered'],
        'cancelled' => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'icon' => 'fa-times-circle', 'text' => 'Cancelled']
    ];
    return $styles[$status] ?? $styles['pending'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['order_number']; ?> - Admin Panel</title>
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
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
            --purple: #8B5CF6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 26px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.3s;
        }

        .logo i {
            color: var(--accent-blue);
            font-size: 28px;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .back-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 24px;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            transform: translateY(-2px);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 30px;
        }

        .breadcrumb a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover {
            color: var(--accent-blue);
        }

        .breadcrumb i {
            margin: 0 8px;
            font-size: 12px;
            color: var(--text-light);
        }

        /* Order Card */
        .order-card {
            background: var(--white);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .order-card:hover {
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
        }

        /* Order Header */
        .order-header {
            background: linear-gradient(135deg, #F8F9FA 0%, #FFFFFF 100%);
            padding: 30px 35px;
            border-bottom: 2px solid rgba(0,168,232,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .order-title h1 {
            color: var(--primary-blue);
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .order-title h1 i {
            color: var(--accent-blue);
            margin-right: 12px;
        }

        .order-date {
            color: var(--text-light);
            font-size: 14px;
        }

        .order-date i {
            margin-right: 5px;
        }

        .status-badge-large {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Alert Messages */
        .alert {
            padding: 16px 24px;
            border-radius: 16px;
            margin: 20px 35px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            color: #065F46;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            color: #991B1B;
            border-left: 4px solid var(--danger);
        }

        .alert i {
            font-size: 22px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            padding: 35px;
        }

        .info-card {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s;
            border: 1px solid rgba(0,168,232,0.1);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-color: var(--accent-blue);
        }

        .info-card h3 {
            color: var(--primary-blue);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h3 i {
            color: var(--accent-blue);
            font-size: 20px;
        }

        .info-card p {
            margin: 12px 0;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .info-card strong {
            color: var(--primary-blue);
            font-weight: 600;
        }

        /* Status Form */
        .status-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .status-select {
            padding: 10px 15px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            margin-right: 12px;
            font-family: inherit;
            font-weight: 500;
            transition: all 0.3s;
        }

        .status-select:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .btn-update {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #0080c0 100%);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-family: inherit;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,168,232,0.3);
        }

        /* Order Items Section */
        .order-items-section {
            padding: 0 35px 35px 35px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--accent-blue);
        }

        .items-table-container {
            overflow-x: auto;
            border-radius: 20px;
            background: var(--bg-light);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 16px 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .items-table td {
            padding: 20px;
            border-bottom: 1px solid #E5E7EB;
            vertical-align: middle;
        }

        .items-table tr:hover {
            background: rgba(0,168,232,0.05);
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .product-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Order Summary */
        .order-summary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            border-radius: 20px;
            padding: 25px;
            margin-top: 30px;
            color: white;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-total {
            font-size: 20px;
            font-weight: 800;
            color: var(--accent-blue);
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        /* Print Button */
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-blue);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .print-btn:hover {
            transform: scale(1.1);
            background: var(--accent-blue);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }
            
            .order-header {
                padding: 20px;
                flex-direction: column;
                text-align: center;
            }
            
            .info-grid {
                padding: 20px;
                grid-template-columns: 1fr;
            }
            
            .order-items-section {
                padding: 0 20px 20px 20px;
            }
            
            .items-table th,
            .items-table td {
                padding: 12px;
            }
            
            .product-image {
                width: 40px;
                height: 40px;
            }
        }

        @media print {
            .header, .back-btn, .status-form, .print-btn, .breadcrumb {
                display: none;
            }
            
            .order-card {
                box-shadow: none;
            }
            
            .info-card:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <?php $statusStyle = getStatusStyle($order['status']); ?>
    
    <div class="header">
        <div class="header-content">
            <a href="admin_dashboard.php" class="logo">
                <i class="fas fa-store"></i> PLLC Admin
            </a>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <div class="breadcrumb">
            <a href="admin_dashboard.php">Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="orders.php">Orders</a>
            <i class="fas fa-chevron-right"></i>
            <span>Order #<?php echo $order['order_number']; ?></span>
        </div>

        <div class="order-card">
            <div class="order-header">
                <div class="order-title">
                    <h1>
                        <i class="fas fa-receipt"></i> 
                        Order #<?php echo htmlspecialchars($order['order_number']); ?>
                    </h1>
                    <div class="order-date">
                        <i class="fas fa-calendar-alt"></i> 
                        Placed on <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?>
                    </div>
                </div>
                <div class="status-badge-large" style="background: <?php echo $statusStyle['bg']; ?>; color: <?php echo $statusStyle['color']; ?>;">
                    <i class="fas <?php echo $statusStyle['icon']; ?>"></i>
                    <?php echo $statusStyle['text']; ?>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-user-circle"></i> Customer Information</h3>
                    <p><strong><i class="fas fa-user"></i> Name:</strong> <?php echo htmlspecialchars($order['full_name'] ?? $order['username'] ?? 'Guest'); ?></p>
                    <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></p>
                    <p><strong><i class="fas fa-phone"></i> Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-map-marked-alt"></i> Shipping Address</h3>
                    <p><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'No address provided')); ?></p>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-credit-card"></i> Payment Information</h3>
                    <p><strong><i class="fas fa-money-bill-wave"></i> Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'Cash on Delivery'); ?></p>
                    <p><strong><i class="fas fa-receipt"></i> Order Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                    
                    <form method="POST" class="status-form">
                        <label><strong><i class="fas fa-sync-alt"></i> Update Status:</strong></label><br>
                        <select name="status" class="status-select">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>⚙️ Processing</option>
                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>🚚 Shipped</option>
                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>✅ Delivered</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-update">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>

            <div class="order-items-section">
                <div class="section-title">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Order Items</span>
                </div>
                
                <div class="items-table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($order_items)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-box-open" style="font-size: 48px; color: #CBD5E1;"></i>
                                        <p style="margin-top: 10px; color: var(--text-light);">No items found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="product-info">
                                                <img src="../<?php echo htmlspecialchars($item['image'] ?? 'image/placeholder.jpg'); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                     class="product-image"
                                                     onerror="this.src='../image/placeholder.jpg'">
                                                <div>
                                                    <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                                        <td><strong>₱<?php echo number_format($item['price'], 2); ?></strong></td>
                                        <td><span class="quantity-badge">x<?php echo $item['quantity']; ?></span></td>
                                        <td><strong style="color: var(--accent-blue);">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping Fee</span>
                        <span>₱<?php echo number_format($shipping_fee, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (12% VAT)</span>
                        <span>₱<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="summary-total">
                        <span><strong>Total Amount</strong></span>
                        <span><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i>
    </div>

    <style>
        .quantity-badge {
            display: inline-block;
            background: var(--accent-blue);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
</body>
</html>