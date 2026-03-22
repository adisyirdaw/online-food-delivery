<?php
session_start();
include('../connection.php');


// Ensure user is logged in and order_id exists
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$cust_id = $_SESSION['user_id'];
$order_id = $_GET['id']; 

// 1. Fetch Order and Payment details using JOIN
// Note: Using $connect instead of $conn
$sql = "SELECT o.*, p.method, p.status as p_status 
        FROM `Order` o 
        JOIN Payment_Method p ON o.order_id = p.order_id 
        WHERE o.order_id = ? AND o.cust_id = ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $cust_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Error: Order not found or you do not have permission to view it.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed - Ella Kitchen</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .success-container { text-align: center; padding: 40px 20px; max-width: 500px; margin: auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .check-icon { font-size: 60px; color: #27ae60; margin-bottom: 10px; }
        .receipt-card { background: #ffffff; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: left; margin-top: 20px; border-top: 5px solid #e67e22; }
        .item-row { display: flex; justify-content: space-between; margin: 10px 0; font-size: 0.9em; color: #555; }
        .total-row { border-top: 2px solid #eee; margin-top: 15px; padding-top: 15px; display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1em; }
        .btn-group { margin-top: 30px; display: flex; gap: 10px; justify-content: center; }
        .btn { padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: bold; border: none; cursor: pointer; }
        .btn-home { background: #e67e22; color: white; }
        .btn-print { background: #f1f1f1; color: #333; }
        @media print { .btn-group, .check-icon { display: none; } .receipt-card { box-shadow: none; border: 1px solid #eee; } }
    </style>
</head>
<body>

<div class="success-container">
    <i class="fas fa-check-circle check-icon"></i>
    <h1>Order Placed!</h1>
    <p>Thank you for choosing Ella Kitchen. Your food is being prepared.</p>

    <div class="receipt-card">
        <h3 style="text-align: center; margin-bottom: 20px;">RECEIPT</h3>
        <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
        <p><strong>Payment:</strong> <?php echo strtoupper($order['method']); ?> 
           (<?php echo ucfirst($order['p_status']); ?>)</p>
        
        <div style="margin-top: 20px;">
            <p style="font-weight: bold; border-bottom: 1px solid #eee; padding-bottom: 5px;">Items:</p>
            <?php
            // 2. Fetch specific items for this order
            $items_sql = "SELECT oi.*, f.name FROM Order_item oi 
                          JOIN Foods f ON oi.food_id = f.food_id 
                          WHERE oi.order_id = ?";
            $stmt_items = mysqli_prepare($connect, $items_sql);
            mysqli_stmt_bind_param($stmt_items, "i", $order_id);
            mysqli_stmt_execute($stmt_items);
            $items_result = mysqli_stmt_get_result($stmt_items);

            while($item = mysqli_fetch_assoc($items_result)):
            ?>
            <div class="item-row">
                <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                <span><?php echo number_format($item['price'] * $item['quantity'], 2); ?> ETB</span>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="total-row">
            <span>Total Amount</span>
            <span><?php echo number_format($order['total_price'], 2); ?> Birr</span>
        </div>
    </div>

    <div class="btn-group">
        <a href="home.php" class="btn btn-home">Back to Menu</a>
        <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print"></i> Print</button>
    </div>
</div>

</body>
</html>