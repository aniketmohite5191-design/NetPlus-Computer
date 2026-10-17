<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.image, p.stock_quantity FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = :user_id AND p.status = 'active'";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->bindParam(':user_id', $_SESSION['user_id']);
$cart_stmt->execute();
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($cart_items) === 0) {
    setFlashMessage('Your cart is empty. Add some products first.', 'warning');
    header('Location: products.php');
    exit();
}

// Get user details
$user_query = "SELECT * FROM users WHERE id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(':user_id', $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Get settings
$tax_rate = floatval(getSetting($db, 'tax_rate') ?? 18);
$tax_amount = calculateTax($subtotal, $tax_rate);
$shipping_cost = floatval(getSetting($db, 'shipping_cost') ?? 0);
$total = $subtotal + $tax_amount + $shipping_cost;

// Handle order submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billing_name = sanitizeInput($_POST['billing_name'] ?? '');
    $billing_email = sanitizeInput($_POST['billing_email'] ?? '');
    $billing_phone = sanitizeInput($_POST['billing_phone'] ?? '');
    $billing_address = sanitizeInput($_POST['billing_address'] ?? '');
    $shipping_address = sanitizeInput($_POST['shipping_address'] ?? '');
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    
    if (empty($billing_name) || empty($billing_email) || empty($billing_phone) || empty($billing_address) || empty($payment_method)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($billing_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Create order
        $order_number = generateOrderNumber();
        
        try {
            $db->beginTransaction();
            
            // Insert order
            $order_query = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_status, 
                          payment_method, shipping_address, billing_address) 
                          VALUES (:user_id, :order_number, :total_amount, 'pending', 'pending', 
                          :payment_method, :shipping_address, :billing_address)";
            $order_stmt = $db->prepare($order_query);
            $order_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $order_stmt->bindParam(':order_number', $order_number);
            $order_stmt->bindParam(':total_amount', $total);
            $order_stmt->bindParam(':payment_method', $payment_method);
            $shipping_addr = $shipping_address ?: $billing_address;
            $order_stmt->bindParam(':shipping_address', $shipping_addr);
            $order_stmt->bindParam(':billing_address', $billing_address);
            $order_stmt->execute();
            
            $order_id = $db->lastInsertId();
            
            // Insert order items
            foreach ($cart_items as $item) {
                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES (:order_id, :product_id, :quantity, :price)";
                $item_stmt = $db->prepare($item_query);
                $item_stmt->bindParam(':order_id', $order_id);
                $item_stmt->bindParam(':product_id', $item['product_id']);
                $item_stmt->bindParam(':quantity', $item['quantity']);
                $item_stmt->bindParam(':price', $item['price']);
                $item_stmt->execute();
                
                // Update stock
                $stock_query = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id";
                $stock_stmt = $db->prepare($stock_query);
                $stock_stmt->bindParam(':quantity', $item['quantity']);
                $stock_stmt->bindParam(':product_id', $item['product_id']);
                $stock_stmt->execute();
            }
            
            // Clear cart
            $clear_query = "DELETE FROM cart WHERE user_id = :user_id";
            $clear_stmt = $db->prepare($clear_query);
            $clear_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $clear_stmt->execute();
            
            $db->commit();
            
            setFlashMessage("Order placed successfully! Order number: $order_number", 'success');
            header("Location: order-confirmation.php?order_id=$order_id");
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Order processing failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .checkout-section {
            background: #f9fafb;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .payment-method {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover, .payment-method.active {
            border-color: var(--primary-color);
            background: #eff6ff;
        }
        
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            position: sticky;
            top: 20px;
        }
        
        .cart-item-small {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .cart-item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
        }
        
        @media (max-width: 768px) {
            .desktop-nav {
                display: none !important;
            }
            
            .checkout-section {
                padding: 15px;
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
                <a href="cart.php" class="btn btn-outline-primary me-2">Back to Cart</a>
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
            <a href="cart.php" class="btn btn-outline-primary btn-sm">Back to Cart</a>
        </div>
    </div>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-credit-card me-2"></i>Checkout</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="checkout.php">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Billing Information -->
                    <div class="checkout-section">
                        <h5 class="mb-4"><i class="fas fa-user me-2"></i>Billing Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="billing_name" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="billing_email" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="billing_phone" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Billing Address *</label>
                            <textarea name="billing_address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Shipping Address (if different)</label>
                            <textarea name="shipping_address" class="form-control" rows="3" placeholder="Leave blank if same as billing address"></textarea>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h5 class="mb-4"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="payment-method" onclick="selectPayment('cod')">
                                    <input type="radio" name="payment_method" value="cod" id="payment_cod" required>
                                    <label for="payment_cod" class="d-flex align-items-center mb-0">
                                        <i class="fas fa-money-bill-wave fa-lg me-3 text-success"></i>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <p class="mb-0 small text-muted">Pay when you receive</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="payment-method" onclick="selectPayment('upi')">
                                    <input type="radio" name="payment_method" value="upi" id="payment_upi">
                                    <label for="payment_upi" class="d-flex align-items-center mb-0">
                                        <i class="fas fa-mobile-alt fa-lg me-3 text-primary"></i>
                                        <div>
                                            <strong>UPI Payment</strong>
                                            <p class="mb-0 small text-muted">Google Pay, PhonePe, Paytm</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="payment-method" onclick="selectPayment('credit_card')">
                                    <input type="radio" name="payment_method" value="credit_card" id="payment_credit_card">
                                    <label for="payment_credit_card" class="d-flex align-items-center mb-0">
                                        <i class="fas fa-credit-card fa-lg me-3 text-info"></i>
                                        <div>
                                            <strong>Credit Card</strong>
                                            <p class="mb-0 small text-muted">Visa, Mastercard, RuPay</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="payment-method" onclick="selectPayment('debit_card')">
                                    <input type="radio" name="payment_method" value="debit_card" id="payment_debit_card">
                                    <label for="payment_debit_card" class="d-flex align-items-center mb-0">
                                        <i class="fas fa-credit-card fa-lg me-3 text-warning"></i>
                                        <div>
                                            <strong>Debit Card</strong>
                                            <p class="mb-0 small text-muted">Visa, Mastercard, RuPay</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="payment-method" onclick="selectPayment('net_banking')">
                                    <input type="radio" name="payment_method" value="net_banking" id="payment_net_banking">
                                    <label for="payment_net_banking" class="d-flex align-items-center mb-0">
                                        <i class="fas fa-university fa-lg me-3 text-danger"></i>
                                        <div>
                                            <strong>Net Banking</strong>
                                            <p class="mb-0 small text-muted">All major banks supported</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="payment-method" onclick="selectPayment('wallet')">
                                    <input type="radio" name="payment_method" value="wallet" id="payment_wallet">
                                    <label for="payment_wallet" class="d-flex align-items-center mb-0">
                                        <i class="fas fa-wallet fa-lg me-3 text-secondary"></i>
                                        <div>
                                            <strong>Mobile Wallet</strong>
                                            <p class="mb-0 small text-muted">Paytm, Mobikwik, etc.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid d-md-none mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check me-2"></i>Place Order - ₹<?php echo number_format($total, 2); ?>
                        </button>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h5 class="mb-4">Order Summary</h5>
                        
                        <!-- Cart Items -->
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item-small">
                                <img src="assets/images/products/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" 
                                     class="cart-item-image" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 small"><?php echo htmlspecialchars(truncateText($item['name'], 30)); ?></h6>
                                    <p class="mb-0 small text-muted">
                                        ₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (<?php echo $tax_rate; ?>%):</span>
                            <span>₹<?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span><?php echo $shipping_cost > 0 ? '₹' . number_format($shipping_cost, 2) : 'Free'; ?></span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total:</strong>
                            <strong class="text-primary fs-5">₹<?php echo number_format($total, 2); ?></strong>
                        </div>
                        
                        <div class="d-none d-md-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check me-2"></i>Place Order
                            </button>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>Secure Checkout
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 NETplus Computers. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPayment(method) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            document.querySelector(`[onclick="selectPayment('${method}')"]`).classList.add('active');
            document.getElementById(`payment_${method}`).checked = true;
        }
    </script>
</body>
</html>
