<?php
// File: C:\xampp\htdocs\pllcg82\user\components\navbar.php

// This file expects $user_name and $cart_count to be defined before including
if (!isset($user_name)) {
    $user_name = "User";
}
if (!isset($cart_count)) {
    $cart_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Modern Header - Trusted Tech Colors */
        .amazon-header {
            background: linear-gradient(135deg, #0A3D62 0%, #062c48 100%);
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
            flex-wrap: wrap;
            gap: 10px;
        }

        .header-main {
            padding: 15px 0;
        }

        .header-main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        /* Enhanced Logo Section */
        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            position: relative;
        }

        .logo-img {
            max-width: 45px;
            height: auto;
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.2));
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .logo-text .brand {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, #00A8E8, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .logo-text .tagline {
            font-size: 9px;
            color: rgba(255,255,255,0.7);
            letter-spacing: 0.5px;
        }

        .logo-container:hover .logo-img {
            transform: scale(1.05) rotate(2deg);
            filter: drop-shadow(0 4px 10px rgba(0,168,232,0.4));
        }

        .logo-container:hover .brand {
            background: linear-gradient(135deg, #ffffff, #00A8E8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Optional: Add a subtle badge */
        .logo-badge {
            position: absolute;
            bottom: -5px;
            right: -15px;
            background: #00A8E8;
            color: white;
            font-size: 8px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 20px;
            white-space: nowrap;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.05);
                opacity: 1;
            }
        }

        .search-container {
            flex: 1;
            max-width: 600px;
            display: flex;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            font-size: 15px;
        }

        .search-input:focus {
            outline: none;
        }

        .search-btn {
            padding: 12px 25px;
            background: #00A8E8;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
        }

        .search-btn:hover {
            background: #0080c0;
        }

        /* ========== FIXED HEADER-RIGHT SECTION ========== */
        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-left: auto;
        }

        /* Cart Icon */
        .cart-icon {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s;
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
        }

        .cart-icon:hover {
            background: rgba(0,168,232,0.3);
            transform: translateY(-2px);
        }

        .cart-icon i {
            font-size: 18px;
        }

        .cart-icon span:not(.cart-count) {
            font-size: 14px;
            font-weight: 500;
        }

        .cart-count {
            background: #00A8E8;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
        }

        /* User Menu */
        .user-menu {
            position: relative;
        }

        .user-dropdown-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-dropdown-btn i {
            font-size: 18px;
        }

        .user-dropdown-btn:hover {
            background: rgba(0,168,232,0.3);
            transform: translateY(-2px);
        }

        .user-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            min-width: 240px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border-radius: 8px;
            z-index: 1000;
            overflow: hidden;
            margin-top: 5px;
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            font-size: 14px;
        }

        .user-dropdown a i {
            width: 20px;
            color: #00A8E8;
        }

        .user-dropdown a:hover {
            background: #0A3D62;
            color: white;
            padding-left: 25px;
        }

        .user-dropdown a:hover i {
            color: white;
        }

        .user-menu:hover .user-dropdown {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Desktop Navigation */
        .nav-menu {
            background: rgba(0,0,0,0.3);
            padding: 12px 0;
            backdrop-filter: blur(10px);
        }

        .nav-menu-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: rgba(0,168,232,0.3);
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: #00A8E8;
        }

        /* ========== HAMBURGER MENU STYLES ========== */
        .hamburger {
            display: none;
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 10px 12px;
            border-radius: 8px;
            transition: all 0.3s;
            z-index: 1001;
        }

        .hamburger:hover {
            background: rgba(0,168,232,0.3);
            color: #00A8E8;
        }

        /* Mobile Navigation */
        .mobile-nav {
            display: block;
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: #0A3D62;
            z-index: 2000 !important;
            transition: left 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
            overflow-y: auto;
        }

        .mobile-nav.active {
            left: 0 !important;
        }

        .mobile-nav-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-nav-header .mobile-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .mobile-nav-header .mobile-logo-img {
            max-width: 35px;
            height: auto;
        }

        .mobile-nav-header .mobile-logo-text {
            font-size: 18px;
            font-weight: 800;
            background: linear-gradient(135deg, #00A8E8, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .close-menu {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
        }

        .close-menu:hover {
            color: #00A8E8;
        }

        .mobile-nav-links {
            padding: 20px 0;
        }

        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .mobile-nav-link i {
            width: 25px;
        }

        .mobile-nav-link:hover {
            background: rgba(0,168,232,0.2);
            border-left-color: #00A8E8;
            padding-left: 25px;
        }

        .mobile-nav-link.active {
            background: rgba(0,168,232,0.3);
            border-left-color: #00A8E8;
        }

        .mobile-user-section {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 20px;
        }

        .mobile-user-name {
            padding: 10px 0;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mobile-user-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .mobile-user-links a:hover {
            color: #00A8E8;
            padding-left: 5px;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1500 !important;
        }

        .overlay.active {
            display: block !important;
        }

        /* ========== RESPONSIVE DESIGN ========== */
        @media (max-width: 992px) {
            .search-container {
                max-width: 400px;
            }
            
            .logo-img {
                max-width: 40px;
            }
            
            .logo-text .brand {
                font-size: 18px;
            }
            
            .header-right {
                gap: 10px;
            }
            
            .cart-icon {
                padding: 8px 12px;
            }
            
            .user-dropdown-btn {
                padding: 8px 12px;
            }
        }

        @media (max-width: 768px) {
            .header-top-content {
                flex-direction: column;
                text-align: center;
                font-size: 11px;
                gap: 5px;
            }
            
            .header-main-content {
                flex-wrap: wrap;
                position: relative;
            }
            
            .logo-container {
                order: 2;
                flex: 1;
                justify-content: center;
            }
            
            .logo-img {
                max-width: 35px;
            }
            
            .logo-text .brand {
                font-size: 16px;
            }
            
            .logo-text .tagline {
                font-size: 8px;
            }
            
            .hamburger {
                display: flex;
                order: 1;
                align-items: center;
                justify-content: center;
            }
            
            .header-right {
                order: 3;
                margin-left: 0;
                gap: 8px;
            }
            
            .search-container {
                order: 4;
                max-width: 100%;
                width: 100%;
                margin-top: 12px;
            }
            
            /* Compact header-right items */
            .cart-icon {
                padding: 8px 10px;
            }
            
            .cart-icon span:not(.cart-count) {
                display: none;
            }
            
            .user-dropdown-btn span {
                display: none;
            }
            
            .user-dropdown-btn {
                padding: 8px 10px;
            }
            
            .cart-icon i, 
            .user-dropdown-btn i {
                font-size: 18px;
                margin: 0;
            }
            
            /* Hide desktop navigation */
            .nav-menu {
                display: none;
            }
            
            .logo-badge {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .header-main-content {
                padding: 0 15px;
            }
            
            .logo-img {
                max-width: 30px;
            }
            
            .logo-text .brand {
                font-size: 14px;
            }
            
            .cart-icon, 
            .user-dropdown-btn {
                padding: 6px 8px;
            }
            
            .search-input {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .search-btn {
                padding: 10px 15px;
            }
            
            .header-top-content {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <header class="amazon-header">
        <div class="header-top">
            <div class="header-top-content">
                <span><i class="fas fa-truck"></i> Free Shipping on Orders Over ₱10,000</span>
                <span><i class="fas fa-headset"></i> 24/7 Customer Support</span>
                <span><i class="fas fa-tag"></i> Up to 30% Off on Selected E-Bikes</span>
            </div>
        </div>
        
        <div class="header-main">
            <div class="header-main-content">
                <!-- Hamburger Menu Button (Mobile Only) -->
                <button class="hamburger" id="hamburgerBtn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Enhanced Logo with Image -->
                <a href="dashboard.php" class="logo-container">
                    <img src="../logo.png" alt="PLLC Enterprise" class="logo-img">
                    <div class="logo-text">
                        <span class="brand">PLLC Enterprise</span>
                        <span class="tagline">Power Long-lasting Legendary Cruising</span>
                    </div>
                </a>
                
                <div class="search-container">
                    <form action="products.php" method="GET" style="display: flex; width: 100%;">
                        <input type="text" name="search" class="search-input" placeholder="Search e-bikes, batteries, parts..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="search-btn" aria-label="Search"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <!-- FIXED HEADER-RIGHT SECTION -->
                <div class="header-right">
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <span class="cart-count" id="cartCount"><?php echo $cart_count; ?></span>
                    </a>
                    
                    <div class="user-menu">
                        <button class="user-dropdown-btn" aria-label="User Menu">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($user_name); ?></span>
                            <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                        </button>
                        <div class="user-dropdown">
                            <a href="account.php"><i class="fas fa-user"></i> Your Account</a>
                            <a href="transaction-history.php"><i class="fas fa-history"></i> Order History</a>
                            <a href="appointment.php"><i class="fas fa-calendar-alt"></i> Book Service</a>
                            <a href="feedback.php"><i class="fas fa-comment"></i> Feedback</a>
                            <a href="../auth/admin_login.php"><i class="fas fa-shield-alt"></i> Admin Panel</a>
                            <div style="height: 1px; background: #e0e4e8; margin: 5px 0;"></div>
                            <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Desktop Navigation -->
        <nav class="nav-menu">
            <div class="nav-menu-content">
                <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> All Products
                </a>
                <a href="ebikes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ebikes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bicycle"></i> E-Bikes
                </a>
                <a href="battery.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'battery.php' ? 'active' : ''; ?>">
                    <i class="fas fa-battery-full"></i> Batteries
                </a>
                <a href="spareparts.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'spareparts.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tools"></i> Spare Parts
                </a>
                <a href="appointment.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointment.php' ? 'active' : ''; ?>">
                    <i class="fas fa-wrench"></i> Service Center
                </a>
            </div>
        </nav>
    </header>

    <!-- Mobile Navigation (Hamburger Menu) -->
    <div class="overlay" id="overlay"></div>
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <a href="dashboard.php" class="mobile-logo">
                <img src="../../logo.png" alt="PLLC Enterprise" class="mobile-logo-img">
                <span class="mobile-logo-text">PLLC</span>
            </a>
            <button class="close-menu" id="closeMenuBtn" aria-label="Close Menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mobile-nav-links">
            <a href="dashboard.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="products.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All Products
            </a>
            <a href="ebikes.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ebikes.php' ? 'active' : ''; ?>">
                <i class="fas fa-bicycle"></i> E-Bikes
            </a>
            <a href="battery.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'battery.php' ? 'active' : ''; ?>">
                <i class="fas fa-battery-full"></i> Batteries
            </a>
            <a href="spareparts.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'spareparts.php' ? 'active' : ''; ?>">
                <i class="fas fa-tools"></i> Spare Parts
            </a>
            <a href="appointment.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointment.php' ? 'active' : ''; ?>">
                <i class="fas fa-wrench"></i> Service Center
            </a>
        </div>
        <div class="mobile-user-section">
            <div class="mobile-user-name">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user_name); ?>
            </div>
            <div class="mobile-user-links">
                <a href="account.php"><i class="fas fa-user"></i> Your Account</a>
                <a href="transaction-history.php"><i class="fas fa-history"></i> Order History</a>
                <a href="appointment.php"><i class="fas fa-calendar-alt"></i> Book Service</a>
                <a href="feedback.php"><i class="fas fa-comment"></i> Feedback</a>
                <a href="../auth/admin_login.php"><i class="fas fa-shield-alt"></i> Admin Panel</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
            </div>
        </div>
    </div>

    <script>
    // Mobile Menu Functions
    function openMobileMenu() {
        const mobileNav = document.getElementById('mobileNav');
        const overlay = document.getElementById('overlay');
        
        if (mobileNav) {
            mobileNav.classList.add('active');
        }
        
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeMobileMenu() {
        const mobileNav = document.getElementById('mobileNav');
        const overlay = document.getElementById('overlay');
        
        if (mobileNav) {
            mobileNav.classList.remove('active');
        }
        
        if (overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    // Make functions globally accessible
    window.openMobileMenu = openMobileMenu;
    window.closeMobileMenu = closeMobileMenu;
    
    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const closeMenuBtn = document.getElementById('closeMenuBtn');
        const overlay = document.getElementById('overlay');
        
        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', openMobileMenu);
        }
        
        if (closeMenuBtn) {
            closeMenuBtn.addEventListener('click', closeMobileMenu);
        }
        
        if (overlay) {
            overlay.addEventListener('click', closeMobileMenu);
        }
        
        // Close menu when clicking on mobile links
        const mobileLinks = document.querySelectorAll('.mobile-nav-link, .mobile-user-links a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });
        
        // Prevent body scroll when menu is open on touch devices
        document.addEventListener('touchmove', function(e) {
            if (document.getElementById('mobileNav').classList.contains('active')) {
                e.preventDefault();
            }
        }, { passive: false });
    });
    
    // Update cart count periodically
    function updateCartCount() {
        fetch('../auth/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.count !== undefined) {
                    const cartCountElement = document.getElementById('cartCount');
                    if (cartCountElement) {
                        cartCountElement.innerText = data.count;
                    }
                }
            })
            .catch(error => console.error('Error updating cart count:', error));
    }
    
    // Update cart count every 30 seconds
    setInterval(updateCartCount, 30000);
    </script>
</body>
</html>