<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Handle feedback form submission
$feedback_success = '';
$feedback_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);
    $message = sanitizeInput($_POST['message'] ?? '');
    
    // Handle file upload
    $file_path = '';
    if (isset($_FILES['feedback_file']) && $_FILES['feedback_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/feedback/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['feedback_file']['name']);
        $target_file = $upload_dir . $file_name;
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (in_array($_FILES['feedback_file']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['feedback_file']['tmp_name'], $target_file)) {
                $file_path = $file_name;
            }
        }
    }
    
    if (empty($name) || empty($email) || empty($message)) {
        $feedback_error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback_error = 'Please enter a valid email address.';
    } elseif ($rating < 1 || $rating > 5) {
        $feedback_error = 'Please select a valid rating.';
    } else {
        $insert_query = "INSERT INTO feedback (name, email, rating, message, file) VALUES (:name, :email, :rating, :message, :file)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':name', $name);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':rating', $rating);
        $insert_stmt->bindParam(':message', $message);
        $insert_stmt->bindParam(':file', $file_path);
        
        if ($insert_stmt->execute()) {
            $feedback_success = 'Thank you for your feedback! We appreciate it.';
        } else {
            $feedback_error = 'Failed to submit feedback. Please try again.';
        }
    }
}

// Get recent feedback for display
$recent_query = "SELECT * FROM feedback WHERE status != 'deleted' ORDER BY created_at DESC LIMIT 6";
$recent_stmt = $db->prepare($recent_query);
$recent_stmt->execute();
$recent_feedbacks = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_count FROM feedback";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .feedback-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .feedback-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .star-rating {
            color: #f59e0b;
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

    <!-- Feedback Hero -->
    <section class="feedback-hero">
        <div class="container">
            <div class="d-flex justify-content-center align-items-center mb-3">
                <h2 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Customer Feedback</h2>
            </div>
            <p class="lead mb-0">We value your opinion! Share your experience with us.</p>
        </div>
    </section>

    <!-- Statistics -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <h3 class="text-primary mb-1"><?php echo number_format($stats['total_count'] ?? 0); ?></h3>
                        <p class="text-muted mb-0">Total Reviews</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <h3 class="text-warning mb-1"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                        <p class="text-muted mb-0">Average Rating</p>
                        <div class="star-rating">
                            <?php echo str_repeat('<i class="fas fa-star"></i>', round($stats['avg_rating'] ?? 0)); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <h3 class="text-success mb-1">100%</h3>
                        <p class="text-muted mb-0">Response Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Feedback -->
    <section class="py-5">
        <div class="container">
            <h3 class="text-center mb-5">What Our Customers Say</h3>
            <div class="row g-4">
                <?php if (count($recent_feedbacks) > 0): ?>
                    <?php foreach ($recent_feedbacks as $feedback): ?>
                        <div class="col-md-4">
                            <div class="feedback-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($feedback['name']); ?></h6>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></small>
                                    </div>
                                    <div class="star-rating">
                                        <?php echo str_repeat('<i class="fas fa-star"></i>', $feedback['rating']); ?>
                                        <?php echo str_repeat('<i class="far fa-star"></i>', 5 - $feedback['rating']); ?>
                                    </div>
                                </div>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars(substr($feedback['message'], 0, 150)) . (strlen($feedback['message']) > 150 ? '...' : ''); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No reviews yet. Be the first to share your feedback!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Feedback Form -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white text-center">
                            <h4 class="mb-0"><i class="fas fa-pen me-2"></i>Write Your Review</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($feedback_success): ?>
                                <div class="alert alert-success"><?php echo $feedback_success; ?></div>
                            <?php endif; ?>
                            
                            <?php if ($feedback_error): ?>
                                <div class="alert alert-danger"><?php echo $feedback_error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="submit_feedback" value="1">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Your Name *</label>
                                        <input type="text" name="name" class="form-control" required 
                                               value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Your Email *</label>
                                        <input type="email" name="email" class="form-control" required
                                               value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Rating *</label>
                                    <div class="rating-stars">
                                        <?php for($i = 5; $i >= 1; $i--): ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="rating" id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="rating<?php echo $i; ?>">
                                                    <?php echo str_repeat('<i class="fas fa-star text-warning"></i>', $i); ?>
                                                </label>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Your Feedback *</label>
                                    <textarea name="message" class="form-control" rows="4" required placeholder="Tell us about your experience..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Attach Screenshot (Optional)</label>
                                    <input type="file" name="feedback_file" class="form-control" accept="image/*,.pdf">
                                    <small class="text-muted">Allowed: JPG, PNG, GIF, PDF (Max 5MB)</small>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
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
    
    <?php if ($feedback_success): ?>
    <script>
        alert('Feedback Form Submitted');
    </script>
    <?php endif; ?>
</body>
</html>
