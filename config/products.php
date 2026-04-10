<?php
require_once 'database.php';

class ProductManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    // Get all products
    public function getAllProducts($category = null, $limit = null) {
        try {
            $sql = "SELECT * FROM products WHERE is_available = 1";
            $params = [];
            
            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = (int)$limit;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch products: ' . $e->getMessage()];
        }
    }
    
    // Get product by ID
    public function getProductById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ? AND is_available = 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch product: ' . $e->getMessage()];
        }
    }
    
    // Search products
    public function searchProducts($query, $category = null) {
        try {
            $sql = "SELECT * FROM products WHERE is_available = 1 AND (name LIKE ? OR description LIKE ?)";
            $params = ["%$query%", "%$query%"];
            
            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            $sql .= " ORDER BY name ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ['error' => 'Search failed: ' . $e->getMessage()];
        }
    }
    
    // Add product to cart
    public function addToCart($userId, $productId, $quantity = 1, $sessionId = null) {
        try {
            // Check if item already in cart
            $stmt = $this->pdo->prepare("
                SELECT id, quantity FROM cart 
                WHERE user_id = ? AND product_id = ? AND (session_id = ? OR session_id IS NULL)
            ");
            $stmt->execute([$userId, $productId, $sessionId]);
            $existingItem = $stmt->fetch();
            
            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem['quantity'] + $quantity;
                $stmt = $this->pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $existingItem['id']]);
            } else {
                // Add new item
                $stmt = $this->pdo->prepare("
                    INSERT INTO cart (user_id, product_id, quantity, session_id) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $productId, $quantity, $sessionId]);
            }
            
            return ['success' => true, 'message' => 'Product added to cart'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to add to cart: ' . $e->getMessage()];
        }
    }
    
    // Get cart items
    public function getCartItems($userId, $sessionId = null) {
        try {
            $sql = "
                SELECT c.*, p.name, p.price, p.image, p.stock, p.is_available 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? AND (c.session_id = ? OR c.session_id IS NULL)
                ORDER BY c.created_at DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $sessionId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch cart items: ' . $e->getMessage()];
        }
    }
    
    // Update cart item quantity
    public function updateCartQuantity($cartId, $quantity) {
        try {
            if ($quantity <= 0) {
                // Remove item from cart
                $stmt = $this->pdo->prepare("DELETE FROM cart WHERE id = ?");
                $stmt->execute([$cartId]);
            } else {
                // Update quantity
                $stmt = $this->pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$quantity, $cartId]);
            }
            
            return ['success' => true, 'message' => 'Cart updated'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update cart: ' . $e->getMessage()];
        }
    }
    
    // Remove from cart
    public function removeFromCart($cartId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM cart WHERE id = ?");
            $stmt->execute([$cartId]);
            
            return ['success' => true, 'message' => 'Item removed from cart'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to remove from cart: ' . $e->getMessage()];
        }
    }
    
    // Clear cart
    public function clearCart($userId, $sessionId = null) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ? AND (session_id = ? OR session_id IS NULL)");
            $stmt->execute([$userId, $sessionId]);
            
            return ['success' => true, 'message' => 'Cart cleared'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to clear cart: ' . $e->getMessage()];
        }
    }
    
    // Create order
    public function createOrder($userId, $cartItems, $shippingAddress, $paymentMethod) {
        try {
            $this->pdo->beginTransaction();
            
            // Generate order number
            $orderNumber = 'PLLC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Calculate total
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }
            
            // Create order
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $orderNumber, $totalAmount, $shippingAddress, $paymentMethod]);
            $orderId = $this->pdo->lastInsertId();
            
            // Create order items
            foreach ($cartItems as $item) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $stmt = $this->pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            $this->pdo->commit();
            
            return ['success' => true, 'message' => 'Order created successfully', 'order_id' => $orderId, 'order_number' => $orderNumber];
        } catch (PDOException $e) {
            $this->pdo->rollback();
            return ['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()];
        }
    }
    
    // Get user orders
    public function getUserOrders($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.*, 
                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
                FROM orders o 
                WHERE o.user_id = ? 
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch orders: ' . $e->getMessage()];
        }
    }
    
    // Get order details
    public function getOrderDetails($orderId, $userId = null) {
        try {
            $sql = "
                SELECT o.*, 
                       GROUP_CONCAT(
                           CONCAT(p.name, ' (Qty: ', oi.quantity, ')') 
                           SEPARATOR ', '
                       ) as items
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE o.id = ?
            ";
            
            $params = [$orderId];
            if ($userId) {
                $sql .= " AND o.user_id = ?";
                $params[] = $userId;
            }
            
            $sql .= " GROUP BY o.id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch order details: ' . $e->getMessage()];
        }
    }
}

// Initialize product manager
$productManager = new ProductManager();
?>
