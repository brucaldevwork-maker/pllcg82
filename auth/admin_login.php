<?php
// File: C:\xampp\htdocs\pllcg82\auth\admin_login.php

require_once '../config/config.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ../admin/admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        try {
            // Check if admin exists
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Set admin session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_logged_in'] = true;
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to admin dashboard
                header('Location: ../admin/admin_dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = 'Database error occurred. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PLLC Enterprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 2px, transparent 2px);
            background-size: 40px 40px;
            animation: moveBackground 30s linear infinite;
            pointer-events: none;
        }
        
        body::after {
            content: '🛡️';
            position: absolute;
            font-size: 100px;
            opacity: 0.03;
            bottom: 10%;
            right: 5%;
            animation: floatShield 10s ease-in-out infinite;
            pointer-events: none;
        }
        
        @keyframes moveBackground {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(40px, 40px);
            }
        }
        
        @keyframes floatShield {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }
        
        .container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 460px;
            padding: 48px 40px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: 1px solid rgba(52, 152, 219, 0.2);
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
        
        /* Logo Section */
        .logo-wrapper {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
        }
        
        .logo-container {
            display: inline-block;
            position: relative;
        }
        
        .logo-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120%;
            height: 120%;
            background: radial-gradient(circle, rgba(52, 152, 219, 0.2), transparent);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.5;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0.8;
            }
        }
        
        .logo {
            max-width: 100px;
            height: auto;
            filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.2));
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            z-index: 1;
        }
        
        .logo:hover {
            transform: scale(1.05) rotate(2deg);
            filter: drop-shadow(0 12px 28px rgba(52, 152, 219, 0.4));
        }
        
        .admin-badge {
            position: absolute;
            bottom: -15px;
            right: -20px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            font-size: 12px;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 20px;
            white-space: nowrap;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: shimmer 2s infinite;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        @keyframes shimmer {
            0%, 100% {
                opacity: 0.9;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
        }
        
        .admin-badge i {
            font-size: 10px;
        }
        
        h2 {
            color: #1A2C3E;
            text-align: center;
            margin-bottom: 8px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            text-align: center;
            color: #6C757D;
            font-size: 14px;
            margin-bottom: 32px;
        }
        
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #1A2C3E;
            font-weight: 600;
            font-size: 14px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #ADB5BD;
            font-size: 18px;
            transition: all 0.3s;
            z-index: 1;
        }
        
        input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E9ECEF;
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
            background: white;
        }
        
        input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }
        
        input:focus + i {
            color: #3498db;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(52, 152, 219, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .error {
            background: linear-gradient(135deg, #FEF3F2, #FEE2E2);
            color: #DC2626;
            padding: 12px 16px;
            border-radius: 16px;
            margin-bottom: 24px;
            border-left: 4px solid #DC2626;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .error i {
            font-size: 18px;
        }
        
        .separator {
            margin: 28px 0;
            text-align: center;
            position: relative;
        }
        
        .separator::before,
        .separator::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #E9ECEF, transparent);
        }
        
        .separator::before {
            left: 0;
        }
        
        .separator::after {
            right: 0;
        }
        
        .separator span {
            background: white;
            padding: 0 12px;
            color: #ADB5BD;
            font-size: 12px;
            font-weight: 500;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .links a {
            color: #6C757D;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .links a i {
            font-size: 14px;
            transition: transform 0.3s;
        }
        
        .links a:hover {
            color: #3498db;
        }
        
        .links a:hover i {
            transform: translateX(3px);
        }
        
        .register-link a {
            color: #27ae60;
        }
        
        .register-link a:hover {
            color: #229954;
        }
        
        .decor-line {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2980b9);
            margin: 24px auto 0;
            border-radius: 3px;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 32px 24px;
                border-radius: 24px;
            }
            
            h2 {
                font-size: 28px;
            }
            
            .logo {
                max-width: 80px;
            }
            
            .admin-badge {
                font-size: 10px;
                padding: 4px 10px;
                bottom: -12px;
                right: -15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrapper">
            <div class="logo-container">
                <img src="../logo.png" alt="PLLC Enterprise" class="logo">
                <div class="admin-badge">
                    <i class="fas fa-shield-alt"></i> Admin Access
                </div>
            </div>
        </div>
        
        <h2>Welcome Back</h2>
        <div class="subtitle">Sign in to your admin account</div>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <div class="input-group">
                    <i class="fas fa-user-shield"></i>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus placeholder="Enter your username">
                </div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
            </button>
        </form>
        
        <div class="separator">
            <span>or</span>
        </div>
        
        <div class="links">
            <a href="../auth/login.php">
                <i class="fas fa-user"></i> Go to User Login
            </a>
            <a href="admin_register.php" class="register-link">
                <i class="fas fa-user-plus"></i> Register New Admin Account
            </a>
            <a href="../index.php">
                <i class="fas fa-home"></i> Back to Homepage
            </a>
        </div>
        
        <div class="decor-line"></div>
    </div>
</body>
</html>