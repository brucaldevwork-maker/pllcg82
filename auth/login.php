<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../user/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username_email) || empty($password)) {
        $error = 'Please enter username/email and password';
    } else {
        // Check if user exists by username or email
        $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, phone, address FROM users 
                               WHERE username = ? OR email = ?");
        $stmt->execute([$username_email, $username_email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            
            // Redirect to user dashboard
            header('Location: ../user/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username/email or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PLLC Enterprise</title>
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
            background: linear-gradient(135deg, #0A3D62 0%, #00A8E8 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background with Floating Bikes */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 2px, transparent 2px);
            background-size: 40px 40px;
            animation: moveBackground 30s linear infinite;
            pointer-events: none;
        }
        
        body::after {
            content: '🚲';
            position: absolute;
            font-size: 100px;
            opacity: 0.03;
            bottom: 10%;
            right: 5%;
            animation: floatBike 8s ease-in-out infinite;
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
        
        @keyframes floatBike {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }
        
        .container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(0px);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 460px;
            padding: 48px 40px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: 1px solid rgba(0, 168, 232, 0.2);
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
            background: radial-gradient(circle, rgba(0, 168, 232, 0.2), transparent);
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
            max-width: 130px;
            height: auto;
            filter: drop-shadow(0 8px 20px rgba(10, 61, 98, 0.2));
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            z-index: 1;
        }
        
        .logo:hover {
            transform: scale(1.05) rotate(2deg);
            filter: drop-shadow(0 12px 28px rgba(0, 168, 232, 0.3));
        }
        
        .logo-badge {
            position: absolute;
            bottom: -10px;
            right: -15px;
            background: linear-gradient(135deg, #00A8E8, #0A3D62);
            color: white;
            font-size: 12px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0%, 100% {
                opacity: 0.8;
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
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
            border-color: #00A8E8;
            box-shadow: 0 0 0 4px rgba(0, 168, 232, 0.1);
            transform: translateY(-2px);
        }
        
        input:focus + i {
            color: #00A8E8;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0A3D62, #00A8E8);
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
            box-shadow: 0 10px 25px -5px rgba(0, 168, 232, 0.4);
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
        
        .links {
            text-align: center;
            margin-top: 28px;
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        
        .links a {
            color: #6C757D;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .links a i {
            font-size: 14px;
            transition: transform 0.3s;
        }
        
        .links a:hover {
            color: #00A8E8;
        }
        
        .links a:hover i {
            transform: translateX(3px);
        }
        
        .demo-note {
            background: linear-gradient(135deg, #F8F9FA, #E9ECEF);
            padding: 12px 16px;
            border-radius: 16px;
            margin-top: 28px;
            font-size: 12px;
            color: #6C757D;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid rgba(0, 168, 232, 0.2);
        }
        
        .demo-note i {
            color: #00A8E8;
            font-size: 14px;
        }
        
        /* Decorative Elements */
        .decor-line {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #00A8E8, #0A3D62);
            margin: 20px auto 0;
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
                max-width: 100px;
            }
            
            .logo-badge {
                font-size: 10px;
                padding: 3px 8px;
                bottom: -8px;
                right: -12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrapper">
            <div class="logo-container">
                <img src="../logo.png" alt="PLLC Enterprise" class="logo">
                <div class="logo-badge">
                    <i class="fas fa-bolt"></i> Join Now!
                </div>
            </div>
        </div>
        
        <h2>Welcome Back</h2>
        <div class="subtitle">Sign in to your account</div>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username or Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="username_email" value="<?php echo htmlspecialchars($_POST['username_email'] ?? ''); ?>" required autofocus placeholder="Enter your username or email">
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
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="links">
            <a href="forget_pas.php"><i class="fas fa-key"></i> Forgot Password?</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Create Account</a>
        </div>
        
        <div class="demo-note">
            <i class="fas fa-info-circle"></i>
            Demo: Use your username or email to login
        </div>
        <div class="decor-line"></div>
    </div>
</body>
</html>