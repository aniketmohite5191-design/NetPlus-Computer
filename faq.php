<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Get FAQs
$faq_query = "SELECT * FROM faq WHERE status = 'active' ORDER BY category, created_at";
$faq_stmt = $db->prepare($faq_query);
$faq_stmt->execute();
$faqs = $faq_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group FAQs by category
$grouped_faqs = [];
foreach ($faqs as $faq) {
    $category = $faq['category'] ?: 'General';
    if (!isset($grouped_faqs[$category])) {
        $grouped_faqs[$category] = [];
    }
    $grouped_faqs[$category][] = $faq;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .faq-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .accordion-button:not(.collapsed) {
            background-color: #eff6ff;
            color: var(--primary-color);
            box-shadow: none;
        }
        
        .accordion-button:focus {
            box-shadow: none;
            border-color: #e5e7eb;
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

    <!-- FAQ Hero -->
    <section class="faq-hero">
        <div class="container">
            <h2 class="mb-3"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h2>
            <p class="lead mb-0">Find answers to common questions about our products and services.</p>
        </div>
    </section>

    <div class="container mt-5 mb-5">
        <?php if (!empty($grouped_faqs)): ?>
            <?php foreach ($grouped_faqs as $category => $category_faqs): ?>
                <div class="mb-5">
                    <h4 class="mb-4 text-primary">
                        <i class="fas fa-folder me-2"></i><?php echo htmlspecialchars($category); ?>
                    </h4>
                    
                    <div class="accordion" id="faq-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                        <?php foreach ($category_faqs as $index => $faq): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" 
                                            type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse-<?php echo $faq['id']; ?>">
                                        <i class="fas fa-question-circle me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($faq['question']); ?>
                                    </button>
                                </h2>
                                <div id="collapse-<?php echo $faq['id']; ?>" 
                                     class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                     data-bs-parent="#faq-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                    <div class="accordion-body">
                                        <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-question-circle" style="font-size: 4rem; color: #e5e7eb;"></i>
                <h4 class="mt-3 text-muted">No FAQs available</h4>
                <p class="text-muted">Please check back later or contact us directly.</p>
                <a href="contact.php" class="btn btn-primary">
                    <i class="fas fa-envelope me-2"></i>Contact Us
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Still have questions -->
        <div class="text-center mt-5">
            <h5>Still have questions?</h5>
            <p class="text-muted mb-3">Can't find the answer you're looking for? Please contact our support team.</p>
            <a href="contact.php" class="btn btn-outline-primary">
                <i class="fas fa-headset me-2"></i>Contact Support
            </a>
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
