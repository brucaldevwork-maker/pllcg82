<?php
// File: C:\xampp\htdocs\pllcg82\user\account.php

require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = "";
$user_email = "";
$user_phone = "";
$user_address = "";
$member_since = "";

// Get user data
try {
    $stmt = $pdo->prepare("SELECT username, full_name, email, phone, address, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();
    if ($user_data) {
        $user_name = $user_data['full_name'] ?: $user_data['username'];
        $user_email = $user_data['email'] ?? '';
        $user_phone = $user_data['phone'] ?? '';
        $user_address = $user_data['address'] ?? '';
        $member_since = date('F Y', strtotime($user_data['created_at']));
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

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    
    // Update profile
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        $errors = [];
        if (empty($full_name)) $errors[] = "Full name is required";
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$full_name, $phone, $address, $user_id]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully!'
                ]);
                exit;
            } catch(PDOException $e) {
                error_log($e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to update profile. Please try again.'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => implode('<br>', $errors)
            ]);
            exit;
        }
    }
    
    // Change password
    if (isset($_POST['action']) && $_POST['action'] == 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        if (empty($current_password)) $errors[] = "Current password is required";
        if (empty($new_password)) $errors[] = "New password is required";
        if (strlen($new_password) < 8) $errors[] = "New password must be at least 8 characters";
        if ($new_password !== $confirm_password) $errors[] = "New passwords do not match";
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                if ($current_password !== $user['password']) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ]);
                    exit;
                }
                
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_password, $user_id]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Password changed successfully!'
                ]);
                exit;
            } catch(PDOException $e) {
                error_log($e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to change password. Please try again.'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => implode('<br>', $errors)
            ]);
            exit;
        }
    }
    
    // Submit feedback
    if (isset($_POST['action']) && $_POST['action'] == 'submit_feedback') {
        $feedback_text = trim($_POST['feedback'] ?? '');
        
        if (empty($feedback_text)) {
            echo json_encode([
                'success' => false,
                'message' => 'Feedback cannot be empty'
            ]);
            exit;
        }
        
        $sentiment = analyzeSentiment($feedback_text);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (user_id, rating, comment, sentiment, created_at) VALUES (?, 0, ?, ?, NOW())");
            $stmt->execute([$user_id, $feedback_text, $sentiment['label']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your feedback!',
                'sentiment' => $sentiment
            ]);
            exit;
        } catch(PDOException $e) {
            error_log($e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Unable to save feedback. Please try again.'
            ]);
            exit;
        }
    }
    exit;
}

function analyzeSentiment($text) {
    $text_lower = strtolower($text);
    
    $positive_words = ['good', 'great', 'excellent', 'amazing', 'awesome', 'love', 'happy', 'satisfied', 'perfect', 'fantastic', 'wonderful', 'best', 'recommend', 'helpful', 'fast'];
    $negative_words = ['bad', 'poor', 'terrible', 'awful', 'hate', 'disappointed', 'worst', 'horrible', 'issue', 'problem', 'broken', 'slow', 'expensive'];
    
    $positive_count = 0;
    $negative_count = 0;
    
    foreach ($positive_words as $word) {
        if (strpos($text_lower, $word) !== false) $positive_count++;
    }
    
    foreach ($negative_words as $word) {
        if (strpos($text_lower, $word) !== false) $negative_count++;
    }
    
    if ($positive_count > $negative_count) {
        return ['label' => 'Positive', 'summary' => 'Thank you for your positive feedback!', 'icon' => '😊'];
    } elseif ($negative_count > $positive_count) {
        return ['label' => 'Negative', 'summary' => 'We appreciate your feedback and will improve.', 'icon' => '😟'];
    } else {
        return ['label' => 'Neutral', 'summary' => 'Thank you for sharing your thoughts!', 'icon' => '😐'];
    }
}

include_once 'components/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, follow">
    
    <title>Account Settings | PLLC Enterprise</title>
    <meta name="description" content="Manage your account settings, update profile, change password, and view your information.">
    
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

        .account-page {
            background: var(--white);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(10, 61, 98, 0.08);
        }

        .page-title {
            font-size: 2rem;
            color: var(--primary-blue);
            margin-bottom: 30px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title i {
            color: var(--accent-blue);
        }

        hr {
            margin: 40px 0;
            border: none;
            border-top: 2px solid #eef2f6;
        }

        /* Profile Overview - Full Width */
        .profile-overview {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            padding: 35px;
            border-radius: 24px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 25px;
            color: white;
            width: 100%;
        }

        .profile-info h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .profile-info p {
            margin: 8px 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .profile-info i {
            width: 25px;
            color: var(--accent-blue);
        }

        .logout-page-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 28px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: auto;
        }

        .logout-page-btn:hover {
            background: var(--danger);
            border-color: var(--danger);
            transform: translateY(-2px);
        }

        /* Settings Sections - Full Width */
        .settings-section {
            margin-bottom: 45px;
            width: 100%;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eef2f6;
        }

        .section-header i {
            font-size: 1.5rem;
            color: var(--accent-blue);
        }

        .section-header h2 {
            font-size: 1.5rem;
            color: var(--primary-blue);
            font-weight: 600;
        }

        /* Forms - Full Width */
        .settings-form {
            width: 100%;
            background: var(--bg-light);
            padding: 30px;
            border-radius: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-group label i {
            color: var(--accent-blue);
            margin-right: 8px;
            width: 20px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s;
            background: var(--white);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 168, 232, 0.1);
        }

        /* Password Input */
        .password-input-container {
            position: relative;
        }

        .password-input-container input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: var(--text-light);
            padding: 5px;
        }

        .password-toggle:hover {
            color: var(--accent-blue);
        }

        .password-strength,
        .password-match {
            font-size: 0.75rem;
            margin-top: 6px;
        }

        .strength-weak { color: var(--danger); }
        .strength-medium { color: var(--warning); }
        .strength-strong { color: var(--success); }

        /* Buttons */
        .submit-btn {
            padding: 14px 28px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background: #0080c0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 168, 232, 0.3);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
            width: 100%;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger);
        }

        .alert i {
            font-size: 1.2rem;
        }

        /* About Section */
        .about-content {
            background: var(--bg-light);
            padding: 25px;
            border-radius: 20px;
            line-height: 1.7;
        }

        .about-content p {
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .about-content p:last-child {
            margin-bottom: 0;
        }

        /* Sentiment Result */
        .sentiment-result {
            background: var(--bg-light);
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
            border-left: 4px solid var(--accent-blue);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 0 15px;
                margin: 20px auto;
            }
            .account-page {
                padding: 25px;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .profile-overview {
                flex-direction: column;
                text-align: center;
                padding: 25px;
            }
            .profile-info {
                text-align: center;
            }
            .profile-info h2 {
                font-size: 1.4rem;
            }
            .section-header h2 {
                font-size: 1.3rem;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .settings-form {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .account-page {
                padding: 20px;
            }
            .settings-form {
                padding: 15px;
            }
            .submit-btn {
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar is included from components/navbar.php -->

    <!-- Main Content -->
    <main class="main-content">
        <div class="account-page">
            <div class="page-title">
                <i class="fas fa-user-circle"></i>
                <h1>Account Settings</h1>
            </div>
            
            <!-- Alert Container -->
            <div id="alertContainer"></div>
            
            <!-- Profile Overview -->
            <div class="profile-overview">
                <div class="profile-info">
                    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user_email); ?></p>
                    <p><i class="fas fa-calendar-alt"></i> Member since <?php echo htmlspecialchars($member_since); ?></p>
                    <?php if (!empty($user_phone)): ?>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user_phone); ?></p>
                    <?php endif; ?>
                </div>
                <button class="logout-page-btn" onclick="window.location.href='../auth/logout.php'">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </button>
            </div>

            <!-- Update Profile Section - Full Width -->
            <div class="settings-section">
                <div class="section-header">
                    <i class="fas fa-user-edit"></i>
                    <h2>Update Profile Details</h2>
                </div>
                <form id="profileUpdateForm" class="settings-form">
                    <div class="form-group">
                        <label for="fullName"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="fullName" name="full_name" value="<?php echo htmlspecialchars($user_name); ?>" required placeholder="Enter your full name">
                    </div>
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" placeholder="e.g., 0912-345-6789">
                    </div>
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Shipping Address</label>
                        <textarea id="address" name="address" rows="3" placeholder="Enter your complete shipping address"><?php echo htmlspecialchars($user_address); ?></textarea>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Save Profile Changes
                    </button>
                </form>
            </div>

            <!-- Change Password Section - Full Width -->
            <div class="settings-section">
                <div class="section-header">
                    <i class="fas fa-key"></i>
                    <h2>Change Password</h2>
                </div>
                <form id="passwordChangeForm" class="settings-form">
                    <div class="form-group">
                        <label for="currentPassword"><i class="fas fa-lock"></i> Current Password</label>
                        <div class="password-input-container">
                            <input type="password" id="currentPassword" name="current_password" required placeholder="Enter your current password">
                            <button type="button" class="password-toggle" data-target="currentPassword">👁️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newPassword"><i class="fas fa-key"></i> New Password</label>
                        <div class="password-input-container">
                            <input type="password" id="newPassword" name="new_password" required minlength="8" placeholder="At least 8 characters">
                            <button type="button" class="password-toggle" data-target="newPassword">👁️</button>
                        </div>
                        <div id="passwordStrength" class="password-strength"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                        <div class="password-input-container">
                            <input type="password" id="confirmPassword" name="confirm_password" required minlength="8" placeholder="Re-enter your new password">
                            <button type="button" class="password-toggle" data-target="confirmPassword">👁️</button>
                        </div>
                        <div id="passwordMatch" class="password-match"></div>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-exchange-alt"></i> Change Password
                    </button>
                </form>
            </div>

            <!-- About Section - Full Width -->
            <div class="settings-section">
                <div class="section-header">
                    <i class="fas fa-info-circle"></i>
                    <h2>About PLLC Enterprise</h2>
                </div>
                <div class="about-content">
                    <p><strong>PLLC Enterprise</strong> stands for <strong>Power Long-lasting Legendary Cruising</strong>. We are committed to providing innovative, high-quality, and sustainable electric mobility solutions.</p>
                    <p>Our mission is to empower a greener future by making electric biking accessible and enjoyable for everyone. We specialize in cutting-edge e-bike designs, long-life battery technology, and providing reliable spare parts and service.</p>
                    <p>Thank you for being a part of the PLLC family!</p>
                </div>
            </div>

            <!-- Feedback Section - Full Width -->
            <div class="settings-section">
                <div class="section-header">
                    <i class="fas fa-comment-dots"></i>
                    <h2>Share Your Feedback</h2>
                </div>
                <form id="feedbackForm" class="settings-form">
                    <div class="form-group">
                        <label for="feedbackText"><i class="fas fa-pen"></i> Your Feedback</label>
                        <textarea id="feedbackText" name="feedback" rows="4" required placeholder="Tell us about your experience with PLLC Enterprise..."></textarea>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Submit Feedback
                    </button>
                    <div id="sentimentResult" style="margin-top: 15px;"></div>
                </form>
            </div>
        </div>
    </main>

    <script>
    // Show alert message
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        alertContainer.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Password toggle visibility
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '🙈';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });
    
    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        
        if (strength <= 2) return { level: 'Weak', class: 'strength-weak', message: 'Use at least 8 characters with letters and numbers' };
        if (strength <= 4) return { level: 'Medium', class: 'strength-medium', message: 'Good password, add special characters for stronger security' };
        return { level: 'Strong', class: 'strength-strong', message: 'Excellent password strength!' };
    }
    
    document.getElementById('newPassword').addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        const strengthDiv = document.getElementById('passwordStrength');
        if (this.value.length > 0) {
            strengthDiv.innerHTML = `<span class="${strength.class}">🔒 ${strength.level}: ${strength.message}</span>`;
        } else {
            strengthDiv.innerHTML = '';
        }
    });
    
    // Password match checker
    function checkPasswordMatch() {
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        const matchDiv = document.getElementById('passwordMatch');
        
        if (confirmPass === '') {
            matchDiv.innerHTML = '';
        } else if (newPass === confirmPass) {
            matchDiv.innerHTML = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> Passwords match</span>';
        } else {
            matchDiv.innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> Passwords do not match</span>';
        }
    }
    
    document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
    document.getElementById('newPassword').addEventListener('input', checkPasswordMatch);
    
    // Update Profile Form
    document.getElementById('profileUpdateForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('action', 'update_profile');
        formData.append('full_name', document.getElementById('fullName').value);
        formData.append('phone', document.getElementById('phone').value);
        formData.append('address', document.getElementById('address').value);
        
        try {
            const response = await fetch('account.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showAlert(data.message, 'success');
                document.querySelector('.profile-info h2').innerHTML = `Welcome, ${document.getElementById('fullName').value}!`;
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Unable to update profile. Please try again.', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // Change Password Form
    document.getElementById('passwordChangeForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        
        if (newPass !== confirmPass) {
            showAlert('New passwords do not match', 'error');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('action', 'change_password');
        formData.append('current_password', document.getElementById('currentPassword').value);
        formData.append('new_password', newPass);
        formData.append('confirm_password', confirmPass);
        
        try {
            const response = await fetch('account.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showAlert(data.message, 'success');
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
                document.getElementById('passwordStrength').innerHTML = '';
                document.getElementById('passwordMatch').innerHTML = '';
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Unable to change password. Please try again.', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // Feedback Form
    document.getElementById('feedbackForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const feedbackText = document.getElementById('feedbackText').value;
        if (!feedbackText.trim()) {
            showAlert('Please enter your feedback', 'error');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('action', 'submit_feedback');
        formData.append('feedback', feedbackText);
        
        try {
            const response = await fetch('account.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showAlert(data.message, 'success');
                document.getElementById('feedbackText').value = '';
                
                if (data.sentiment) {
                    const sentimentDiv = document.getElementById('sentimentResult');
                    sentimentDiv.innerHTML = `
                        <div class="sentiment-result">
                            <p><strong>📊 Sentiment Analysis:</strong> ${data.sentiment.icon} ${data.sentiment.label}</p>
                            <p style="font-size: 0.85rem; margin-top: 5px; color: var(--text-light);">${data.sentiment.summary}</p>
                        </div>
                    `;
                    setTimeout(() => {
                        sentimentDiv.innerHTML = '';
                    }, 5000);
                }
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Unable to submit feedback. Please try again.', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    </script>
</body>
</html>