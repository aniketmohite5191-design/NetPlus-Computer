<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $cart_id = isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0;
    
    switch ($action) {
        case 'add':
            $product_id = intval($_GET['id'] ?? 0);
            $quantity = intval($_GET['quantity'] ?? 1);
            
            if ($product_id > 0) {
                $cart_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)
                               ON DUPLICATE KEY UPDATE quantity = quantity + :quantity";
                $cart_stmt = $db->prepare($cart_query);
                $cart_stmt->bindParam(':user_id', $_SESSION['user_id']);
                $cart_stmt->bindParam(':product_id', $product_id);
                $cart_stmt->bindParam(':quantity', $quantity);
                $cart_stmt->execute();
                
                setFlashMessage('Product added to cart!', 'success');
            }
            break;
            
        case 'remove':
            if ($cart_id > 0) {
                $delete_query = "DELETE FROM cart WHERE id = :cart_id AND user_id = :user_id";
                $delete_stmt = $db->prepare($delete_query);
                $delete_stmt->bindParam(':cart_id', $cart_id);
                $delete_stmt->bindParam(':user_id', $_SESSION['user_id']);
                $delete_stmt->execute();
                
                setFlashMessage('Item removed from cart.', 'success');
            }
            break;
            
        case 'update':
            if ($cart_id > 0 && isset($_GET['quantity'])) {
                $quantity = intval($_GET['quantity']);
                if ($quantity > 0) {
                    $update_query = "UPDATE cart SET quantity = :quantity WHERE id = :cart_id AND user_id = :user_id";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(':quantity', $quantity);
                    $update_stmt->bindParam(':cart_id', $cart_id);
                    $update_stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $update_stmt->execute();
                }
            }
            break;
            
        case 'clear':
            $clear_query = "DELETE FROM cart WHERE user_id = :user_id";
            $clear_stmt = $db->prepare($clear_query);
            $clear_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $clear_stmt->execute();
            
            setFlashMessage('Cart cleared.', 'success');
            break;
    }
    
    header('Location: cart.php');
    exit();
}

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.image, p.stock_quantity FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = :user_id AND p.status = 'active'";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->bindParam(':user_id', $_SESSION['user_id']);
$cart_stmt->execute();
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Get tax rate from settings
$tax_rate = floatval(getSetting($db, 'tax_rate') ?? 18);
$tax_amount = calculateTax($subtotal, $tax_rate);
$total = $subtotal + $tax_amount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .cart-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
        }
        
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 1px solid #e5e7eb;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin: 0 10px;
        }
        
        .order-summary {
            background: #f9fafb;
            border-radius: 12px;
            padding: 25px;
        }
        
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            z-index: 1000;
            padding: 10px 0;
        }
        
        .mobile-bottom-nav .nav-item {
            flex: 1;
            text-align: center;
        }
        
        .mobile-bottom-nav .nav-link {
            color: #6b7280;
            padding: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.8rem;
        }
        
        .mobile-bottom-nav .nav-link.active {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .mobile-bottom-nav {
                display: flex;
            }
            
            .desktop-nav {
                display: none !important;
            }
            
            body {
                padding-bottom: 70px;
            }
            
            .cart-item {
                padding: 15px;
            }
            
            .cart-item-image {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Desktop Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm desktop-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-laptop text-primary"></i> NETplus Computers
            </a>
            
            <div class="d-flex align-items-center">
                <a href="products.php" class="btn btn-outline-primary me-2">Continue Shopping</a>
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Mobile Header -->
    <div class="d-md-none bg-white p-3 border-bottom sticky-top">
        <div class="d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-laptop text-primary"></i> NETplus
            </a>
            <a href="products.php" class="btn btn-outline-primary btn-sm">Continue Shopping</a>
        </div>
    </div>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h2>
        
        <?php displayFlashMessage(); ?>
        
        <?php if (count($cart_items) > 0): ?>
            <div class="row">
                <div class="col-lg-8">
                    <!-- Cart Items -->
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-2 col-3">
                                    <img src="assets/images/products/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" 
                                         class="cart-item-image" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="col-md-4 col-9">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="text-muted small mb-0">₹<?php echo number_format($item['price'], 2); ?> each</p>
                                </div>
                                <div class="col-md-3 col-6 mt-3 mt-md-0">
                                    <div class="d-flex align-items-center justify-content-md-center">
                                        <a href="cart.php?action=update&cart_id=<?php echo $item['id']; ?>&quantity=<?php echo max(1, $item['quantity'] - 1); ?>" 
                                           class="quantity-btn text-decoration-none text-dark">
                                            <i class="fas fa-minus"></i>
                                        </a>
                                        <input type="text" class="quantity-input" value="<?php echo $item['quantity']; ?>" readonly>
                                        <a href="cart.php?action=update&cart_id=<?php echo $item['id']; ?>&quantity=<?php echo min($item['stock_quantity'], $item['quantity'] + 1); ?>" 
                                           class="quantity-btn text-decoration-none text-dark">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mt-3 mt-md-0 text-md-center">
                                    <h6 class="mb-0 text-primary">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></h6>
                                </div>
                                <div class="col-md-1 col-2 mt-3 mt-md-0 text-end">
                                    <a href="cart.php?action=remove&cart_id=<?php echo $item['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove this item?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="products.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                        <a href="cart.php?action=clear" class="btn btn-outline-danger" onclick="return confirm('Clear all items from cart?')">
                            <i class="fas fa-trash me-2"></i>Clear Cart
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h5 class="mb-4">Order Summary</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (<?php echo $tax_rate; ?>%):</span>
                            <span>₹<?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total:</strong>
                            <strong class="text-primary">₹<?php echo number_format($total, 2); ?></strong>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                        </a>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>Secure Checkout
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart" style="font-size: 5rem; color: #e5e7eb;"></i>
                <h4 class="mt-4 text-muted">Your cart is empty</h4>
                <p class="text-muted mb-4">Looks like you haven't added any products to your cart yet.</p>
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 NETplus Computers. All rights reserved.</p>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <div class="nav-item">
            <a href="index.php" class="nav-link">
                <i class="fas fa-home fa-lg"></i>
                <span>Home</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="products.php" class="nav-link">
                <i class="fas fa-th-large fa-lg"></i>
                <span>Products</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="cart.php" class="nav-link active">
                <i class="fas fa-shopping-cart fa-lg"></i>
                <span>Cart</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user fa-lg"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
