<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get user's orders
$orders_query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
$orders_stmt = $db->prepare($orders_query);
$orders_stmt->bindParam(':user_id', $_SESSION['user_id']);
$orders_stmt->execute();
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .order-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .order-header {
            background: #f9fafb;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .order-body {
            padding: 20px;
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
            <a href="profile.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user"></i>
            </a>
        </div>
    </div>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-shopping-bag me-2"></i>My Orders</h2>
        
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <?php
                // Get order items
                $items_query = "SELECT oi.*, p.name, p.image FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = :order_id";
                $items_stmt = $db->prepare($items_query);
                $items_stmt->bindParam(':order_id', $order['id']);
                $items_stmt->execute();
                $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="order-card">
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-3 col-6">
                                <strong>Order #:</strong> <?php echo htmlspecialchars($order['order_number']); ?>
                            </div>
                            <div class="col-md-3 col-6">
                                <strong>Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </div>
                            <div class="col-md-3 col-6 mt-2 mt-md-0">
                                <strong>Total:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                            <div class="col-md-3 col-6 mt-2 mt-md-0 text-md-end">
                                <span class="badge bg-<?php 
                                    echo $order['status'] === 'delivered' ? 'success' : 
                                         ($order['status'] === 'pending' ? 'warning' : 
                                         ($order['status'] === 'processing' ? 'info' : 
                                         ($order['status'] === 'shipped' ? 'primary' : 'secondary'))); 
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <h6 class="mb-3">Order Items:</h6>
                        <?php foreach ($order_items as $item): ?>
                            <div class="d-flex align-items-center mb-2">
                                <img src="assets/images/products/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" 
                                     class="me-3">
                                <div class="flex-grow-1">
                                    <span class="small"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <small class="text-muted d-block">Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></small>
                                </div>
                                <span class="fw-bold">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">
                                    Payment: <?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?> | 
                                    Status: <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['payment_status']); ?></span>
                                </small>
                            </div>
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag" style="font-size: 4rem; color: #e5e7eb;"></i>
                <h4 class="mt-3 text-muted">No orders yet</h4>
                <p class="text-muted mb-4">You haven't placed any orders yet.</p>
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
