<?php
// File: C:\xampp\htdocs\pllcg82\admin\edit_product.php

require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../auth/admin_login.php');
    exit;
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$product_id = intval($_GET['id']);
$error = '';
$success = '';

// Fetch product details
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: admin_dashboard.php');
        exit;
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = 'Error fetching product details';
}

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
    $sku = trim($_POST['sku']);
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = 'Product name is required';
    if ($price <= 0) $errors[] = 'Valid price is required';
    if (empty($category)) $errors[] = 'Category is required';
    if ($stock < 0) $errors[] = 'Stock cannot be negative';
    if ($min_stock < 0) $errors[] = 'Minimum stock cannot be negative';
    if (empty($sku)) $errors[] = 'SKU is required';
    
    // Handle image upload
    $image_path = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../image/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = strtolower(str_replace(' ', '_', $name)) . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists and not default
                if (!empty($product['image']) && file_exists('../' . $product['image']) && $product['image'] != 'image/placeholder.jpg') {
                    unlink('../' . $product['image']);
                }
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
            $stmt = $pdo->prepare("UPDATE products SET 
                name = ?, 
                price = ?, 
                category = ?, 
                subcategory = ?, 
                image = ?, 
                description = ?, 
                stock = ?, 
                min_stock = ?, 
                is_available = ?, 
                sku = ? 
                WHERE id = ?");
            
            $stmt->execute([$name, $price, $category, $subcategory, $image_path, $description, $stock, $min_stock, $is_available, $sku, $product_id]);
            
            $success = 'Product updated successfully!';
            
            // Refresh product data
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Get categories and subcategories for dropdowns
$categories = ['ebikes', 'batteries', 'spareparts'];
$subcategories = [
    'ebikes' => ['raven', 'echo', 'supreme', 'skye', 'zhi18', 'adventure', 'blaze', 'dc', 'dusk', 'cargo', 'p1plus', 'pau', 'ragnar', 'storm', 'summer', 'supremeplus'],
    'batteries' => ['48v', '60v', 'lithium', 'standard', 'heavy-duty'],
    'spareparts' => ['tires', 'brakes', 'motor', 'lighting', 'accessories']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-light);
        }

        .header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            color: var(--accent-blue);
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: var(--accent-blue);
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .form-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(10, 61, 98, 0.08);
            border: 1px solid rgba(10, 61, 98, 0.1);
        }

        .form-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent-blue);
        }

        .form-header h1 {
            color: var(--primary-blue);
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0,168,232,0.1);
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
        }

        .checkbox-group input {
            width: auto;
            width: 20px;
            height: 20px;
        }

        .checkbox-group label {
            margin-bottom: 0;
        }

        .current-image {
            margin-top: 10px;
            padding: 15px;
            background: var(--bg-light);
            border-radius: 12px;
            text-align: center;
        }

        .current-image img {
            max-width: 200px;
            border-radius: 12px;
        }

        .btn-submit {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background: #0080c0;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="admin_dashboard.php" class="logo">
                <i class="fas fa-bicycle"></i> PLLC Admin
            </a>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-edit"></i> Edit Product</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Price (₱) *</label>
                        <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" id="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo $product['category'] == $cat ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subcategory</label>
                        <select name="subcategory" id="subcategory">
                            <option value="">Select Subcategory</option>
                            <?php 
                            $current_category = $product['category'];
                            if (isset($subcategories[$current_category])):
                                foreach ($subcategories[$current_category] as $subcat):
                            ?>
                                <option value="<?php echo $subcat; ?>" <?php echo $product['subcategory'] == $subcat ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(str_replace('-', ' ', $subcat)); ?>
                                </option>
                            <?php 
                                endforeach;
                            endif; 
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" name="sku" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Minimum Stock Level</label>
                        <input type="number" name="min_stock" value="<?php echo $product['min_stock']; ?>">
                        <small>Alert when stock falls below this level</small>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="is_available" value="1" <?php echo $product['is_available'] ? 'checked' : ''; ?>>
                        <label>Product Available for Sale</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*">
                    <div class="current-image">
                        <p>Current Image:</p>
                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Update Product
                </button>
            </form>
        </div>
    </div>

    <script>
        // Dynamic subcategory update based on category selection
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
    </script>
</body>
</html>