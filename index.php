<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get featured products
$featured_products_query = "SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.status = 'active' AND p.stock_quantity > 0 
                           ORDER BY p.created_at DESC LIMIT 8";
$featured_products_stmt = $db->prepare($featured_products_query);
$featured_products_stmt->execute();
$featured_products = $featured_products_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NETplus - Your Trusted Computer Store</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0;
            position: relative;
            min-height: 600px;
        }
        
        .carousel-item {
            height: 600px;
            position: relative;
        }
        
        .carousel-item img {
            height: 100%;
            object-fit: cover;
        }
        
        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.3));
            display: flex;
            align-items: center;
        }
        
        .carousel-caption {
            text-align: center;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.8), rgba(30, 64, 175, 0.7));
            padding: 30px;
            border-radius: 10px;
            backdrop-filter: blur(5px);
            box-shadow: 0 8px 32px rgba(37, 99, 235, 0.3);
        }
        
        .carousel-caption h1 {
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .carousel-caption p {
            font-size: 1.25rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff20" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,106.7C1248,96,1344,96,1392,96L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
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
            background: var(--bg-light);
        }
        
        .category-card {
            text-align: center;
            padding: 30px;
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .category-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .category-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
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
            color: var(--text-light);
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
            .hero-section {
                padding: 60px 0;
            }
            
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
                <i class="fas fa-laptop text-primary"></i> NETplus
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback.php">Feedback</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <a href="cart.php" class="btn btn-outline-primary me-2 position-relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo getCartCount($db); ?>
                        </span>
                    </a>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                        <a href="register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Carousel -->
    <section class="hero-section">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="4"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="5"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="6"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="d-block w-100" alt="Laptop">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="d-block w-100" alt="Desktop Computer">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="d-block w-100" alt="Computer Setup">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1603302576837-37561b2e2302?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="d-block w-100" alt="Gaming Setup">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1517336714731-489689fd1ca8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="d-block w-100" alt="MacBook">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="d-block w-100" alt="HP Laptop">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1572569511254-d8f925fe2cbb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="d-block w-100" alt="Custom Computer">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- Brands Section -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="text-center mb-4">
                <h5 class="text-muted">Trusted Brands</h5>
            </div>
            <div class="row align-items-center justify-content-center">
                <div class="col-md-3 col-6 text-center mb-3">
                    <div class="brand-logo hp-logo">HP</div>
                </div>
                <div class="col-md-3 col-6 text-center mb-3">
                    <div class="brand-logo dell-logo">DELL</div>
                </div>
                <div class="col-md-3 col-6 text-center mb-3">
                    <div class="brand-logo lenovo-logo">LENOVO</div>
                </div>
                <div class="col-md-3 col-6 text-center mb-3">
                    <div class="brand-logo apple-logo">Apple</div>
                </div>
            </div>
            
            <style>
                .brand-logo {
                    font-size: 24px;
                    font-weight: bold;
                    padding: 10px 20px;
                    border-radius: 5px;
                    display: inline-block;
                    opacity: 0.8;
                    transition: all 0.3s ease;
                }
                .brand-logo:hover {
                    opacity: 1;
                    transform: scale(1.05);
                }
                .hp-logo {
                    background: #0096D6;
                    color: white;
                }
                .dell-logo {
                    background: #007DB8;
                    color: white;
                }
                .lenovo-logo {
                    background: #E60012;
                    color: white;
                }
                .apple-logo {
                    background: #000000;
                    color: white;
                }
            </style>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="featured" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Featured Products</h2>
            <div class="row g-4">
                <?php if(count($featured_products) > 0): ?>
                    <?php foreach($featured_products as $product): ?>
                        <div class="col-lg-3 col-md-4 col-6">
                            <div class="card product-card">
                                <img src="assets/images/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>" 
                                     class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                    <p class="text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <button class="btn btn-sm btn-outline-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No featured products available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <i class="fas fa-truck text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Fast Delivery</h5>
                    <p class="text-muted">Quick and reliable delivery across India</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-shield-alt text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Secure Payment</h5>
                    <p class="text-muted">Multiple payment options with 100% security</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-headset text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">24/7 Support</h5>
                    <p class="text-muted">Dedicated customer support team</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-laptop"></i> NETplus</h5>
                    <p class="small">Your trusted partner for all computing needs.</p>
                </div>
                <div class="col-md-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li><a href="about.php" class="text-white-50">About Us</a></li>
                        <li><a href="contact.php" class="text-white-50">Contact</a></li>
                        <li><a href="faq.php" class="text-white-50">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Contact Info</h6>
                    <p class="small">
                        <i class="fas fa-phone"></i> 8468810837<br>
                        <i class="fas fa-envelope"></i> info@netplus.com
                    </p>
                </div>
            </div>
            <hr class="bg-white-50">
            <div class="text-center small">
                <p>&copy; 2024 NETplus. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <div class="nav-item">
            <a href="index.php" class="nav-link active">
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
            <a href="cart.php" class="nav-link position-relative">
                <i class="fas fa-shopping-cart fa-lg"></i>
                <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo getCartCount($db); ?>
                </span>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Add to cart function
        function addToCart(productId) {
            fetch('add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                    // Show success message
                    alert('Product added to cart successfully!');
                } else {
                    alert(data.message || 'Failed to add product to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding product to cart');
            });
        }
    </script>
</body>
</html>
