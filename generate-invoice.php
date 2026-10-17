<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get order ID from URL parameter
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    die('Order ID not provided');
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get order details
$order_query = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = :order_id";
$order_stmt = $db->prepare($order_query);
$order_stmt->bindParam(':order_id', $order_id);
$order_stmt->execute();
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found');
}

// Get order items
$items_query = "SELECT oi.*, p.name, p.image FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->bindParam(':order_id', $order_id);
$items_stmt->execute();
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal
$subtotal = 0;
foreach ($order_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax_rate = floatval(getSetting($db, 'tax_rate') ?? 18);
$tax_amount = calculateTax($subtotal, $tax_rate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order_id; ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 28px;
        }
        
        .invoice-details {
            margin-bottom: 20px;
        }
        
        .invoice-details h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .details-table td {
            vertical-align: top;
            width: 50%;
            padding: 10px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .items-table td:nth-child(2),
        .items-table td:nth-child(3),
        .items-table td:nth-child(4) {
            text-align: right;
        }
        
        .total-table {
            width: 30%;
            border-collapse: collapse;
            margin-left: auto;
        }
        
        .total-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        
        .total-table td:first-child {
            text-align: left;
        }
        
        .total-table td:last-child {
            text-align: right;
        }
        
        .total-table tr:last-child {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        
        .btn-print {
            background: #1e40af;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        
        .btn-print:hover {
            background: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="no-print">
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            <button class="btn-print" onclick="window.close()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        
        <div class="header">
            <h1>NETplus</h1>
            <p>Your Trusted Computer Store</p>
            <p>Phone: 8468810837 | Email: info@netplus.com</p>
        </div>

        <div class="invoice-details">
            <h2>INVOICE</h2>
            <table class="details-table">
                <tr>
                    <td>
                        <strong>Invoice Number:</strong> #<?php echo $order_id; ?><br>
                        <strong>Date:</strong> <?php echo date('d M Y', strtotime($order['created_at'])); ?><br>
                        <strong>Order Status:</strong> <?php echo ucfirst($order['status']); ?><br>
                        <strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?>
                    </td>
                    <td>
                        <strong>Billed To:</strong><br>
                        <?php echo htmlspecialchars($order['user_name']); ?><br>
                        <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                        Phone: <?php echo htmlspecialchars($order['user_phone']); ?><br>
                        Email: <?php echo htmlspecialchars($order['user_email']); ?>
                    </td>
                </tr>
            </table>
        </div>

        <h3>Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table class="total-table">
            <tr>
                <td>Subtotal:</td>
                <td>₹<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr>
                <td>Tax (<?php echo $tax_rate; ?>%):</td>
                <td>₹<?php echo number_format($tax_amount, 2); ?></td>
            </tr>
            <tr>
                <td>Shipping:</td>
                <td>₹<?php echo number_format($order['shipping_cost'] ?? 0, 2); ?></td>
            </tr>
            <tr>
                <td>Total:</td>
                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
            </tr>
        </table>

        <div class="footer">
            <p>Thank you for your order! This is a computer-generated invoice and does not require a signature.</p>
            <p>For any queries, please contact us at info@netplus.com or call 8468810837</p>
        </div>
    </div>
</body>
</html>
