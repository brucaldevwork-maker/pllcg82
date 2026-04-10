<?php
// File: C:\xampp\htdocs\pllcg82\user\feedback.php

require_once '../config/config.php';

// Remove session_start() - it's already started in config.php

// Check if user is logged in
$user_name = "User";
$user_id = null;
$user_email = "";
$user_fullname = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT username, full_name, email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        if ($user_data) {
            $user_name = $user_data['full_name'] ?: $user_data['username'];
            $user_fullname = $user_data['full_name'] ?: $user_data['username'];
            $user_email = $user_data['email'] ?? '';
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

// Handle AJAX request for sentiment analysis
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['action']) && $_POST['action'] == 'analyze_sentiment') {
        $feedback_text = trim($_POST['feedback'] ?? '');
        
        if (empty($feedback_text)) {
            echo json_encode(['success' => false, 'message' => 'No feedback text provided']);
            exit;
        }
        
        // Simple sentiment analysis
        $sentiment = analyzeSentiment($feedback_text);
        
        echo json_encode([
            'success' => true,
            'sentiment' => $sentiment['label'],
            'analysis' => $sentiment['summary'],
            'icon' => $sentiment['icon']
        ]);
        exit;
    }
    
    // Handle feedback submission via AJAX
    if (isset($_POST['action']) && $_POST['action'] == 'submit_feedback') {
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $feedback_type = trim($_POST['feedback_type'] ?? '');
        $recommendation = trim($_POST['recommendation'] ?? '');
        $rating = intval($_POST['rating'] ?? 0);
        $feedback_message = trim($_POST['feedback_message'] ?? '');
        $product_id = !empty($_POST['product_id']) ? intval($_POST['product_id']) : null; // Optional product ID
        
        $errors = [];
        
        if (empty($customer_name)) $errors[] = "Name is required";
        if (empty($customer_email)) $errors[] = "Email is required";
        if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($feedback_type)) $errors[] = "Feedback type is required";
        if ($rating < 1 || $rating > 5) $errors[] = "Valid rating is required";
        if (empty($feedback_message)) $errors[] = "Feedback message is required";
        
        if (empty($errors)) {
            try {
                // Analyze sentiment for the feedback
                $sentiment = analyzeSentiment($feedback_message);
                
                // Prepare comment with additional metadata
                $comment = $feedback_message . "\n\n--- Additional Information ---\n" .
                          "Feedback Type: $feedback_type\n" .
                          "Recommendation: $recommendation\n" .
                          "Name: $customer_name\n" .
                          "Email: $customer_email";
                
                // Insert into feedback table matching your SQL structure
                $stmt = $pdo->prepare("INSERT INTO feedback (user_id, product_id, rating, comment, sentiment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $user_id,        // Can be NULL if user not logged in
                    $product_id,     // Can be NULL if not product-specific
                    $rating,
                    $comment,
                    $sentiment['label']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Thank you for your feedback!',
                    'sentiment' => $sentiment
                ]);
                exit;
            } catch(PDOException $e) {
                error_log($e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Unable to save feedback. Please try again.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
            exit;
        }
    }
    exit;
}

// Simple sentiment analysis function
function analyzeSentiment($text) {
    $text_lower = strtolower($text);
    
    // Positive keywords
    $positive_words = ['good', 'great', 'excellent', 'amazing', 'awesome', 'love', 'happy', 'satisfied', 'perfect', 'fantastic', 'wonderful', 'best', 'recommend', 'helpful', 'fast', 'quick'];
    // Negative keywords
    $negative_words = ['bad', 'poor', 'terrible', 'awful', 'hate', 'disappointed', 'worst', 'horrible', 'issue', 'problem', 'broken', 'slow', 'expensive', 'delay', 'damage'];
    
    $positive_count = 0;
    $negative_count = 0;
    
    foreach ($positive_words as $word) {
        if (strpos($text_lower, $word) !== false) {
            $positive_count++;
        }
    }
    
    foreach ($negative_words as $word) {
        if (strpos($text_lower, $word) !== false) {
            $negative_count++;
        }
    }
    
    if ($positive_count > $negative_count) {
        return [
            'label' => 'Positive',
            'summary' => 'Thank you for your positive feedback! We appreciate your support and will continue to improve our services.',
            'icon' => '😊'
        ];
    } elseif ($negative_count > $positive_count) {
        return [
            'label' => 'Negative',
            'summary' => 'We apologize for your experience. Your feedback helps us improve. Our team will reach out to address your concerns.',
            'icon' => '😟'
        ];
    } else {
        return [
            'label' => 'Neutral',
            'summary' => 'Thank you for sharing your thoughts. We value your feedback and will use it to enhance our products and services.',
            'icon' => '😐'
        ];
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
    <meta name="robots" content="noindex, follow">
    
    <title>Customer Feedback | PLLC Enterprise</title>
    <meta name="description" content="Share your feedback with us. We value your opinion and use it to improve our products and services.">
    
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

        .feedback-page {
            background: var(--white);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(10, 61, 98, 0.08);
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eef2f6;
            text-align: center;
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

        /* Form Styles */
        .feedback-form {
            background: var(--bg-light);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
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

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* Star Rating */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
            gap: 5px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 2rem;
            color: #cbd5e1;
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #fbbf24;
        }

        .rating-text {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 8px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background: #0080c0;
            transform: translateY(-2px);
        }

        /* Sentiment Analysis Box */
        .reaction-box {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            display: none;
        }

        .reaction-box.show {
            display: block;
        }

        .sentiment-analysis-box {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            padding: 25px;
            border-radius: 16px;
            color: white;
        }

        .sentiment-analysis-box h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .sentiment-content {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .sentiment-icon {
            font-size: 3rem;
        }

        .sentiment-details {
            flex: 1;
        }

        .sentiment-label {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--accent-blue);
        }

        .sentiment-summary {
            margin-top: 8px;
            font-size: 14px;
            opacity: 0.95;
        }

        /* Loading Indicator */
        .loading-indicator {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--bg-light);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            padding: 30px;
            text-align: center;
        }

        .modal-content h3 {
            color: var(--primary-blue);
            margin-bottom: 15px;
        }

        .modal-content p {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .modal-btn {
            padding: 10px 25px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 0 15px;
                margin: 20px auto;
            }
            .feedback-page {
                padding: 20px;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .sentiment-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar is included from components/navbar.php -->

    <!-- Main Content -->
    <main class="main-content">
        <div class="feedback-page">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-comment-dots"></i> Customer Feedback</h1>
                <p class="page-subtitle">We value your opinion! Please share your experience with us.</p>
            </div>
            
            <!-- Alert Messages Container -->
            <div id="alertContainer"></div>
            
            <form id="feedbackForm" class="feedback-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="customerName"><i class="fas fa-user"></i> Your Name <span class="required">*</span></label>
                        <input type="text" id="customerName" name="customerName" required 
                               value="<?php echo htmlspecialchars($user_fullname); ?>"
                               placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="customerEmail"><i class="fas fa-envelope"></i> Email Address <span class="required">*</span></label>
                        <input type="email" id="customerEmail" name="customerEmail" required 
                               value="<?php echo htmlspecialchars($user_email); ?>"
                               placeholder="your@email.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="productId"><i class="fas fa-box"></i> Product (Optional)</label>
                        <select id="productId" name="productId">
                            <option value="">Select a product (optional)</option>
                            <option value="1">Product 1</option>
                            <option value="2">Product 2</option>
                            <option value="3">Product 3</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="feedbackType"><i class="fas fa-tag"></i> Feedback Type <span class="required">*</span></label>
                        <select id="feedbackType" name="feedbackType" required>
                            <option value="">Select feedback type</option>
                            <option value="product">Product Review</option>
                            <option value="service">Service Review</option>
                            <option value="website">Website Experience</option>
                            <option value="delivery">Delivery Experience</option>
                            <option value="support">Customer Support</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="complaint">Complaint</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="recommendation"><i class="fas fa-thumbs-up"></i> Would you recommend us?</label>
                    <select id="recommendation" name="recommendation">
                        <option value="">Select an option</option>
                        <option value="definitely">Definitely Yes</option>
                        <option value="probably">Probably Yes</option>
                        <option value="maybe">Maybe</option>
                        <option value="probably-not">Probably Not</option>
                        <option value="definitely-not">Definitely Not</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="rating"><i class="fas fa-star"></i> Overall Rating <span class="required">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5" title="5 stars">★</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" title="4 stars">★</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" title="3 stars">★</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" title="2 stars">★</label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" title="1 star">★</label>
                    </div>
                    <div class="rating-text" id="ratingText">Click on a star to rate your experience</div>
                </div>
                
                <div class="form-group">
                    <label for="feedbackMessage"><i class="fas fa-comment"></i> Your Detailed Feedback <span class="required">*</span></label>
                    <textarea id="feedbackMessage" name="feedbackMessage" rows="6" 
                              placeholder="Please share your detailed feedback. Tell us what you liked or what we can improve..." 
                              required></textarea>
                </div>
                
                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Submit Feedback
                </button>
            </form>
            
            <!-- Sentiment Analysis Result Box -->
            <div id="reactionBox" class="reaction-box">
                <div id="loadingIndicator" class="loading-indicator" style="display: none;">
                    <div class="spinner"></div>
                    <p>Analyzing your feedback...</p>
                </div>
                <div id="sentimentResult" class="sentiment-analysis-box" style="display: none;">
                    <h3><i class="fas fa-chart-line"></i> Feedback Analysis Complete!</h3>
                    <div class="sentiment-content">
                        <div class="sentiment-icon" id="sentimentIcon">😊</div>
                        <div class="sentiment-details">
                            <div class="sentiment-label" id="sentimentLabel">Positive</div>
                            <div class="sentiment-summary" id="sentimentSummary">Thank you for your positive feedback!</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    // Star rating text update
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const ratingText = document.getElementById('ratingText');
    
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            const texts = {
                1: '⭐ Very Poor - We apologize for your experience',
                2: '⭐⭐ Poor - We\'ll work to improve',
                3: '⭐⭐⭐ Average - Thanks for your feedback',
                4: '⭐⭐⭐⭐ Good - Glad you enjoyed it',
                5: '⭐⭐⭐⭐⭐ Excellent - Thank you!'
            };
            ratingText.textContent = texts[rating] || 'Click on a star to rate your experience';
        });
    });
    
    // Update rating text on page load if any rating is selected
    const selectedRating = document.querySelector('input[name="rating"]:checked');
    if (selectedRating) {
        const event = new Event('change');
        selectedRating.dispatchEvent(event);
    }
    
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
    
    // Show modal
    function showModal(title, message, onClose) {
        const modal = document.getElementById('modal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalClose = document.getElementById('modalClose');
        
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.classList.add('active');
        
        const closeHandler = () => {
            modal.classList.remove('active');
            modalClose.removeEventListener('click', closeHandler);
            if (onClose) onClose();
        };
        
        modalClose.addEventListener('click', closeHandler);
        
        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeHandler();
            }
        });
    }
    
    // Analyze sentiment via AJAX
    async function analyzeSentiment(feedback) {
        const loadingIndicator = document.getElementById('loadingIndicator');
        const sentimentResult = document.getElementById('sentimentResult');
        const reactionBox = document.getElementById('reactionBox');
        
        loadingIndicator.style.display = 'block';
        sentimentResult.style.display = 'none';
        reactionBox.classList.add('show');
        
        try {
            const formData = new FormData();
            formData.append('action', 'analyze_sentiment');
            formData.append('feedback', feedback);
            
            const response = await fetch('feedback.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            
            loadingIndicator.style.display = 'none';
            sentimentResult.style.display = 'block';
            
            if (data.success) {
                document.getElementById('sentimentIcon').textContent = data.icon;
                document.getElementById('sentimentLabel').textContent = data.sentiment;
                document.getElementById('sentimentSummary').textContent = data.analysis;
            } else {
                document.getElementById('sentimentIcon').textContent = '😐';
                document.getElementById('sentimentLabel').textContent = 'Unable to analyze';
                document.getElementById('sentimentSummary').textContent = 'Please try again later.';
            }
        } catch (error) {
            loadingIndicator.style.display = 'none';
            sentimentResult.style.display = 'block';
            document.getElementById('sentimentIcon').textContent = '😐';
            document.getElementById('sentimentLabel').textContent = 'Error';
            document.getElementById('sentimentSummary').textContent = 'Unable to analyze sentiment. Please try again.';
        }
    }
    
    // Handle form submission
    document.getElementById('feedbackForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('action', 'submit_feedback');
        formData.append('customer_name', document.getElementById('customerName').value);
        formData.append('customer_email', document.getElementById('customerEmail').value);
        formData.append('product_id', document.getElementById('productId').value);
        formData.append('feedback_type', document.getElementById('feedbackType').value);
        formData.append('recommendation', document.getElementById('recommendation').value);
        formData.append('rating', document.querySelector('input[name="rating"]:checked')?.value || '');
        formData.append('feedback_message', document.getElementById('feedbackMessage').value);
        
        try {
            const response = await fetch('feedback.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert(data.message, 'success');
                
                // Show sentiment result if available
                if (data.sentiment) {
                    document.getElementById('sentimentIcon').textContent = data.sentiment.icon;
                    document.getElementById('sentimentLabel').textContent = data.sentiment.label;
                    document.getElementById('sentimentSummary').textContent = data.sentiment.summary;
                    document.getElementById('reactionBox').classList.add('show');
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('sentimentResult').style.display = 'block';
                }
                
                // Reset form
                document.getElementById('feedbackForm').reset();
                ratingText.textContent = 'Click on a star to rate your experience';
                document.getElementById('reactionBox').classList.remove('show');
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
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
    
    // Real-time sentiment analysis on text input (debounced)
    let debounceTimer;
    document.getElementById('feedbackMessage').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const feedback = this.value.trim();
        
        if (feedback.length > 20) {
            debounceTimer = setTimeout(() => {
                analyzeSentiment(feedback);
            }, 1000);
        }
    });
    </script>
    
    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle"></h3>
            <p id="modalMessage"></p>
            <button id="modalClose" class="modal-btn">OK</button>
        </div>
    </div>
</body>
</html>