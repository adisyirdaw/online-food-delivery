<?php
session_start();
include "../connection.php";

/* 🔐 Security check: must be logged in as delivery */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'delivery' ||
    !isset($_SESSION['delivery_user_id'])
) {
    header("Location: ../login.php");
    exit();
}

$deliveryId = (int)$_SESSION['delivery_user_id'];

$sql = "
    SELECT 
        o.order_id,
        c.Fname,
        c.Lname,
        c.phone,
        o.delivery_address,
        o.latitude,
        o.longitude
    FROM `order` o
    JOIN Customer c ON o.cust_id = c.cust_id
    WHERE o.del_id = ?
    AND o.status = 'out_for_delivery'
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $deliveryId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Current Delivery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="delivery_person.css">
</head>
<body>

<header class="header">
    <span class="brand">Ella Kitchen Delivery</span>
</header>

<div class="layout">
<main class="content">

<h2>Current Delivery</h2>

<div class="card">
<table>
<thead>
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Map</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($result) == 0): ?>
<tr>
    <td colspan="6" class="empty">No current delivery</td>
</tr>
<?php endif; ?>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td data-label="Order ID"><?= $row['order_id'] ?></td>

    <td data-label="Customer">
        <?= htmlspecialchars($row['Fname'] . " " . $row['Lname']) ?>
    </td>

    <td data-label="Phone"><?= htmlspecialchars($row['phone']) ?></td>

    <td data-label="Address"><?= htmlspecialchars($row['delivery_address']) ?></td>

    <td data-label="Map">
        <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
            <a href="view_location.php?id=<?= $row['order_id'] ?>"
               target="_blank"
               class="action-btn btn-location">View Location</a>
        <?php else: ?>
            <span class="empty">Not shared</span>
        <?php endif; ?>
    </td>

    <td data-label="Action">
        <a href="mark_delivered.php?id=<?= $row['order_id'] ?>"
           class="action-btn btn-delivered"
           onclick="return confirm('Mark this order as delivered?')">
           Delivered
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<br>
<a href="assigned_order.php" class="action-btn btn-accept">
← Back to Assigned Orders
</a>

</main>
</div>

</body>
</html>

