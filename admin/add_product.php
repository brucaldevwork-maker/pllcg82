<?php
// File: C:\xampp\htdocs\pllcg82\admin\add_product.php

require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../auth/admin_login.php');
    exit;
}

$error = '';
$success = '';

// Categories and subcategories
$categories = ['ebikes', 'batteries', 'spareparts'];
$subcategories = [
    'ebikes' => ['raven', 'echo', 'supreme', 'skye', 'zhi18', 'adventure', 'blaze', 'dc', 'dusk', 'cargo', 'p1plus', 'pau', 'ragnar', 'storm', 'summer', 'supremeplus'],
    'batteries' => ['48v', '60v', 'lithium', 'standard', 'heavy-duty'],
    'spareparts' => ['tires', 'brakes', 'motor', 'lighting', 'accessories']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $description = trim($_POST['description']);
    $stock = intval($_POST['stock']);
    $min_stock = intval($_POST['min_stock']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = 'Product name is required';
    if ($price <= 0) $errors[] = 'Valid price is required';
    if (empty($category)) $errors[] = 'Category is required';
    if ($stock < 0) $errors[] = 'Stock cannot be negative';
    if ($min_stock < 0) $errors[] = 'Minimum stock cannot be negative';
    
    // Handle image upload
    $image_path = 'image/placeholder.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../image/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = strtolower(str_replace(' ', '_', $name)) . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'image/' . $new_filename;
            } else {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp, avif';
        }
    }
    
    if (empty($errors)) {
        try {
            // Insert into products table - SKU will be NULL by default
            $stmt = $pdo->prepare("INSERT INTO products (name, price, category, subcategory, image, rating, reviews, description, stock, min_stock, is_available) 
                VALUES (?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?)");
            
            $stmt->execute([
                $name, 
                $price, 
                $category, 
                $subcategory, 
                $image_path, 
                $description, 
                $stock, 
                $min_stock, 
                $is_available
            ]);
            
            $product_id = $pdo->lastInsertId();
            $success = 'Product added successfully! Product ID: ' . $product_id;
            
            // Clear form data
            $_POST = array();
            
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Admin Panel</title>
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
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            color: var(--accent-blue);
        }

        .back-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 24px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .back-btn:hover {
            background: var(--accent-blue);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .form-card {
            background: var(--white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .form-header {
            background: linear-gradient(135deg, #F8F9FA 0%, #FFFFFF 100%);
            padding: 30px;
            border-bottom: 2px solid rgba(0,168,232,0.2);
        }

        .form-header h1 {
            color: var(--primary-blue);
            font-size: 28px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-header h1 i {
            color: var(--accent-blue);
        }

        .form-header p {
            margin-top: 8px;
            color: var(--text-light);
        }

        .form-body {
            padding: 30px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border-left: 4px solid var(--danger);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        .form-group label i {
            margin-right: 8px;
            color: var(--accent-blue);
        }

        .form-group label .required {
            color: var(--danger);
            margin-left: 4px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0,168,232,0.1);
        }

        .form-group small {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: var(--text-light);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
        }

        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
        }

        .image-preview {
            margin-top: 15px;
            max-width: 200px;
            max-height: 200px;
            border-radius: 12px;
            border: 2px dashed var(--gray-300);
            padding: 10px;
            background: var(--gray-100);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #0080c0 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,168,232,0.3);
        }

        .section-divider {
            margin: 25px 0 20px;
            position: relative;
        }

        .section-divider h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-blue);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--white);
            padding-right: 15px;
        }

        .section-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--accent-blue) 0%, transparent 100%);
            z-index: -1;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .form-header {
                padding: 20px;
            }
            
            .form-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="admin_dashboard.php" class="logo">
                <i class="fas fa-store"></i> PLLC Admin
            </a>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h1>
                    <i class="fas fa-plus-circle"></i>
                    Add New Product
                </h1>
                <p>Fill in the details to add a new product to your store</p>
            </div>

            <div class="form-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="section-divider">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Product Name <span class="required">*</span></label>
                            <input type="text" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                   placeholder="e.g., PLLC Raven E-Bike" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-dollar-sign"></i> Price (₱) <span class="required">*</span></label>
                            <input type="number" name="price" step="0.01" value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" 
                                   placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-folder"></i> Category <span class="required">*</span></label>
                            <select name="category" id="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] == $cat) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-layer-group"></i> Subcategory</label>
                            <select name="subcategory" id="subcategory">
                                <option value="">Select Subcategory</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-boxes"></i> Stock Quantity <span class="required">*</span></label>
                            <input type="number" name="stock" value="<?php echo isset($_POST['stock']) ? $_POST['stock'] : 0; ?>" 
                                   min="0" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-chart-line"></i> Minimum Stock Level</label>
                            <input type="number" name="min_stock" value="<?php echo isset($_POST['min_stock']) ? $_POST['min_stock'] : 5; ?>" 
                                   min="0">
                            <small>Alert when stock falls below this level</small>
                        </div>
                    </div>

                    <div class="section-divider">
                        <h3><i class="fas fa-align-left"></i> Product Description</h3>
                    </div>

                    <div class="form-group">
                        <textarea name="description" rows="6" placeholder="Detailed product description..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <small>Include features, specifications, and benefits</small>
                    </div>

                    <div class="section-divider">
                        <h3><i class="fas fa-image"></i> Product Image</h3>
                    </div>

                    <div class="form-group">
                        <input type="file" name="image" accept="image/*" id="productImage">
                        <small>Allowed: JPG, JPEG, PNG, GIF, WEBP, AVIF. Leave empty for placeholder image</small>
                        <div id="imagePreviewContainer" style="display: none;">
                            <img id="imagePreview" class="image-preview" alt="Preview">
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="is_available" value="1" id="isAvailable" 
                               <?php echo !isset($_POST['is_available']) || $_POST['is_available'] == '1' ? 'checked' : ''; ?>>
                        <label for="isAvailable">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i> 
                            Product Available for Sale
                        </label>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Category and subcategory handling
        const categorySelect = document.getElementById('category');
        const subcategorySelect = document.getElementById('subcategory');
        
        const subcategories = <?php echo json_encode($subcategories); ?>;
        
        categorySelect.addEventListener('change', function() {
            const category = this.value;
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            
            if (category && subcategories[category]) {
                subcategories[category].forEach(subcat => {
                    const option = document.createElement('option');
                    option.value = subcat;
                    option.textContent = subcat.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    subcategorySelect.appendChild(option);
                });
            }
        });
        
        // Trigger change if category was pre-selected
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
            <?php if (isset($_POST['subcategory'])): ?>
            setTimeout(() => {
                subcategorySelect.value = '<?php echo $_POST['subcategory']; ?>';
            }, 100);
            <?php endif; ?>
        }
        
        // Image preview
        const imageInput = document.getElementById('productImage');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imagePreview = document.getElementById('imagePreview');
        
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                imagePreviewContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>