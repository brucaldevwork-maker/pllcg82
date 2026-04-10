<?php
// File: C:\xampp\htdocs\pllcg82\user\appointment.php

require_once '../config/config.php';

// Remove this line - session is already started in config.php or navbar.php
// session_start();

// Check if user is logged in
$user_name = "User";
$user_id = null;
$user_email = "";
$user_phone = "";
$user_fullname = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT username, full_name, email, phone FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        if ($user_data) {
            $user_name = $user_data['full_name'] ?: $user_data['username'];
            $user_fullname = $user_data['full_name'] ?: $user_data['username'];
            $user_email = $user_data['email'] ?? '';
            $user_phone = $user_data['phone'] ?? '';
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

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customerName'] ?? '');
    $customer_email = trim($_POST['customerEmail'] ?? '');
    $customer_phone = trim($_POST['customerPhone'] ?? '');
    $bike_model = trim($_POST['bikeModel'] ?? '');
    $service_type = trim($_POST['serviceType'] ?? '');
    $appointment_date = $_POST['appointmentDate'] ?? '';
    $appointment_time = $_POST['appointmentTime'] ?? '';
    $branch_location = trim($_POST['branchLocation'] ?? '');
    $problem_description = trim($_POST['problemDescription'] ?? '');
    $urgency = trim($_POST['urgency'] ?? 'normal');
    
    // Validation
    $errors = [];
    
    if (empty($customer_name)) $errors[] = "Full name is required";
    if (empty($customer_email)) $errors[] = "Email address is required";
    if (empty($customer_phone)) $errors[] = "Phone number is required";
    if (empty($bike_model)) $errors[] = "Please select your e-bike model";
    if (empty($service_type)) $errors[] = "Please select service type";
    if (empty($appointment_date)) $errors[] = "Please select appointment date";
    if (empty($appointment_time)) $errors[] = "Please select appointment time";
    if (empty($branch_location)) $errors[] = "Please select service branch";
    
    // Validate email format
    if (!empty($customer_email) && !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    // Validate date (cannot be in the past)
    if (!empty($appointment_date) && $appointment_date < date('Y-m-d')) {
        $errors[] = "Appointment date cannot be in the past";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (user_id, service_type, appointment_date, appointment_time, status, notes, created_at) 
                                    VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
            
            $notes = "Bike Model: $bike_model\nBranch: $branch_location\nUrgency: $urgency\nProblem: $problem_description";
            
            $stmt->execute([
                $user_id,
                $service_type,
                $appointment_date,
                $appointment_time,
                $notes
            ]);
            
            $success_message = "Your appointment has been booked successfully! We will contact you within 24 hours to confirm.";
            
            // Clear form data after successful submission
            $_POST = array();
            
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error_message = "Unable to book appointment. Please try again later.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Get existing appointments for the user
$user_appointments = [];
if ($user_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC, appointment_time DESC LIMIT 5");
        $stmt->execute([$user_id]);
        $user_appointments = $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
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
    <title>Book Service Appointment | E-Bike Repair & Maintenance | PLLC Enterprise</title>
    <meta name="description" content="Schedule professional maintenance and repair services for your PLLC e-bike. Certified technicians, multiple branches, and quick turnaround time. Book your appointment today!">
    <meta name="keywords" content="e-bike service, e-bike repair, bike maintenance, PLLC service center, electric bike repair, battery service, motor service">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Book Service Appointment | PLLC Enterprise">
    <meta property="og:description" content="Schedule professional maintenance and repair services for your PLLC e-bike.">
    <meta property="og:image" content="https://yourdomain.com/image/og-service.jpg">
    <meta property="og:url" content="https://yourdomain.com/user/appointment.php">
    <meta property="og:site_name" content="PLLC Enterprise">
    
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

        .appointment-page {
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

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert i {
            font-size: 20px;
        }

        /* Form Styles */
        .appointment-form {
            background: var(--bg-light);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group label i {
            margin-right: 5px;
            color: var(--accent-blue);
        }

        .form-group label .required {
            color: var(--danger);
            margin-left: 3px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
            background: var(--white);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 168, 232, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .submit-btn {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .submit-btn:hover {
            background: #0080c0;
            transform: translateY(-2px);
        }

        .submit-btn i {
            margin-right: 8px;
        }

        /* Service Info Section */
        .service-info {
            margin-top: 40px;
        }

        .service-info h3 {
            font-size: 1.5rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .service-info h3 i {
            color: var(--accent-blue);
            margin-right: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .info-item {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 16px;
            transition: all 0.3s;
            border: 1px solid #eef2f6;
        }

        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(10, 61, 98, 0.1);
            border-color: rgba(0, 168, 232, 0.3);
        }

        .info-item h4 {
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .info-item p {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.5;
        }

        /* Appointments History */
        .appointments-history {
            margin-top: 40px;
        }

        .appointments-history h3 {
            font-size: 1.5rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .appointments-history h3 i {
            color: var(--accent-blue);
            margin-right: 10px;
        }

        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-light);
            border-radius: 16px;
            overflow: hidden;
        }

        .appointments-table th,
        .appointments-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eef2f6;
        }

        .appointments-table th {
            background: var(--primary-blue);
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .appointments-table td {
            color: var(--text-dark);
            font-size: 14px;
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

        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .no-appointments {
            text-align: center;
            padding: 40px;
            background: var(--bg-light);
            border-radius: 16px;
            color: var(--text-light);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 0 15px;
                margin: 20px auto;
            }
            .appointment-page {
                padding: 20px;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .appointment-form {
                padding: 20px;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .appointments-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar is included from components/navbar.php -->

    <!-- Main Content -->
    <main class="main-content">
        <div class="appointment-page">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Book Service Appointment</h1>
                <p class="page-subtitle">Schedule professional maintenance or repair for your PLLC e-bike</p>
            </div>
            
            <!-- Alert Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Appointment Form -->
            <form method="POST" action="" class="appointment-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="customerName"><i class="fas fa-user"></i> Full Name <span class="required">*</span></label>
                        <input type="text" id="customerName" name="customerName" required 
                               value="<?php echo htmlspecialchars($_POST['customerName'] ?? $user_fullname); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="customerEmail"><i class="fas fa-envelope"></i> Email Address <span class="required">*</span></label>
                        <input type="email" id="customerEmail" name="customerEmail" required 
                               value="<?php echo htmlspecialchars($_POST['customerEmail'] ?? $user_email); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="customerPhone"><i class="fas fa-phone"></i> Phone Number <span class="required">*</span></label>
                        <input type="tel" id="customerPhone" name="customerPhone" required 
                               value="<?php echo htmlspecialchars($_POST['customerPhone'] ?? $user_phone); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="bikeModel"><i class="fas fa-bicycle"></i> E-Bike Model <span class="required">*</span></label>
                        <select id="bikeModel" name="bikeModel" required>
                            <option value="">Select your e-bike model</option>
                            <option value="raven" <?php echo (isset($_POST['bikeModel']) && $_POST['bikeModel'] == 'raven') ? 'selected' : ''; ?>>PLLC Raven E-Bike</option>
                            <option value="echo" <?php echo (isset($_POST['bikeModel']) && $_POST['bikeModel'] == 'echo') ? 'selected' : ''; ?>>PLLC Echo E-Bike</option>
                            <option value="supreme" <?php echo (isset($_POST['bikeModel']) && $_POST['bikeModel'] == 'supreme') ? 'selected' : ''; ?>>PLLC Supreme E-Bike</option>
                            <option value="skye" <?php echo (isset($_POST['bikeModel']) && $_POST['bikeModel'] == 'skye') ? 'selected' : ''; ?>>PLLC Skye E-Bike</option>
                            <option value="zhi18" <?php echo (isset($_POST['bikeModel']) && $_POST['bikeModel'] == 'zhi18') ? 'selected' : ''; ?>>PLLC Zhi 18 E-Bike</option>
                            <option value="other" <?php echo (isset($_POST['bikeModel']) && $_POST['bikeModel'] == 'other') ? 'selected' : ''; ?>>Other Model</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="serviceType"><i class="fas fa-tools"></i> Service Type <span class="required">*</span></label>
                        <select id="serviceType" name="serviceType" required>
                            <option value="">Select service type</option>
                            <option value="maintenance" <?php echo (isset($_POST['serviceType']) && $_POST['serviceType'] == 'maintenance') ? 'selected' : ''; ?>>Regular Maintenance</option>
                            <option value="repair" <?php echo (isset($_POST['serviceType']) && $_POST['serviceType'] == 'repair') ? 'selected' : ''; ?>>Repair Service</option>
                            <option value="battery" <?php echo (isset($_POST['serviceType']) && $_POST['serviceType'] == 'battery') ? 'selected' : ''; ?>>Battery Service</option>
                            <option value="motor" <?php echo (isset($_POST['serviceType']) && $_POST['serviceType'] == 'motor') ? 'selected' : ''; ?>>Motor Service</option>
                            <option value="brake" <?php echo (isset($_POST['serviceType']) && $_POST['serviceType'] == 'brake') ? 'selected' : ''; ?>>Brake Service</option>
                            <option value="tire" <?php echo (isset($_POST['serviceType']) && $_POST['serviceType'] == 'tire') ? 'selected' : ''; ?>>Tire Service</option>
                            <option value="electrical" <?php echo (isset($_POST['serviceType']) && $_POST['serviceType'] == 'electrical') ? 'selected' : ''; ?>>Electrical Service</option>
                            <option value="other" <?php echo (isset($_POST['serviceType']) && $_POST['serviceType'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="urgency"><i class="fas fa-clock"></i> Urgency Level</label>
                        <select id="urgency" name="urgency">
                            <option value="normal" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'normal') ? 'selected' : ''; ?>>Normal (1-2 weeks)</option>
                            <option value="urgent" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'urgent') ? 'selected' : ''; ?>>Urgent (3-5 days)</option>
                            <option value="emergency" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'emergency') ? 'selected' : ''; ?>>Emergency (1-2 days)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="appointmentDate"><i class="fas fa-calendar-day"></i> Preferred Date <span class="required">*</span></label>
                        <input type="date" id="appointmentDate" name="appointmentDate" required 
                               value="<?php echo htmlspecialchars($_POST['appointmentDate'] ?? ''); ?>"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="appointmentTime"><i class="fas fa-clock"></i> Preferred Time <span class="required">*</span></label>
                        <select id="appointmentTime" name="appointmentTime" required>
                            <option value="">Select time slot</option>
                            <option value="09:00" <?php echo (isset($_POST['appointmentTime']) && $_POST['appointmentTime'] == '09:00') ? 'selected' : ''; ?>>9:00 AM</option>
                            <option value="10:00" <?php echo (isset($_POST['appointmentTime']) && $_POST['appointmentTime'] == '10:00') ? 'selected' : ''; ?>>10:00 AM</option>
                            <option value="11:00" <?php echo (isset($_POST['appointmentTime']) && $_POST['appointmentTime'] == '11:00') ? 'selected' : ''; ?>>11:00 AM</option>
                            <option value="13:00" <?php echo (isset($_POST['appointmentTime']) && $_POST['appointmentTime'] == '13:00') ? 'selected' : ''; ?>>1:00 PM</option>
                            <option value="14:00" <?php echo (isset($_POST['appointmentTime']) && $_POST['appointmentTime'] == '14:00') ? 'selected' : ''; ?>>2:00 PM</option>
                            <option value="15:00" <?php echo (isset($_POST['appointmentTime']) && $_POST['appointmentTime'] == '15:00') ? 'selected' : ''; ?>>3:00 PM</option>
                            <option value="16:00" <?php echo (isset($_POST['appointmentTime']) && $_POST['appointmentTime'] == '16:00') ? 'selected' : ''; ?>>4:00 PM</option>
                            <option value="17:00" <?php echo (isset($_POST['appointmentTime']) && $_POST['appointmentTime'] == '17:00') ? 'selected' : ''; ?>>5:00 PM</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="branchLocation"><i class="fas fa-map-marker-alt"></i> Service Branch <span class="required">*</span></label>
                    <select id="branchLocation" name="branchLocation" required>
                        <option value="">Select a branch</option>
                        <option value="Calamba" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'Calamba') ? 'selected' : ''; ?>>Calamba - National H-way Corner, Carnation St. Brgy. 1 Crossing</option>
                        <option value="CSC" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'CSC') ? 'selected' : ''; ?>>Calamba - Lot 21 Block 6 National Highway, Real</option>
                        <option value="SPC" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'SPC') ? 'selected' : ''; ?>>San Pablo - Main Road, National Highway Brgy. 6A Rizal Ave.</option>
                        <option value="Los Baños" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'Los Baños') ? 'selected' : ''; ?>>Los Baños - Brgy. San Antonio</option>
                        <option value="Lbsc" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'Lbsc') ? 'selected' : ''; ?>>Los Baños Service Center - Brgy. San Antonio</option>
                        <option value="tanuan" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'tanuan') ? 'selected' : ''; ?>>Tanauan, Batangas - #29 Pres. Laurel Highway Poblacion Brgy. 3</option>
                        <option value="Lucena" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'Lucena') ? 'selected' : ''; ?>>Lucena City - H.R Building Quezon Ave. Corner Gomez St.</option>
                        <option value="STC" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'STC') ? 'selected' : ''; ?>>Santa Cruz - Doña Lolita Bidg, Sampaguita Circle Brgy. Bubukal</option>
                        <option value="Gumaca" <?php echo (isset($_POST['branchLocation']) && $_POST['branchLocation'] == 'Gumaca') ? 'selected' : ''; ?>>Gumaca Quezon - JT2 Building, Maharlika Highway, Brgy. Peñafrancia</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="problemDescription"><i class="fas fa-comment"></i> Problem Description</label>
                    <textarea id="problemDescription" name="problemDescription" rows="4" placeholder="Describe the issue or service needed in detail..."><?php echo htmlspecialchars($_POST['problemDescription'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-calendar-check"></i> Book Appointment
                </button>
            </form>
            
            <!-- Service Information -->
            <div class="service-info">
                <h3><i class="fas fa-info-circle"></i> Service Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <h4>🔧 Regular Maintenance</h4>
                        <p>Includes brake adjustment, tire check, battery test, and general inspection. Recommended every 3 months.</p>
                    </div>
                    <div class="info-item">
                        <h4>🔋 Battery Service</h4>
                        <p>Battery diagnostics, charging port check, and battery replacement if needed.</p>
                    </div>
                    <div class="info-item">
                        <h4>⚡ Motor Service</h4>
                        <p>Motor diagnostics, controller check, and motor repair or replacement.</p>
                    </div>
                    <div class="info-item">
                        <h4>🛞 Tire Service</h4>
                        <p>Tire inspection, pressure check, and tire replacement if needed.</p>
                    </div>
                </div>
            </div>
            
            <!-- Appointment History -->
            <?php if ($user_id && count($user_appointments) > 0): ?>
            <div class="appointments-history">
                <h3><i class="fas fa-history"></i> Your Recent Appointments</h3>
                <div style="overflow-x: auto;">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Service Type</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td><?php echo ucfirst($appointment['service_type']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(substr($appointment['notes'], 0, 50)) . (strlen($appointment['notes']) > 50 ? '...' : ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
    // Set min date to today
    const dateInput = document.getElementById('appointmentDate');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
    </script>
</body>
</html>