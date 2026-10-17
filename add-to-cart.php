<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add products to cart']);
    exit();
}

// Get product ID and quantity
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Check if product exists and is active
$product_query = "SELECT * FROM products WHERE id = :id AND status = 'active'";
$product_stmt = $db->prepare($product_query);
$product_stmt->bindParam(':id', $product_id);
$product_stmt->execute();
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

// Check if product is in stock
if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Product out of stock']);
    exit();
}

// Check if product already in cart
$cart_query = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->bindParam(':user_id', $_SESSION['user_id']);
$cart_stmt->bindParam(':product_id', $product_id);
$cart_stmt->execute();
$existing_item = $cart_stmt->fetch(PDO::FETCH_ASSOC);

if ($existing_item) {
    // Update quantity
    $new_quantity = $existing_item['quantity'] + $quantity;
    if ($new_quantity > $product['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit();
    }
    
    $update_query = "UPDATE cart SET quantity = :quantity WHERE id = :cart_id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':quantity', $new_quantity);
    $update_stmt->bindParam(':cart_id', $existing_item['id']);
    $update_stmt->execute();
} else {
    // Add new item to cart
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity, price) VALUES (:user_id, :product_id, :quantity, :price)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $insert_stmt->bindParam(':product_id', $product_id);
    $insert_stmt->bindParam(':quantity', $quantity);
    $insert_stmt->bindParam(':price', $product['price']);
    $insert_stmt->execute();
}

// Get cart count
$count_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = :user_id";
$count_stmt = $db->prepare($count_query);
$count_stmt->bindParam(':user_id', $_SESSION['user_id']);
$count_stmt->execute();
$cart_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

echo json_encode(['success' => true, 'cart_count' => $cart_count, 'message' => 'Product added to cart successfully']);
?>
