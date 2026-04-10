<?php
require_once '../config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address])) {
                $success = 'Registration successful! You can now login.';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Registration failed. Please try again.';
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
    <title>Register - PLLC Enterprise</title>
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
            content: '⚡';
            position: absolute;
            font-size: 100px;
            opacity: 0.03;
            top: 10%;
            left: 5%;
            animation: floatBike 10s ease-in-out infinite;
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
                transform: translateY(-20px) rotate(10deg);
            }
        }
        
        .container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 580px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #1A2C3E;
            font-weight: 600;
            font-size: 14px;
        }
        
        .required {
            color: #DC2626;
            margin-left: 4px;
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
        
        input, textarea {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E9ECEF;
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
            background: white;
        }
        
        textarea {
            padding: 14px 16px;
            resize: vertical;
            min-height: 90px;
        }
        
        input:focus, textarea:focus {
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
        
        .note {
            font-size: 11px;
            color: #ADB5BD;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .decor-line {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #00A8E8, #0A3D62);
            margin: 24px auto 0;
            border-radius: 3px;
        }
        
        @media (max-width: 640px) {
            .container {
                padding: 32px 24px;
                border-radius: 24px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
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
                    <i class="fas fa-star"></i> Premium
                </div>
            </div>
        </div>
        
        <h2>Create Account</h2>
        <div class="subtitle">Join the PLLC community today</div>
        
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
            <div class="form-row">
                <div class="form-group">
                    <label>Username <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required placeholder="Choose a username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="your@email.com">
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" required placeholder="Create a password">
                    </div>
                    <div class="note">
                        <i class="fas fa-info-circle"></i> Password must be at least 6 characters
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-check-circle"></i>
                        <input type="password" name="confirm_password" required placeholder="Confirm your password">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-group">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" placeholder="Enter your full name">
                </div>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <div class="input-group">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+63 XXX XXX XXXX">
                </div>
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" placeholder="Enter your complete address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Register Now
            </button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php"><i class="fas fa-arrow-right"></i> Login here</a>
        </div>
        <div class="decor-line"></div>
    </div>
</body>
</html>