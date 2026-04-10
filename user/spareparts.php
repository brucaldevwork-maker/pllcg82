<?php
// File: C:\xampp\htdocs\pllcg82\user\spareparts.php

require_once '../config/config.php';

// Check if user is logged in
$user_name = "User";
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT username, full_name FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch();
        if ($user_data) {
            $user_name = $user_data['full_name'] ?: $user_data['username'];
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
}

// Get cart count
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $cart_count = $result['total'] ?? 0;
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
} elseif (isset($_SESSION['session_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
        $stmt->execute([$_SESSION['session_id']]);
        $result = $stmt->fetch();
        $cart_count = $result['total'] ?? 0;
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
}

// Get filter parameters
$subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? floatval($_GET['max_price']) : 999999;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Validate and sanitize price range
if ($min_price < 0) $min_price = 0;
if ($max_price < 0) $max_price = 999999;
if ($min_price > $max_price) {
    $temp = $min_price;
    $min_price = $max_price;
    $max_price = $temp;
}

// Build query for Spare Parts only
$where_conditions = ["is_available = 1", "category = 'spareparts'"];
$params = [];

// Subcategory filter (spare part type)
if (!empty($subcategory) && $subcategory != 'all') {
    $where_conditions[] = "subcategory = ?";
    $params[] = $subcategory;
}

// Price filter
if ($min_price > 0) {
    $where_conditions[] = "price >= ?";
    $params[] = $min_price;
}
if ($max_price < 999999) {
    $where_conditions[] = "price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(" AND ", $where_conditions);

// Sorting with whitelist validation for security
$allowed_sort_options = ['featured', 'price-low', 'price-high', 'newest', 'rating'];
if (!in_array($sort, $allowed_sort_options)) {
    $sort = 'featured';
}

switch ($sort) {
    case 'price-low':
        $order_by = "price ASC";
        break;
    case 'price-high':
        $order_by = "price DESC";
        break;
    case 'newest':
        $order_by = "id DESC";
        break;
    case 'rating':
        $order_by = "rating DESC, reviews DESC";
        break;
    default:
        $order_by = "rating DESC, reviews DESC";
}

// Get total products count for pagination
$count_sql = "SELECT COUNT(*) as total FROM products WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetch()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$sql = "SELECT * FROM products WHERE $where_clause ORDER BY $order_by LIMIT $offset, $per_page";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all unique subcategories (spare part types) for spare parts
$subcategory_stmt = $pdo->query("SELECT DISTINCT subcategory, COUNT(*) as count FROM products WHERE category = 'spareparts' AND is_available = 1 GROUP BY subcategory ORDER BY subcategory");
$subcategories = $subcategory_stmt->fetchAll();

// Get price statistics for spare parts
$price_stats = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price, AVG(price) as avg_price FROM products WHERE category = 'spareparts' AND is_available = 1")->fetch();

// ========== SEO META TAGS ==========
$page_title = "Genuine E-Bike Spare Parts | Replacement Components | PLLC Enterprise";
$page_description = "Shop genuine e-bike spare parts including tires, brake systems, motor controllers, LED lights, and accessories. Quality replacement parts with warranty. Free shipping on orders over ₱10,000!";
$page_keywords = "e-bike spare parts, e-bike tires, e-bike brakes, motor controller, e-bike accessories, PLLC Enterprise, electric bike parts";

if (!empty($subcategory) && $subcategory != 'all') {
    $subcategory_name = ucfirst(str_replace('-', ' ', $subcategory));
    $page_title = "$subcategory_name Parts | Genuine E-Bike Components | PLLC Enterprise";
    $page_description = "Shop genuine $subcategory_name for your electric bike. High-quality replacement parts from PLLC Enterprise with warranty included.";
}

// Include navbar
include_once 'components/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="index, follow">
    <meta name="author" content="PLLC Enterprise">
    
    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:image" content="https://yourdomain.com/image/og-spareparts.jpg">
    <meta property="og:url" content="https://yourdomain.com/user/spareparts.php">
    <meta property="og:site_name" content="PLLC Enterprise">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://yourdomain.com/user/spareparts.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>">
    
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
            --star-gold: #FFA41C;
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

        .products-page {
            background: var(--white);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(10, 61, 98, 0.08);
        }

        .breadcrumb {
            color: var(--text-light);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: var(--accent-blue);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb span {
            color: var(--text-dark);
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .filter-tag {
            background: var(--bg-light);
            border: 1px solid var(--accent-blue);
            color: var(--accent-blue);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .filter-tag a {
            color: var(--danger);
            text-decoration: none;
            font-weight: bold;
        }

        .filter-tag a:hover {
            color: var(--accent-blue);
        }

        .products-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
        }

        /* Filters Sidebar - Trusted Tech */
        .filters-sidebar {
            background: var(--bg-light);
            padding: 24px;
            border-radius: 20px;
            position: sticky;
            top: 100px;
            height: fit-content;
            border: 1px solid rgba(10, 61, 98, 0.1);
        }

        .filters-sidebar h3 {
            margin: 20px 0 15px;
            color: var(--primary-blue);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .filters-sidebar h3:first-child {
            margin-top: 0;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .filter-option {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .filter-option:hover {
            background: rgba(0, 168, 232, 0.1);
        }

        .filter-option input[type="checkbox"],
        .filter-option input[type="radio"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: var(--accent-blue);
            flex-shrink: 0;
        }

        .filter-option span {
            color: var(--text-dark);
            font-size: 14px;
        }

        .filter-option .count {
            color: var(--text-light);
            font-size: 12px;
            margin-left: auto;
            flex-shrink: 0;
        }

        /* Price Range */
        .price-range-container {
            margin-top: 10px;
        }

        .price-range-inputs {
            display: flex;
            gap: 12px;
            align-items: center;
            width: 100%;
        }

        .price-range-inputs input {
            flex: 1;
            min-width: 0;
            padding: 10px 12px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .price-range-inputs span {
            color: var(--text-light);
            font-weight: 500;
            flex-shrink: 0;
        }

        .price-range-inputs input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 168, 232, 0.1);
        }

        .apply-filter-btn {
            width: 100%;
            margin-top: 15px;
            padding: 12px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .apply-filter-btn:hover {
            background: #0080c0;
            transform: translateY(-2px);
        }

        .clear-filters {
            text-align: center;
            margin-top: 15px;
        }

        .clear-filters a {
            color: var(--accent-blue);
            text-decoration: none;
            font-size: 13px;
        }

        .clear-filters a:hover {
            text-decoration: underline;
        }

        /* Products Content */
        .products-content {
            min-height: 500px;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eef2f6;
            flex-wrap: wrap;
            gap: 15px;
        }

        .products-header h1 {
            font-size: 1.8rem;
            color: var(--primary-blue);
            font-weight: 700;
        }

        .products-header h1 i {
            color: var(--accent-blue);
            margin-right: 10px;
        }

        .products-count {
            color: var(--text-light);
            font-size: 14px;
        }

        .sort-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sort-options label {
            color: var(--text-light);
            font-size: 14px;
        }

        .sort-options select {
            padding: 8px 12px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            background: var(--white);
            transition: all 0.3s;
        }

        .sort-options select:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            position: relative;
            border: 1px solid #eef2f6;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(10, 61, 98, 0.12);
            border-color: rgba(0, 168, 232, 0.3);
        }

        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--accent-blue);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            z-index: 1;
        }

        .product-badge.limited {
            background: var(--warning);
        }

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .product-card:hover .product-image {
            transform: scale(1.03);
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 1rem;
            margin: 0 0 10px;
            color: var(--text-dark);
            font-weight: 600;
            line-height: 1.4;
            min-height: 45px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-rating {
            color: var(--star-gold);
            margin: 8px 0;
            font-size: 13px;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin: 10px 0;
        }

        .product-stock {
            font-size: 12px;
            margin-bottom: 15px;
        }

        /* Fixed Button Styles */
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            width: 100%;
        }

        .btn-view-details {
            background: var(--primary-blue);
            border: none;
            padding: 12px;
            border-radius: 30px;
            cursor: pointer;
            flex: 1;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 14px;
        }

        .btn-view-details:hover {
            background: var(--dark-blue);
            transform: translateY(-2px);
        }

        .btn-add-cart {
            background: var(--accent-blue);
            border: none;
            padding: 12px;
            border-radius: 30px;
            cursor: pointer;
            flex: 1;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
            width: auto;
        }

        .btn-add-cart:hover {
            background: #0080c0;
            transform: translateY(-2px);
        }

        .btn-add-cart:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
        }

        /* Fix for form inside product-actions */
        .product-actions form {
            flex: 1;
            margin: 0;
        }

        .product-actions form button {
            width: 100%;
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
            padding: 10px 15px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            text-decoration: none;
            color: var(--accent-blue);
            transition: all 0.3s;
            font-weight: 500;
        }

        .pagination a:hover {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .pagination .active {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .no-products i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--accent-blue);
            opacity: 0.5;
        }

        .no-products h3 {
            color: var(--primary-blue);
            margin-bottom: 10px;
        }

        /* Category Icons */
        .category-icon {
            font-size: 18px;
            margin-right: 10px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .products-layout {
                grid-template-columns: 1fr;
            }
            .filters-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0 15px;
                margin: 20px auto;
            }
            .products-page {
                padding: 20px;
            }
            .products-header h1 {
                font-size: 1.5rem;
            }
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 20px;
            }
            .products-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .price-range-inputs {
                gap: 8px;
            }
            .price-range-inputs input {
                padding: 10px 8px;
                font-size: 13px;
            }
            .product-actions {
                flex-direction: column;
            }
            .btn-view-details, .btn-add-cart {
                width: 100%;
            }
            .product-actions form {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            .price-range-inputs {
                gap: 6px;
            }
            .price-range-inputs input {
                padding: 8px 6px;
                font-size: 12px;
            }
            .filters-sidebar {
                padding: 16px;
            }
        }
    </style>
    
    <!-- JSON-LD Structured Data for SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "<?php echo htmlspecialchars($page_title); ?>",
        "description": "<?php echo htmlspecialchars($page_description); ?>",
        "numberOfItems": "<?php echo $total_products; ?>",
        "itemListElement": [
            <?php 
            $item_count = 0;
            foreach($products as $product):
                $item_count++;
                $image_url = !empty($product['image']) ? "https://yourdomain.com/" . $product['image'] : "https://yourdomain.com/image/placeholder-sparepart.jpg";
            ?>
            {
                "@type": "ListItem",
                "position": <?php echo $item_count; ?>,
                "url": "https://yourdomain.com/user/product-details.php?id=<?php echo $product['id']; ?>",
                "name": "<?php echo htmlspecialchars(addslashes($product['name'])); ?>",
                "image": "<?php echo htmlspecialchars($image_url); ?>",
                "offers": {
                    "@type": "Offer",
                    "price": "<?php echo $product['price']; ?>",
                    "priceCurrency": "PHP",
                    "availability": "https://schema.org/<?php echo $product['stock'] > 0 ? 'InStock' : 'OutOfStock'; ?>"
                }
            }<?php echo $item_count < count($products) ? ',' : ''; ?>
            <?php endforeach; ?>
        ]
    }
    </script>
</head>
<body>
    <!-- Navbar is included from components/navbar.php -->

    <!-- Main Content -->
    <main class="main-content">
        <div class="products-page">
            <div class="breadcrumb">
                <a href="dashboard.php"><i class="fas fa-home"></i> Home</a> > 
                <a href="products.php">Products</a> > 
                <span>Spare Parts</span>
                <?php if(!empty($subcategory) && $subcategory != 'all'): ?> > <span><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $subcategory))); ?></span><?php endif; ?>
            </div>
            
            <!-- Active Filters Display -->
            <?php if(!empty($subcategory) || $min_price > 0 || $max_price < 999999): ?>
            <div class="active-filters">
                <?php if(!empty($subcategory) && $subcategory != 'all'): ?>
                <span class="filter-tag">
                    <i class="fas fa-filter"></i> Category: 
                    <?php 
                        if($subcategory == 'tires') echo "Tires & Wheels";
                        elseif($subcategory == 'brakes') echo "Brake System";
                        elseif($subcategory == 'motor') echo "Motor & Controller";
                        elseif($subcategory == 'lighting') echo "Lighting";
                        elseif($subcategory == 'accessories') echo "Accessories";
                        else echo ucfirst(str_replace('-', ' ', $subcategory));
                    ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['subcategory' => '', 'page' => 1])); ?>" class="remove-filter">&times;</a>
                </span>
                <?php endif; ?>
                
                <?php if($min_price > 0 || $max_price < 999999): ?>
                <span class="filter-tag">
                    <i class="fas fa-tag"></i> 
                    <?php 
                    if($min_price > 0 && $max_price < 999999) echo "₱" . number_format($min_price) . " - ₱" . number_format($max_price);
                    elseif($min_price > 0) echo "≥ ₱" . number_format($min_price);
                    elseif($max_price < 999999) echo "≤ ₱" . number_format($max_price);
                    ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['min_price' => '', 'max_price' => '', 'page' => 1])); ?>" class="remove-filter">&times;</a>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="products-layout">
                <!-- Sidebar Filters -->
                <aside class="filters-sidebar">
                    <h3><i class="fas fa-filter"></i> Filter by Category</h3>
                    <div class="filter-group" id="typeFilters">
                        <label class="filter-option">
                            <input type="radio" name="type_radio" value="all" class="type-radio" <?php echo empty($subcategory) ? 'checked' : ''; ?>>
                            <span>All Parts</span>
                            <span class="count">(<?php echo $total_products; ?>)</span>
                        </label>
                        <?php foreach($subcategories as $cat): ?>
                        <label class="filter-option">
                            <input type="radio" name="type_radio" value="<?php echo htmlspecialchars($cat['subcategory']); ?>" class="type-radio" <?php echo $subcategory == $cat['subcategory'] ? 'checked' : ''; ?>>
                            <span>
                                <i class="fas 
                                    <?php 
                                    if($cat['subcategory'] == 'tires') echo 'fa-circle';
                                    elseif($cat['subcategory'] == 'brakes') echo 'fa-stop';
                                    elseif($cat['subcategory'] == 'motor') echo 'fa-microchip';
                                    elseif($cat['subcategory'] == 'lighting') echo 'fa-lightbulb';
                                    elseif($cat['subcategory'] == 'accessories') echo 'fa-puzzle-piece';
                                    else echo 'fa-cog';
                                    ?> category-icon"></i>
                                <?php 
                                    $type_name = ucfirst(str_replace('-', ' ', $cat['subcategory']));
                                    if($cat['subcategory'] == 'tires') echo "Tires & Wheels";
                                    elseif($cat['subcategory'] == 'brakes') echo "Brake System";
                                    elseif($cat['subcategory'] == 'motor') echo "Motor & Controller";
                                    elseif($cat['subcategory'] == 'lighting') echo "Lighting";
                                    elseif($cat['subcategory'] == 'accessories') echo "Accessories";
                                    else echo $type_name;
                                ?>
                            </span>
                            <span class="count">(<?php echo $cat['count']; ?>)</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <h3><i class="fas fa-tag"></i> Price Range</h3>
                    <form method="GET" id="priceFilterForm" class="price-filter-form">
                        <?php 
                        // Preserve all existing GET parameters except price and page
                        $preserve_params = $_GET;
                        unset($preserve_params['min_price']);
                        unset($preserve_params['max_price']);
                        unset($preserve_params['page']);
                        foreach($preserve_params as $key => $value):
                            if(is_array($value)) {
                                foreach($value as $val):
                        ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>[]" value="<?php echo htmlspecialchars($val); ?>">
                        <?php 
                                endforeach;
                            } else {
                        ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                        <?php 
                            }
                        endforeach; 
                        ?>
                        <div class="price-range-container">
                            <div class="price-range-inputs">
                                <input type="number" name="min_price" id="min_price" placeholder="Min ₱" value="<?php echo $min_price > 0 ? $min_price : ''; ?>" step="1" min="0">
                                <span>-</span>
                                <input type="number" name="max_price" id="max_price" placeholder="Max ₱" value="<?php echo $max_price < 999999 ? $max_price : ''; ?>" step="1" min="0">
                            </div>
                            <button type="submit" class="apply-filter-btn">Apply Price Filter</button>
                        </div>
                    </form>
                    
                    <div class="clear-filters">
                        <a href="spareparts.php"><i class="fas fa-times-circle"></i> Clear All Filters</a>
                    </div>
                </aside>
                
                <!-- Products Grid -->
                <div class="products-content">
                    <div class="products-header">
                        <div>
                            <h1><i class="fas fa-tools"></i> <?php 
                                if(!empty($subcategory) && $subcategory != 'all') {
                                    $type_name = ucfirst(str_replace('-', ' ', $subcategory));
                                    if($subcategory == 'tires') echo "Tires & Wheels";
                                    elseif($subcategory == 'brakes') echo "Brake System";
                                    elseif($subcategory == 'motor') echo "Motor & Controller";
                                    elseif($subcategory == 'lighting') echo "Lighting";
                                    elseif($subcategory == 'accessories') echo "Accessories";
                                    else echo $type_name;
                                } else {
                                    echo "All Spare Parts";
                                }
                            ?></h1>
                            <div class="products-count">
                                <i class="fas fa-box"></i> Showing <?php echo count($products); ?> of <?php echo $total_products; ?> replacement parts
                            </div>
                        </div>
                        <div class="sort-options">
                            <label for="sortSelect"><i class="fas fa-sort"></i> Sort by:</label>
                            <select id="sortSelect" onchange="window.location.href=this.value">
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'featured', 'page' => 1])); ?>" <?php echo $sort == 'featured' ? 'selected' : ''; ?>>Featured</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price-low', 'page' => 1])); ?>" <?php echo $sort == 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price-high', 'page' => 1])); ?>" <?php echo $sort == 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'rating', 'page' => 1])); ?>" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Customer Rating</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="products-grid">
                        <?php if(count($products) > 0): ?>
                            <?php foreach($products as $product): ?>
                                <div class="product-card" itemscope itemtype="https://schema.org/Product">
                                    <?php if($product['rating'] >= 4.8): ?>
                                        <div class="product-badge"><i class="fas fa-trophy"></i> Top Rated</div>
                                    <?php elseif($product['stock'] <= $product['min_stock'] && $product['stock'] > 0): ?>
                                        <div class="product-badge limited"><i class="fas fa-exclamation-triangle"></i> Limited Stock!</div>
                                    <?php endif; ?>
                                    
                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image"
                                         itemprop="image"
                                         loading="lazy"
                                         onerror="this.src='https://via.placeholder.com/300x200?text=Spare+Part'">
                                    <div class="product-info">
                                        <h3 class="product-title" itemprop="name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <div class="product-rating" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                                            <?php 
                                            $rating = round($product['rating'], 1);
                                            for($i = 1; $i <= 5; $i++) {
                                                if($i <= $rating) {
                                                    echo "<i class='fas fa-star'></i>";
                                                } elseif($i - 0.5 <= $rating) {
                                                    echo "<i class='fas fa-star-half-alt'></i>";
                                                } else {
                                                    echo "<i class='far fa-star'></i>";
                                                }
                                            }
                                            echo " (<span itemprop='ratingValue'>$rating</span>/5, <span itemprop='reviewCount'>{$product['reviews']}</span> reviews)";
                                            ?>
                                        </div>
                                        <div class="product-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                            <span itemprop="price">₱<?php echo number_format($product['price'], 2); ?></span>
                                            <meta itemprop="priceCurrency" content="PHP">
                                            <meta itemprop="availability" content="https://schema.org/<?php echo $product['stock'] > 0 ? 'InStock' : 'OutOfStock'; ?>">
                                        </div>
                                        <div class="product-stock">
                                            <?php 
                                            if($product['stock'] > 10) {
                                                echo "<span style='color: var(--success);'><i class='fas fa-check-circle'></i> In Stock</span>";
                                            } elseif($product['stock'] > 0) {
                                                echo "<span style='color: var(--warning);'><i class='fas fa-exclamation-circle'></i> Only {$product['stock']} left!</span>";
                                            } else {
                                                echo "<span style='color: var(--danger);'><i class='fas fa-times-circle'></i> Out of Stock</span>";
                                            }
                                            ?>
                                        </div>
                                  <div class="product-actions">
                                          
                                            <?php if($product['stock'] > 0): ?>
                                            <form action="../auth/add_to_cart.php" method="POST">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn-add-cart">
                                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button class="btn-add-cart" disabled>
                                                <i class="fas fa-times-circle"></i> Out of Stock
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-products">
                                <i class="fas fa-tools"></i>
                                <h3>No spare parts found</h3>
                                <p>Try adjusting your filters or check back later for new parts</p>
                                <a href="spareparts.php" class="apply-filter-btn" style="display: inline-block; width: auto; padding: 10px 20px; margin-top: 20px;">Clear Filters</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                        <?php endif; ?>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if($start_page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                            <?php if($start_page > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if($end_page < $total_pages): ?>
                            <?php if($end_page < $total_pages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a>
                        <?php endif; ?>
                        
                        <?php if($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next <i class="fas fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    // Type filter handling with radio buttons
    document.querySelectorAll('.type-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            if(this.checked) {
                let url = new URL(window.location.href);
                if(this.value !== 'all') {
                    url.searchParams.set('subcategory', this.value);
                } else {
                    url.searchParams.delete('subcategory');
                }
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
        });
    });
    
    // Price filter validation
    const priceForm = document.getElementById('priceFilterForm');
    if(priceForm) {
        priceForm.addEventListener('submit', function(e) {
            const minPrice = document.getElementById('min_price');
            const maxPrice = document.getElementById('max_price');
            
            if(minPrice.value && parseFloat(minPrice.value) < 0) {
                e.preventDefault();
                alert('Minimum price cannot be negative');
                minPrice.value = '';
                return false;
            }
            
            if(maxPrice.value && parseFloat(maxPrice.value) < 0) {
                e.preventDefault();
                alert('Maximum price cannot be negative');
                maxPrice.value = '';
                return false;
            }
            
            if(minPrice.value && maxPrice.value && parseFloat(minPrice.value) > parseFloat(maxPrice.value)) {
                e.preventDefault();
                alert('Minimum price cannot be greater than maximum price');
                return false;
            }
            
            return true;
        });
    }
    
    // Lazy loading images
    if('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('.product-image').forEach(img => {
            imageObserver.observe(img);
        });
    }
    </script>
</body>
</html>