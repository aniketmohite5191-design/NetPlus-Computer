<?php
// Script to add status column to products table
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if status column exists
    $result = $db->query("SHOW COLUMNS FROM products LIKE 'status'");
    if ($result->rowCount() == 0) {
        // Add status column
        $db->exec("ALTER TABLE products ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER price");
        echo "✓ Status column added to products table successfully!<br>";
    } else {
        echo "✓ Status column already exists in products table.<br>";
    }
    
    // Update all existing products to have 'active' status if null
    $db->exec("UPDATE products SET status = 'active' WHERE status IS NULL");
    echo "✓ All existing products set to active status.<br>";
    
    echo "<br><a href='admin/products.php'>Go to Products Page</a>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
