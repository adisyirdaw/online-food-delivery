<?php
//session_start();
include('../connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch User Name AND their average rating from the related table
// We use COALESCE to show '5.0' if they haven't been rated yet
$sql = "SELECT 
            c.Fname, 
            (SELECT COALESCE(AVG(rating), 5.0) 
             FROM Ratings_Reviews 
             WHERE cust_id = c.cust_id) AS avg_rating
        FROM Customer c 
        WHERE c.cust_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
// Fetch 5 most recent orders
// Fetch 5 most recent orders using your actual table columns
// Change 'order' to '`Order`'
$order_query = "SELECT order_id, total_price, status, order_date 
                FROM `Order` 
                WHERE cust_id = ? 
                ORDER BY order_date DESC 
                LIMIT 5";

$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$orders_result = $order_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .account-layout { display: flex; max-width: 1200px; margin: 40px auto; gap: 30px; font-family: sans-serif; }
        .sidebar { flex: 1; padding: 20px; border-right: 1px solid #eee; }
        .content-area { flex: 3; padding: 20px; }
        .rating-box { background-color: #fff9c4; color: #f57f17; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #fbc02d; }
        .quick-task a { display: block; padding: 10px 0; color: #c92c34; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="account-layout">
    <div class="sidebar">
        <h2>My Account</h2>
        <p>Hello, <strong><?php echo htmlspecialchars($user['Fname']); ?></strong></p>
        <div class="quick-task">
            <a href="#history-section">View my orders Food</a>
            <a href="edit_profile.php">Edit Profile</a>
            <a href="logout.php">Change my password</a>
        </div>
    </div>

    <div class="content-area">
    <h3 id="history-section">Recent Orders</h3>
    <?php if ($orders_result && $orders_result->num_rows > 0): ?>
        <table style="width:100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #eee; background-color: #f8f9fa;">
                    <th style="padding: 12px;">Order ID</th>
                    <th style="padding: 12px;">Date</th>
                    <th style="padding: 12px;">Total</th>
                    <th style="padding: 12px;">Status</th>
                    <td style="padding: 12px;">
    <a href="order_success.php?order_id=<?php echo $row['order_id']; ?>" 
       style="color: #3498db; text-decoration: none; font-size: 0.9em; margin-right: 10px;">
       <i class="fas fa-eye"></i> View
    </a>
    
    <a href="reorder_action.php?order_id=<?php echo $row['order_id']; ?>" 
       style="background: #27ae60; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.8em;"
       onclick="return confirm('Add these items to your current cart?')">
       <i class="fas fa-redo"></i> Reorder
    </a>
</td>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $orders_result->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">#<?php echo $row['order_id']; ?></td>
                        <td style="padding: 12px; font-size: 0.9em; color: #666;">
                            <?php echo date('M d, Y', strtotime($row['order_date'])); ?>
                        </td>
                        <td style="padding: 12px; font-weight: bold;">
                            <?php echo number_format($row['total_price'], 2); ?> Birr
                        </td>
                        <td style="padding: 12px;">
                            <?php 
                                // Professional Status Badges
                                $status = strtolower($row['status']);
                                $color = "#f39c12"; // Default orange
                                if($status == 'completed' || $status == 'delivered') $color = "#27ae60";
                                if($status == 'cancelled') $color = "#e74c3c";
                            ?>
                            <span style="background: <?php echo $color; ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <a href="order_success.php?order_id=<?php echo $row['order_id']; ?>" 
                               style="color: #3498db; text-decoration: none; font-size: 0.9em;">
                               <i class="fas fa-eye"></i> View Receipt
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding: 40px; text-align: center; border: 2px dashed #eee;">
            <p>You have no recent orders yet.</p>
            <a href="categories.php" style="background: #c92c34; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Start Ordering!</a>
        </div>
    <?php endif; ?>
</div>
</div>

</body>
</html>