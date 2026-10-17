<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get product details
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = :id AND p.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $product_id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get related products
$related_query = "SELECT p.*, c.name as category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = :category_id AND p.id != :product_id 
                  AND p.status = 'active' LIMIT 4";
$related_stmt = $db->prepare($related_query);
$related_stmt->bindParam(':category_id', $product['category_id']);
$related_stmt->bindParam(':product_id', $product_id);
$related_stmt->execute();
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle add to cart
if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    requireLogin();
    
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    
    $cart_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)
                   ON DUPLICATE KEY UPDATE quantity = quantity + :quantity";
    $cart_stmt = $db->prepare($cart_query);
    $cart_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $cart_stmt->bindParam(':product_id', $product_id);
    $cart_stmt->bindParam(':quantity', $quantity);
    
    if ($cart_stmt->execute()) {
        setFlashMessage('Product added to cart successfully!', 'success');
        header('Location: cart.php');
        exit();
    }
}

// Parse specifications
$specifications = json_decode($product['specifications'], true) ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .product-image-large {
            max-height: 400px;
            object-fit: contain;
            background: #f9fafb;
            border-radius: 12px;
        }
        
        .spec-table th {
            background: #f8f9fa;
            width: 30%;
        }
        
        .quantity-input {
            width: 80px;
            text-align: center;
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
            
            .product-image-large {
                max-height: 250px;
            }
        }
        
        .related-product-card {
            transition: transform 0.3s ease;
            border: none;
            border-radius: 8px;
        }
        
        .related-product-card:hover {
            transform: translateY(-3px);
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
                <a href="cart.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-shopping-cart"></i>
                </a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn btn-primary">
                        <i class="fas fa-user"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Mobile Header -->
    <div class="d-md-none bg-white p-3 border-bottom sticky-top">
        <div class="d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-laptop text-primary"></i> NETplus
            </a>
            <a href="cart.php" class="btn btn-outline-primary">
                <i class="fas fa-shopping-cart"></i>
            </a>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6 mb-4">
                <img src="assets/images/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>" 
                     class="img-fluid product-image-large w-100" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <!-- Product Details -->
            <div class="col-md-6">
                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <h2 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h2>
                
                <div class="mb-3">
                    <h3 class="text-primary mb-0">₹<?php echo number_format($product['price'], 2); ?></h3>
                    <small class="text-muted">Inclusive of all taxes</small>
                </div>
                
                <div class="mb-3">
                    <span class="badge <?php echo $product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                    </span>
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <small class="text-muted">(<?php echo $product['stock_quantity']; ?> available)</small>
                    <?php endif; ?>
                </div>
                
                <p class="mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                    <form action="product-detail.php" method="GET">
                        <input type="hidden" name="id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row align-items-center mb-3">
                            <div class="col-auto">
                                <label class="form-label mb-0">Quantity:</label>
                            </div>
                            <div class="col-auto">
                                <input type="number" name="quantity" class="form-control quantity-input" 
                                       value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                            </button>
                            <a href="cart.php?action=buy_now&id=<?php echo $product_id; ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-bolt me-2"></i>Buy Now
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100" disabled>
                        <i class="fas fa-times-circle me-2"></i>Out of Stock
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Specifications -->
        <?php if (!empty($specifications)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Specifications</h4>
                <div class="table-responsive">
                    <table class="table table-bordered spec-table">
                        <?php foreach ($specifications as $key => $value): ?>
                        <tr>
                            <th><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $key))); ?></th>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Related Products</h4>
                <div class="row g-4">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-lg-3 col-md-4 col-6">
                            <div class="card related-product-card h-100 border border-dark">
                                <img src="assets/images/products/<?php echo $related['image'] ?: 'placeholder.jpg'; ?>" 
                                     class="card-img-top" style="height: 250px; object-fit: cover;" alt="<?php echo htmlspecialchars($related['name']); ?>">
                                <div class="card-body">
                                    <h6 class="card-title text-truncate"><?php echo htmlspecialchars($related['name']); ?></h6>
                                    <p class="text-primary fw-bold mb-2">₹<?php echo number_format($related['price'], 2); ?></p>
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary btn-sm w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
            <a href="products.php" class="nav-link active">
                <i class="fas fa-th-large fa-lg"></i>
                <span>Products</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="cart.php" class="nav-link">
                <i class="fas fa-shopping-cart fa-lg"></i>
                <span>Cart</span>
            </a>
        </div>
        <div class="nav-item">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user fa-lg"></i>
                    <span>Profile</span>
                </a>
            <?php else: ?>
                <a href="login.php" class="nav-link">
                    <i class="fas fa-sign-in-alt fa-lg"></i>
                    <span>Login</span>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
