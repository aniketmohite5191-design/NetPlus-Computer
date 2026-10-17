<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$order_query = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = :order_id AND o.user_id = :user_id";
$order_stmt = $db->prepare($order_query);
$order_stmt->bindParam(':order_id', $order_id);
$order_stmt->bindParam(':user_id', $_SESSION['user_id']);
$order_stmt->execute();
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setFlashMessage('Order not found.', 'danger');
    header('Location: orders.php');
    exit();
}

// Get order items
$items_query = "SELECT oi.*, p.name, p.image FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->bindParam(':order_id', $order_id);
$items_stmt->execute();
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal
$subtotal = 0;
foreach ($order_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax_rate = floatval(getSetting($db, 'tax_rate') ?? 18);
$tax_amount = calculateTax($subtotal, $tax_rate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlspecialchars($order['order_number']); ?> - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
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
                <a href="orders.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-arrow-left me-1"></i>Back to Orders
                </a>
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
            <a href="orders.php" class="btn btn-outline-primary btn-sm">Back</a>
        </div>
    </div>

    <div class="container mt-4">
        <h2 class="mb-4">Order Details</h2>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Order Info -->
                <div class="order-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Order #<?php echo htmlspecialchars($order['order_number']); ?></h5>
                            <p class="text-muted mb-0">Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <span class="badge bg-<?php 
                            echo $order['status'] === 'delivered' ? 'success' : 
                                 ($order['status'] === 'pending' ? 'warning' : 
                                 ($order['status'] === 'processing' ? 'info' : 
                                 ($order['status'] === 'shipped' ? 'primary' : 'secondary'))); 
                        ?> fs-6">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <!-- Order Items -->
                    <h6 class="mb-3">Order Items</h6>
                    <?php foreach ($order_items as $item): ?>
                        <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                            <img src="assets/images/products/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" 
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" 
                                 class="me-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="mb-0 text-muted">Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold fs-5">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <!-- Order Summary -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Shipping Address</h6>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Billing Address</h6>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Order Summary Card -->
                <div class="order-card p-4">
                    <h6 class="mb-3">Order Summary</h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (<?php echo $tax_rate; ?>%):</span>
                        <span>₹<?php echo number_format($tax_amount, 2); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping:</span>
                        <span class="text-success">Free</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total:</span>
                        <span class="text-primary">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h6>Payment Information</h6>
                        <p class="mb-1"><strong>Method:</strong> <?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        <p class="mb-0"><strong>Status:</strong> 
                            <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </p>
                    </div>
                    
                    <?php if ($order['transaction_id']): ?>
                        <p class="mb-0"><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
            <a href="orders.php" class="nav-link active">
                <i class="fas fa-shopping-bag fa-lg"></i>
                <span>Orders</span>
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
