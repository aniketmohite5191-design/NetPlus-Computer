<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";
$params = [];

if ($category_id) {
    $query .= " AND p.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if ($search) {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($min_price !== null && $min_price > 0) {
    $query .= " AND p.price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price !== null && $max_price > 0) {
    $query .= " AND p.price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Default ordering
$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
            background: #f9fafb;
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
            
            .filter-sidebar {
                display: none;
            }
            
            .mobile-filter-btn {
                display: block !important;
            }
        }
        
        .mobile-filter-btn {
            display: none;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Products</h2>
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Home
            </a>
        </div>
        
        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" action="products.php">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-md-3 filter-sidebar">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="products.php">
                            <?php if ($search): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Price Range</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control" 
                                               placeholder="Min" value="<?php echo $min_price; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control" 
                                               placeholder="Max" value="<?php echo $max_price; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-md-9">
                <?php if (count($products) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-lg-3 col-md-6 col-6">
                                <div class="card product-card">
                                    <img src="assets/images/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>" 
                                         class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="card-body">
                                        <h6 class="card-title text-truncate"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-primary">₹<?php echo number_format($product['price'], 2); ?></span>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                            <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search" style="font-size: 4rem; color: #e5e7eb;"></i>
                        <h5 class="mt-3 text-muted">No products found</h5>
                        <p class="text-muted">Try adjusting your search or filters</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 NETplus. All rights reserved.</p>
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
