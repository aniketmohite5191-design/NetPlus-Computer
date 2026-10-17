<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

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
    <title>Order Confirmation - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: white;
            font-size: 2.5rem;
        }
        
        .order-details {
            background: #f9fafb;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            text-align: left;
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
            
            .confirmation-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Desktop Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm desktop-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-laptop text-primary"></i> NETplus Computers
            </a>
            
            <div class="d-flex align-items-center">
                <a href="orders.php" class="btn btn-outline-primary me-2">My Orders</a>
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="confirmation-card">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    
                    <h3 class="mb-3">Order Placed Successfully!</h3>
                    <p class="text-muted mb-4">Thank you for your purchase. Your order has been received and is being processed.</p>
                    
                    <div class="order-details">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Order Number</h6>
                                <p class="fw-bold fs-5"><?php echo htmlspecialchars($order['order_number']); ?></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="text-muted">Order Date</h6>
                                <p><?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">Order Items</h6>
                        <?php foreach ($order_items as $item): ?>
                            <div class="d-flex align-items-center mb-3 p-2 bg-white rounded">
                                <img src="assets/images/products/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" 
                                     class="me-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="mb-0 text-muted small">Qty: <?php echo $item['quantity']; ?></p>
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
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span class="text-primary">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Payment Method</h6>
                                <p class="text-uppercase"><?php echo str_replace('_', ' ', htmlspecialchars($order['payment_method'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Order Status</h6>
                                <span class="badge bg-warning"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="generate-invoice.php?order_id=<?php echo $order_id; ?>" class="btn btn-success me-2" target="_blank">
                            <i class="fas fa-download me-2"></i>Download Invoice
                        </a>
                        <a href="orders.php" class="btn btn-primary me-2">
                            <i class="fas fa-list me-2"></i>View My Orders
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
