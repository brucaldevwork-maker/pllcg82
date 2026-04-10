<?php
// File: C:\xampp\htdocs\pllcg82\user\products.php

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
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
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

// Build query
$where_conditions = ["is_available = 1"];
$params = [];

// Category filter
if (!empty($category) && $category != 'all') {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

// Search filter
if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
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

// Get categories for filter
$category_stmt = $pdo->query("SELECT DISTINCT category, COUNT(*) as count FROM products WHERE is_available = 1 GROUP BY category ORDER BY category");
$categories = $category_stmt->fetchAll();

// Get price ranges for statistics
$price_stats = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price, AVG(price) as avg_price FROM products WHERE is_available = 1")->fetch();

// Build query string for preserving filters
$query_params = $_GET;
unset($query_params['page']); // Remove page from query params for filter links
$base_query_string = http_build_query($query_params);
$filter_query_string = !empty($base_query_string) ? '&' . $base_query_string : '';

// ========== SEO META TAGS ==========
$page_title = "Premium E-Bikes, Batteries & Spare Parts | PLLC Enterprise";
$page_description = "Shop the best electric bicycles, high-performance batteries, and genuine spare parts in the Philippines. Free shipping on orders over ₱10,000. Up to 30% off on selected e-bikes!";
$page_keywords = "e-bikes, electric bicycles, e-bike batteries, e-bike spare parts, electric bike Philippines, PLLC Enterprise, electric bicycle shop, e-bike accessories";

// Dynamic SEO based on filters
if (!empty($search)) {
    $page_title = "Search Results for \"$search\" | PLLC Enterprise";
    $page_description = "Find \"$search\" in our collection of premium e-bikes, batteries, and spare parts. Shop now and enjoy free shipping!";
}
if (!empty($category) && $category != 'all') {
    $page_title = ucfirst($category) . " | Premium Electric Bikes & Parts | PLLC Enterprise";
    $page_description = "Browse our collection of premium " . strtolower($category) . ". High-quality electric bicycles, batteries, and accessories with warranty included.";
}
if ($min_price > 0 || $max_price < 999999) {
    $price_range = "";
    if ($min_price > 0) $price_range .= "₱" . number_format($min_price);
    if ($min_price > 0 && $max_price < 999999) $price_range .= " - ";
    if ($max_price < 999999) $price_range .= "₱" . number_format($max_price);
    if (!empty($price_range)) {
        $page_title = "Products $price_range | PLLC Enterprise";
        $page_description = "Shop e-bikes and accessories priced $price_range. Quality electric bicycles with warranty and free shipping available.";
    }
}
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
    <meta property="og:image" content="https://yourdomain.com/image/og-image.jpg">
    <meta property="og:url" content="https://yourdomain.com/user/products.php">
    <meta property="og:site_name" content="PLLC Enterprise">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="twitter:image" content="https://yourdomain.com/image/twitter-card.jpg">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://yourdomain.com/user/products.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>">
    
    <!-- Preconnect for faster loading -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    
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

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eef2f6;
        }

        .breadcrumb {
            color: var(--text-light);
            margin-bottom: 10px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: var(--accent-blue);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
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
        }

        .filter-option span {
            color: var(--text-dark);
            font-size: 14px;
        }

        .filter-option .count {
            color: var(--text-light);
            font-size: 12px;
            margin-left: auto;
        }

        .price-range-inputs {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .price-range-inputs input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            font-size: 13px;
            transition: all 0.3s;
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

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 60px 0 20px;
            margin-top: 60px;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-section h4 {
            margin-bottom: 20px;
            font-size: 1.2rem;
            position: relative;
            display: inline-block;
        }

        .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--accent-blue);
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .footer-section a:hover {
            color: var(--accent-blue);
            transform: translateX(5px);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            font-size: 24px;
            display: inline-block;
        }

        .social-links a:hover {
            transform: translateY(-3px);
            color: var(--accent-blue);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 40px;
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-bottom i {
            color: var(--accent-blue);
        }

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
            .page-title {
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
        }
        
        /* Price Range Styles - Fixed Layout */
        .price-range-inputs {
            display: flex;
            gap: 12px;
            margin-top: 10px;
            align-items: center;
            flex-wrap: nowrap;
        }

        .price-range-inputs input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s;
            min-width: 0;
        }

        .price-range-inputs span {
            color: var(--text-light);
            font-weight: 500;
        }

        .price-range-inputs input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 168, 232, 0.1);
        }

        /* Mobile Responsive Fix */
        @media (max-width: 768px) {
            .price-range-inputs {
                gap: 8px;
            }
            
            .price-range-inputs input {
                padding: 10px 8px;
                font-size: 13px;
            }
            
            .price-range-inputs span {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .price-range-inputs {
                gap: 6px;
            }
            
            .price-range-inputs input {
                padding: 8px 6px;
                font-size: 12px;
            }
            
            .price-range-inputs span {
                font-size: 12px;
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
                $image_url = !empty($product['image']) ? "https://yourdomain.com/" . $product['image'] : "https://yourdomain.com/image/placeholder.jpg";
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
    <?php include_once 'components/navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="products-page">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="dashboard.php"><i class="fas fa-home"></i> Home</a> > 
                    <a href="products.php">Products</a>
                    <?php if(!empty($category) && $category != 'all'): ?> > <span><?php echo htmlspecialchars(ucfirst($category)); ?></span><?php endif; ?>
                    <?php if(!empty($search)): ?> > <span>Search: "<?php echo htmlspecialchars($search); ?>"</span><?php endif; ?>
                </div>
                <h1 class="page-title"><i class="fas fa-store"></i> <?php 
                    if(!empty($category) && $category != 'all') echo htmlspecialchars(ucfirst($category));
                    elseif(!empty($search)) echo "Search Results";
                    else echo "All Products";
                ?></h1>
                <p class="page-subtitle"><?php 
                    if(!empty($category) && $category != 'all') echo "Browse our collection of premium " . htmlspecialchars(strtolower($category)) . " for your electric bike needs.";
                    elseif(!empty($search)) echo "Showing results for \"" . htmlspecialchars($search) . "\"";
                    else echo "Discover our complete range of premium e-bikes, high-performance batteries, and genuine spare parts.";
                ?></p>
                
                <!-- Active Filters Display -->
                <?php if(!empty($category) || !empty($search) || $min_price > 0 || $max_price < 999999): ?>
                <div class="active-filters">
                    <?php if(!empty($category) && $category != 'all'): ?>
                    <span class="filter-tag">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars(ucfirst($category)); ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => '', 'page' => 1])); ?>" class="remove-filter">&times;</a>
                    </span>
                    <?php endif; ?>
                    
                    <?php if(!empty($search)): ?>
                    <span class="filter-tag">
                        <i class="fas fa-search"></i> "<?php echo htmlspecialchars($search); ?>"
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['search' => '', 'page' => 1])); ?>" class="remove-filter">&times;</a>
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
            </div>
            
            <div class="products-layout">
                <!-- Sidebar Filters -->
                <aside class="filters-sidebar">
                    <h3><i class="fas fa-filter"></i> Categories</h3>
                    <div class="filter-group" id="categoryFilters">
                        <label class="filter-option">
                            <input type="radio" name="category_radio" value="all" class="category-radio" <?php echo empty($category) ? 'checked' : ''; ?>>
                            <span>All Categories</span>
                            <span class="count">(<?php echo $total_products; ?>)</span>
                        </label>
                        <?php foreach($categories as $cat): ?>
                        <label class="filter-option">
                            <input type="radio" name="category_radio" value="<?php echo htmlspecialchars($cat['category']); ?>" class="category-radio" <?php echo $category == $cat['category'] ? 'checked' : ''; ?>>
                            <span><?php echo ucfirst(htmlspecialchars($cat['category'])); ?></span>
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
                        <div class="price-range-inputs">
                            <input type="number" name="min_price" id="min_price" placeholder="Min ₱" value="<?php echo $min_price > 0 ? $min_price : ''; ?>" step="1" min="0">
                            <span>-</span>
                            <input type="number" name="max_price" id="max_price" placeholder="Max ₱" value="<?php echo $max_price < 999999 ? $max_price : ''; ?>" step="1" min="0">
                        </div>
                        <button type="submit" class="apply-filter-btn">Apply Price Filter</button>
                    </form>
                    
                    <div class="clear-filters">
                        <a href="products.php"><i class="fas fa-times-circle"></i> Clear All Filters</a>
                    </div>
                </aside>
                
                <!-- Products Grid -->
                <div class="products-content">
                    <div class="products-header">
                        <div class="products-count">
                            <i class="fas fa-box"></i> Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products
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
                                        <div class="product-badge"><i class="fas fa-trophy"></i> Bestseller</div>
                                    <?php elseif($product['stock'] <= $product['min_stock'] && $product['stock'] > 0): ?>
                                        <div class="product-badge limited" style="background: var(--warning);"><i class="fas fa-exclamation-triangle"></i> Limited Stock!</div>
                                    <?php endif; ?>
                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image"
                                         itemprop="image"
                                         loading="lazy"
                                         onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
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
                           
                                            </a>
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
                                <i class="fas fa-search"></i>
                                <h3>No products found</h3>
                                <p>Try adjusting your filters or search criteria</p>
                                <a href="products.php" class="apply-filter-btn" style="display: inline-block; width: auto; padding: 10px 20px; margin-top: 20px;">Clear Filters</a>
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

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4><i class="fas fa-bicycle"></i> PLLC Enterprise</h4>
                <p>Power Long-lasting Legendary Cruising</p>
                <p style="margin-top: 15px;">Premium electric bicycles and accessories for the modern rider.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact Us</a>
                <a href="faq.php">FAQs</a>
                <a href="shipping.php">Shipping Policy</a>
                <a href="returns.php">Returns & Refunds</a>
            </div>
            <div class="footer-section">
                <h4>Contact Info</h4>
                <p><i class="fas fa-phone"></i> +63 (2) 1234 5678</p>
                <p><i class="fas fa-envelope"></i> support@pllc-enterprise.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Manila, Philippines</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 PLLC Enterprise. All rights reserved. | Designed with <i class="fas fa-heart"></i> for electric mobility</p>
        </div>
    </footer>

    <script>
    // Category filter handling with radio buttons
    document.querySelectorAll('.category-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            if(this.checked) {
                let url = new URL(window.location.href);
                if(this.value !== 'all') {
                    url.searchParams.set('category', this.value);
                } else {
                    url.searchParams.delete('category');
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