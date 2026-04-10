<?php
// File: C:\xampp\htdocs\pllcg82\user\dashboard.php

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

// Get statistics
$total_products = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_available = 1");
    $total_products = $stmt->fetch()['total'];
} catch(PDOException $e) {
    error_log($e->getMessage());
}

// Include navbar
include_once 'components/navbar.php';
?>

<!-- Main Content Styles - Trusted Tech Colors -->
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

    /* Hero Banner - Trusted Tech Style */
    .hero-section {
        position: relative;
        margin-bottom: 50px;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(10, 61, 98, 0.15);
    }

    .hero-banner {
        position: relative;
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        padding: 80px 60px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .hero-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(0,168,232,0.1) 1px, transparent 1px);
        background-size: 50px 50px;
        animation: movePattern 20s linear infinite;
    }

    @keyframes movePattern {
        from {
            transform: translate(0, 0);
        }
        to {
            transform: translate(50px, 50px);
        }
    }

    .hero-banner h1 {
        font-size: 3rem;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
        animation: slideUp 0.8s ease;
        font-weight: 700;
    }

    .hero-banner p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        position: relative;
        z-index: 1;
        animation: slideUp 1s ease;
        opacity: 0.95;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-stats {
        display: flex;
        justify-content: center;
        gap: 50px;
        margin-top: 40px;
        position: relative;
        z-index: 1;
        flex-wrap: wrap;
    }

    .stat-item {
        text-align: center;
        animation: fadeIn 1.2s ease;
    }

    .stat-number {
        font-size: 2.2rem;
        font-weight: bold;
        display: block;
        color: var(--accent-blue);
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .cta-button {
        display: inline-block;
        background: var(--accent-blue);
        color: white;
        padding: 14px 40px;
        text-decoration: none;
        border-radius: 50px;
        font-weight: bold;
        transition: all 0.3s;
        position: relative;
        z-index: 1;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .cta-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,168,232,0.4);
        background: #0080c0;
    }

    /* Section Styles */
    .section-container {
        margin-bottom: 60px;
    }

    .section-container h2 {
        font-size: 1.8rem;
        margin-bottom: 30px;
        color: var(--primary-blue);
        position: relative;
        display: inline-block;
        font-weight: 600;
    }

    .section-container h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 60px;
        height: 3px;
        background: var(--accent-blue);
        border-radius: 2px;
    }

    .section-container h2 i {
        color: var(--accent-blue);
        margin-right: 10px;
    }

    /* Categories Grid */
    .categories-grid, .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 20px;
    }

    .category-card, .service-card {
        background: var(--white);
        padding: 0;
        text-align: center;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .category-card:hover, .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(10, 61, 98, 0.15);
    }

    .category-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        border-radius: 20px 20px 0 0;
    }

    .category-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .category-card:hover .category-image img {
        transform: scale(1.05);
    }

    .category-card h3, .service-card h3 {
        margin: 20px 0 10px;
        color: var(--primary-blue);
        font-size: 1.3rem;
        font-weight: 600;
    }

    .category-card p, .service-card p {
        color: var(--text-light);
        padding: 0 20px;
    }

    .category-card small, .service-card small {
        display: block;
        padding: 15px 20px 20px;
        color: var(--accent-blue);
        font-weight: 500;
    }

    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 20px;
    }

    .product-card {
        background: var(--white);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(10, 61, 98, 0.12);
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
        margin: 0 0 10px 0;
        color: var(--text-dark);
        font-weight: 600;
        line-height: 1.4;
        height: 2.8em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .product-rating {
        color: #FFA41C;
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

    .btn-add-cart {
        background: var(--accent-blue);
        border: none;
        padding: 12px 20px;
        border-radius: 30px;
        cursor: pointer;
        width: 100%;
        font-weight: 600;
        color: white;
        transition: all 0.3s;
    }

    .btn-add-cart:hover {
        background: #0080c0;
        transform: translateY(-2px);
    }

    /* Newsletter Section - Trusted Tech */
    .newsletter-section {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        border-radius: 24px;
        padding: 60px 40px;
        text-align: center;
        color: white;
        margin: 60px 0;
    }

    .newsletter-section h3 {
        font-size: 2rem;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .newsletter-section h3 i {
        color: var(--accent-blue);
        margin-right: 10px;
    }

    .newsletter-form {
        max-width: 500px;
        margin: 30px auto 0;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .newsletter-input {
        flex: 1;
        padding: 15px 20px;
        border: none;
        border-radius: 50px;
        font-size: 16px;
        outline: none;
    }

    .newsletter-input:focus {
        box-shadow: 0 0 0 2px var(--accent-blue);
    }

    .newsletter-btn {
        padding: 15px 30px;
        background: var(--accent-blue);
        border: none;
        border-radius: 50px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        color: white;
    }

    .newsletter-btn:hover {
        background: #0080c0;
        transform: translateY(-2px);
    }

    /* Footer - Trusted Tech */
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
        color: rgba(255,255,255,0.8);
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
        border-top: 1px solid rgba(255,255,255,0.1);
        font-size: 14px;
        color: rgba(255,255,255,0.7);
    }

    .footer-bottom i {
        color: var(--accent-blue);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .hero-banner {
            padding: 50px 30px;
        }
        .hero-banner h1 {
            font-size: 2.2rem;
        }
    }

    @media (max-width: 768px) {
        .hero-banner {
            padding: 40px 20px;
        }
        .hero-banner h1 {
            font-size: 1.8rem;
        }
        .hero-banner p {
            font-size: 1rem;
        }
        .hero-stats {
            gap: 25px;
        }
        .stat-number {
            font-size: 1.5rem;
        }
        .newsletter-section {
            padding: 40px 20px;
        }
        .newsletter-section h3 {
            font-size: 1.5rem;
        }
        .newsletter-form {
            flex-direction: column;
        }
        .newsletter-input, .newsletter-btn {
            width: 100%;
        }
        .section-container h2 {
            font-size: 1.5rem;
        }
        .categories-grid, .services-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 0 15px;
            margin: 20px auto;
        }
        .hero-stats {
            gap: 15px;
        }
        .stat-number {
            font-size: 1.2rem;
        }
        .stat-label {
            font-size: 0.7rem;
        }
        .cta-button {
            padding: 10px 25px;
            font-size: 0.9rem;
        }
    }

    /* Animation Keyframes */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
</style>

<!-- Main Content -->
<main class="main-content">
    <div class="dashboard-container">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-banner">
                <h1>Power Long-lasting Legendary Cruising</h1>
                <p>Experience the future of mobility with PLLC's premium electric bicycles</p>
                <a href="products.php" class="cta-button">
                    <i class="fas fa-shopping-bag"></i> Shop Now & Save 15%
                </a>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">5000+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $total_products; ?>+</span>
                        <span class="stat-label">Premium Products</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">4.8</span>
                        <span class="stat-label">Average Rating</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Customer Support</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Categories -->
        <section class="categories-section">
            <div class="section-container">
                <h2><i class="fas fa-th-large"></i> Shop by Category</h2>
                <div class="categories-grid">
                    <div class="category-card" onclick="location.href='ebikes.php'">
                        <div class="category-image">
                            <img src="../image/adjustable.jpg" alt="Premium Electric Bikes" onerror="this.src='https://via.placeholder.com/300x200?text=E-Bikes'">
                        </div>
                        <h3><i class="fas fa-bicycle"></i> E-Bikes</h3>
                        <p>Premium electric bicycles for every terrain</p>
                        <small>24 models available <i class="fas fa-arrow-right"></i></small>
                    </div>
                    
                    <div class="category-card" onclick="location.href='battery.php'">
                        <div class="category-image">
                            <img src="../image/battery.avif" alt="High-Performance Batteries" onerror="this.src='https://via.placeholder.com/300x200?text=Batteries'">
                        </div>
                        <h3><i class="fas fa-battery-full"></i> Batteries</h3>
                        <p>Long-lasting power solutions</p>
                        <small>Up to 100km range <i class="fas fa-arrow-right"></i></small>
                    </div>
                    
                    <div class="category-card" onclick="location.href='spareparts.php'">
                        <div class="category-image">
                            <img src="../image/spareparts.jpg" alt="Quality Spare Parts" onerror="this.src='https://via.placeholder.com/300x200?text=Spare+Parts'">
                        </div>
                        <h3><i class="fas fa-tools"></i> Spare Parts</h3>
                        <p>Quality replacement parts</p>
                        <small>Genuine OEM parts <i class="fas fa-arrow-right"></i></small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="featured-products">
            <div class="section-container">
                <h2><i class="fas fa-star"></i> Featured Products</h2>
                <div class="products-grid">
                    <?php
                    // Fetch featured products (limit to 8 products)
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_available = 1 AND stock > 0 ORDER BY rating DESC, reviews DESC LIMIT 8");
                        $stmt->execute();
                        $products = $stmt->fetchAll();
                        
                        if (count($products) > 0) {
                            foreach ($products as $product) {
                                $image_path = !empty($product['image']) ? "../" . $product['image'] : "https://via.placeholder.com/300x200?text=" . urlencode($product['name']);
                                ?>
                                <div class="product-card">
                                    <?php if($product['rating'] >= 4.8): ?>
                                        <div class="product-badge"><i class="fas fa-trophy"></i> Bestseller</div>
                                    <?php endif; ?>
                                    <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image"
                                         onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                                    <div class="product-info">
                                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <div class="product-rating">
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
                                            echo " (" . $product['reviews'] . " reviews)";
                                            ?>
                                        </div>
                                        <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
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
                                        <?php if($product['stock'] > 0): ?>
                                        <form action="../auth/add_to_cart.php" method="POST">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn-add-cart">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<p>No products found.</p>";
                        }
                    } catch(PDOException $e) {
                        error_log($e->getMessage());
                        echo "<p>Unable to load products at this time.</p>";
                    }
                    ?>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <h3><i class="fas fa-envelope"></i> Stay Updated!</h3>
            <p>Subscribe to our newsletter and get 10% off your first purchase + exclusive offers!</p>
            <form class="newsletter-form" method="POST" action="../auth/subscribe.php">
                <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
                <button type="submit" class="newsletter-btn">Subscribe Now <i class="fas fa-paper-plane"></i></button>
            </form>
        </section>

        <!-- Service Section -->
        <section class="service-section">
            <div class="section-container">
                <h2><i class="fas fa-concierge-bell"></i> Our Services</h2>
                <div class="services-grid">
                    <div class="service-card" onclick="location.href='appointment.php'">
                        <i class="fas fa-wrench" style="font-size: 48px; color: var(--accent-blue);"></i>
                        <h3>Service & Repair</h3>
                        <p>Professional maintenance and repair services by certified technicians</p>
                        <small>Book now <i class="fas fa-arrow-right"></i></small>
                    </div>
                    <div class="service-card" onclick="location.href='feedback.php'">
                        <i class="fas fa-comments" style="font-size: 48px; color: var(--accent-blue);"></i>
                        <h3>Customer Support</h3>
                        <p>24/7 customer service and feedback</p>
                        <small>Contact us <i class="fas fa-arrow-right"></i></small>
                    </div>
                    <div class="service-card" onclick="location.href='warranty.php'">
                        <i class="fas fa-shield-alt" style="font-size: 48px; color: var(--accent-blue);"></i>
                        <h3>Warranty Registration</h3>
                        <p>Register your product for extended warranty coverage</p>
                        <small>Register now <i class="fas fa-arrow-right"></i></small>
                    </div>
                </div>
            </div>
        </section>
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
</body>
</html>