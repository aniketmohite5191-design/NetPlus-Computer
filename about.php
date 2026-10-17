<?php
session_start();
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .about-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
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

    <!-- About Hero -->
    <section class="about-hero">
        <div class="container">
            <div class="text-center mb-3">
                <h2 class="mb-0"><i class="fas fa-info-circle me-2"></i>About Us</h2>
            </div>
            <div class="text-center">
                <p class="lead mb-0">Your Trusted Partner for Computer Solutions</p>
            </div>
        </div>
    </section>

    <div class="container mt-5">
        <!-- About Content -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h3 class="mb-4">Who We Are</h3>
                <p class="lead text-muted">
                    NETplus Computers is a leading provider of high-quality computer products and accessories. 
                    Since our establishment, we have been committed to delivering cutting-edge technology solutions 
                    to meet the diverse needs of our customers.
                </p>
                <p class="text-muted">
                    We offer a comprehensive range of products including laptops, desktops, monitors, printers, 
                    and computer accessories from top brands. Our mission is to provide reliable, affordable, 
                    and innovative computing solutions backed by excellent customer service.
                </p>
            </div>
        </div>

        <!-- Features -->
        <h3 class="text-center mb-4">Why Choose Us</h3>
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h5>Quality Products</h5>
                    <p class="text-muted mb-0">We source only the best products from trusted manufacturers to ensure quality and reliability.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h5>Competitive Prices</h5>
                    <p class="text-muted mb-0">Get the best value for your money with our competitive pricing and regular discounts.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>Expert Support</h5>
                    <p class="text-muted mb-0">Our knowledgeable team is always ready to help you find the right solutions.</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-6">
                <div class="text-center">
                    <h2 class="text-primary">10+</h2>
                    <p class="text-muted">Years Experience</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-center">
                    <h2 class="text-primary">5000+</h2>
                    <p class="text-muted">Happy Customers</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-center">
                    <h2 class="text-primary">1000+</h2>
                    <p class="text-muted">Products</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-center">
                    <h2 class="text-primary">24/7</h2>
                    <p class="text-muted">Support</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-laptop"></i> NETplus Computers</h5>
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
                <p>&copy; 2024 NETplus Computers. All rights reserved.</p>
            </div>
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
