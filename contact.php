<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Get contact info from settings
$contact_email = getSetting($db, 'contact_email') ?? 'info@netplus.com';
$phone = getSetting($db, 'phone') ?? '+91-9876543210';
$address = getSetting($db, 'address') ?? '123 Tech Street, Computer City, India';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Save to support tickets
        if (isset($_SESSION['user_id'])) {
            $ticket_query = "INSERT INTO support_tickets (user_id, subject, message) VALUES (:user_id, :subject, :message)";
            $ticket_stmt = $db->prepare($ticket_query);
            $ticket_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $ticket_stmt->bindParam(':subject', $subject);
            $ticket_stmt->bindParam(':message', $message);
            $ticket_stmt->execute();
        }
        
        $success = 'Thank you for your message! We will get back to you soon.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - NETplus Computers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        .contact-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .contact-info-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 15px;
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

    <!-- Contact Hero -->
    <section class="contact-hero">
        <div class="container">
            <h2 class="mb-3"><i class="fas fa-headset me-2"></i>Contact Us</h2>
            <p class="lead mb-0">We're here to help! Get in touch with us.</p>
        </div>
    </section>

    <div class="container mt-5">
        <!-- Contact Info Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h5>Phone</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($phone); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h5>Email</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($contact_email); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h5>Address</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($address); ?></p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Send us a Message</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="contact.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" name="name" class="form-control" required 
                                           value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Email</label>
                                    <input type="email" name="email" class="form-control" required
                                           value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="5" required placeholder="How can we help you?"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
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
