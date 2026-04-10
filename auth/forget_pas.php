<?php
require_once '../config/config.php';

$step = 1; // 1: request email, 2: reset password
$error = '';
$success = '';
$email = '';

// Step 1: Request password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database (you might want to create a password_resets table)
            // For simplicity, we'll just show a message
            $success = 'If the email exists, you will receive password reset instructions.';
            
            // In production, you would send an email here with a reset link
            // For demo purposes, we'll show the reset link
            $reset_link = SITE_URL . "auth/forget_pas.php?token=" . $token . "&email=" . urlencode($email);
            $success .= "<br><br><strong>Demo Reset Link:</strong><br><a href='{$reset_link}' target='_blank'>{$reset_link}</a>";
        } else {
            // Don't reveal that email doesn't exist for security
            $success = 'If the email exists, you will receive password reset instructions.';
        }
    }
}

// Step 2: Reset password
if (isset($_GET['token']) && isset($_GET['email'])) {
    $step = 2;
    $email = urldecode($_GET['email']);
    $token = $_GET['token'];
    
    // For demo, we'll just verify token exists
    // In production, you should verify token from database
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in all fields';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            
            if ($stmt->execute([$hashed_password, $email])) {
                $success = 'Password has been reset successfully! You can now login.';
                $step = 3; // Show success message only
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PLLC Enterprise</title>
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
        
        /* Animated Background */
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
            content: '🔋';
            position: absolute;
            font-size: 100px;
            opacity: 0.03;
            bottom: 10%;
            right: 5%;
            animation: floatBike 9s ease-in-out infinite;
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
                transform: translateY(-20px) rotate(-5deg);
            }
        }
        
        .container {
            background: rgba(255, 255, 255, 0.98);
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
            font-size: 11px;
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
        
        .success {
            background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
            color: #2E7D32;
            padding: 12px 16px;
            border-radius: 16px;
            margin-bottom: 24px;
            border-left: 4px solid #4CAF50;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
            flex-direction: column;
            text-align: center;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .success i {
            font-size: 18px;
        }
        
        .success a {
            color: #00A8E8;
            text-decoration: none;
            font-weight: 600;
            margin-top: 5px;
            display: inline-block;
        }
        
        .success a:hover {
            text-decoration: underline;
        }
        
        .login-link {
            text-align: center;
            margin-top: 28px;
            color: #6C757D;
            font-size: 14px;
        }
        
        .login-link a {
            color: #00A8E8;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .login-link a:hover {
            color: #0A3D62;
            gap: 10px;
        }
        
        .info-text {
            background: linear-gradient(135deg, #E8F4F8, #D4EAF2);
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #0A3D62;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(0, 168, 232, 0.3);
        }
        
        .info-text i {
            font-size: 20px;
            color: #00A8E8;
        }
        
        .decor-line {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #00A8E8, #0A3D62);
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
                max-width: 100px;
            }
            
            .logo-badge {
                font-size: 9px;
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
                    <i class="fas fa-shield-alt"></i> Secure
                </div>
            </div>
        </div>
        
        <?php if ($step == 1): ?>
            <h2>Forgot Password</h2>
            <div class="subtitle">We'll help you reset your password</div>
            
            <div class="info-text">
                <i class="fas fa-info-circle"></i>
                Enter your email address and we'll send you instructions to reset your password.
            </div>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="your@email.com">
                    </div>
                </div>
                
                <button type="submit" name="request_reset" class="btn">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>
            
            <div class="login-link">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
            <div class="decor-line"></div>
            
        <?php elseif ($step == 2): ?>
            <h2>Reset Password</h2>
            <div class="subtitle">Create a new password for your account</div>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>New Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="new_password" required placeholder="Create a new password">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="input-group">
                        <i class="fas fa-check-circle"></i>
                        <input type="password" name="confirm_password" required placeholder="Confirm your new password">
                    </div>
                </div>
                
                <button type="submit" name="reset_password" class="btn">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </form>
            
            <div class="login-link">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
            <div class="decor-line"></div>
            
        <?php elseif ($step == 3): ?>
            <h2>Password Reset</h2>
            <div class="subtitle">Your password has been updated</div>
            
            <?php if ($success): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <button onclick="window.location.href='login.php'" class="btn">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </button>
            
            <div class="login-link">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
            <div class="decor-line"></div>
        <?php endif; ?>
    </div>
</body>
</html>