<?php
// File: C:\xampp\htdocs\pllcg82\user\transaction-history.php

require_once '../config/config.php';

// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$user_name = "User";
$user_id = null;

if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: ../auth/login.php");
    exit;
}

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

// Get cart count
$cart_count = 0;
try {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $cart_count = $result['total'] ?? 0;
} catch(PDOException $e) {
    error_log($e->getMessage());
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query for orders
$where_conditions = ["user_id = ?"];
$params = [$user_id];

// Status filter
if (!empty($status_filter) && $status_filter != 'all') {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Sorting
switch ($sort) {
    case 'oldest':
        $order_by = "created_at ASC";
        break;
    case 'amount-high':
        $order_by = "total_amount DESC";
        break;
    case 'amount-low':
        $order_by = "total_amount ASC";
        break;
    default:
        $order_by = "created_at DESC";
}

// Get total orders count for pagination
$count_sql = "SELECT COUNT(*) as total FROM orders WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_orders = $count_stmt->fetch()['total'];
$total_pages = ceil($total_orders / $per_page);

// Get orders
$sql = "SELECT * FROM orders WHERE $where_clause ORDER BY $order_by LIMIT $offset, $per_page";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order statistics
$stats_sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_spent,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders
              FROM orders WHERE user_id = ?";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute([$user_id]);
$stats = $stats_stmt->fetch();

// Include navbar
include_once 'components/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, follow">
    
    <title>Order History | PLLC Enterprise</title>
    <meta name="description" content="View your order history and track your purchases.">
    
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
            --info: #3498db;
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

        .history-container {
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(10, 61, 98, 0.1);
        }

        .stat-card i {
            font-size: 32px;
            color: var(--accent-blue);
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: var(--primary-blue);
        }

        .stat-card .stat-label {
            font-size: 14px;
            color: var(--text-light);
            margin-top: 5px;
        }

        /* Filters */
        .filters-bar {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .filters-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .filter-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e0e4e8;
            border-radius: 8px;
            font-size: 14px;
            background: var(--white);
        }

        .filter-group select:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .btn-filter {
            padding: 10px 20px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-reset {
            padding: 10px 20px;
            background: var(--text-light);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eef2f6;
        }

        .orders-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--primary-blue);
        }

        .orders-table tr:hover {
            background: rgba(0, 168, 232, 0.02);
        }

        .order-number {
            font-weight: 600;
            color: var(--accent-blue);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-shipped {
            background: #cce5ff;
            color: #004085;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        /* Order Items Section (for expanded view) */
        .order-items {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 15px;
            margin-top: 15px;
            display: none;
        }

        .order-items.show {
            display: block;
        }

        .order-item {
            display: flex;
            gap: 15px;
            padding: 10px;
            background: var(--white);
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .order-item:last-child {
            margin-bottom: 0;
        }

        .order-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .order-item-price {
            font-size: 13px;
            color: var(--primary-blue);
        }

        .order-item-quantity {
            font-size: 12px;
            color: var(--text-light);
        }

        .btn-toggle-items {
            background: none;
            border: none;
            color: var(--accent-blue);
            cursor: pointer;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .btn-toggle-items:hover {
            background: rgba(0, 168, 232, 0.1);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #e0e4e8;
            border-radius: 8px;
            text-decoration: none;
            color: var(--accent-blue);
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: var(--accent-blue);
            color: white;
        }

        .pagination .active {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .no-orders {
            text-align: center;
            padding: 60px 20px;
        }

        .no-orders i {
            font-size: 64px;
            color: var(--accent-blue);
            opacity: 0.5;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 0 15px;
                margin: 20px auto;
            }
            .history-container {
                padding: 20px;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .filters-form {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .filter-actions {
                width: 100%;
            }
            .orders-table {
                display: block;
                overflow-x: auto;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Navbar is included from components/navbar.php -->

    <!-- Main Content -->
    <main class="main-content">
        <div class="history-container">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-history"></i> Order History</h1>
                <p class="page-subtitle">View and track all your orders</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-peso-sign"></i>
                    <div class="stat-value">₱<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-value"><?php echo $stats['completed_orders'] ?? 0; ?></div>
                    <div class="stat-label">Completed Orders</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div class="stat-value"><?php echo $stats['pending_orders'] ?? 0; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters-bar">
                <form method="GET" action="" class="filters-form">
                    <div class="filter-group">
                        <label>Filter by Status</label>
                        <select name="status">
                            <option value="all" <?php echo $status_filter == '' || $status_filter == 'all' ? 'selected' : ''; ?>>All Orders</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Sort by</label>
                        <select name="sort">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="amount-high" <?php echo $sort == 'amount-high' ? 'selected' : ''; ?>>Highest Amount</option>
                            <option value="amount-low" <?php echo $sort == 'amount-low' ? 'selected' : ''; ?>>Lowest Amount</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">Apply Filters</button>
                        <a href="transaction-history.php" class="btn-reset">Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Orders Table -->
            <?php if (count($orders) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                                    <td>
                                        <button class="btn-toggle-items" onclick="toggleItems(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-box"></i> View Items
                                        </button>
                                    </td>
                                </tr>
                                <tr class="order-items-row" id="items-row-<?php echo $order['id']; ?>" style="display: none;">
                                    <td colspan="6" style="padding: 0;">
                                        <div class="order-items" id="order-items-<?php echo $order['id']; ?>">
                                            <div style="text-align: center; padding: 20px;">
                                                <i class="fas fa-spinner fa-spin"></i> Loading items...
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                    <?php endif; ?>
                    
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                        <?php if ($start_page > 2): ?>
                            <span>...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span>...</span>
                        <?php endif; ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No orders found</h3>
                    <p>You haven't placed any orders yet.</p>
                    <a href="products.php" class="btn-filter" style="display: inline-block; margin-top: 20px; text-decoration: none;">
                        <i class="fas fa-shopping-cart"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
    // Store loaded items to avoid multiple requests
    let loadedItems = {};
    
    function toggleItems(orderId) {
        const itemsRow = document.getElementById(`items-row-${orderId}`);
        const button = event.target.closest('.btn-toggle-items');
        
        if (itemsRow.style.display === 'none' || itemsRow.style.display === '') {
            // Show items row
            itemsRow.style.display = 'table-row';
            button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Items';
            
            // Load items if not already loaded
            if (!loadedItems[orderId]) {
                loadOrderItems(orderId);
            }
        } else {
            // Hide items row
            itemsRow.style.display = 'none';
            button.innerHTML = '<i class="fas fa-box"></i> View Items';
        }
    }
    
    function loadOrderItems(orderId) {
        const container = document.getElementById(`order-items-${orderId}`);
        
        fetch(`get_order_items.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items.length > 0) {
                    let itemsHtml = '';
                    data.items.forEach(item => {
                        itemsHtml += `
                            <div class="order-item">
                                <img src="../${item.image}" alt="${item.name}" class="order-item-image" onerror="this.src='https://via.placeholder.com/60x60?text=Product'">
                                <div class="order-item-details">
                                    <div class="order-item-title">${item.name}</div>
                                    <div class="order-item-price">₱${item.price}</div>
                                    <div class="order-item-quantity">Quantity: ${item.quantity}</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: bold;">₱${(item.price * item.quantity).toFixed(2)}</div>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = itemsHtml;
                    loadedItems[orderId] = true;
                } else {
                    container.innerHTML = '<div style="text-align: center; padding: 20px; color: #6c757d;">No items found for this order.</div>';
                }
            })
            .catch(error => {
                container.innerHTML = '<div style="text-align: center; padding: 20px; color: #e74c3c;">Error loading items. Please try again.</div>';
            });
    }
    </script>
</body>
</html>