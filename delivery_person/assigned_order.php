<?php
session_start();
require_once "../connection.php";

/* 🔧 Ensure DB variable name matches */
if (isset($connect) && !isset($conn)) {
    $conn = $connect;
}

/* 🔐 Must be logged in as delivery */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'delivery'
) {
    header("Location: ../login.php");
    exit();
}

/* 🔧 Auto-create delivery_user_id if missing */
if (!isset($_SESSION['user_id'])) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT del_id FROM delivery_person WHERE user_id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {
        $_SESSION['delivery_user_id'] = $row['del_id'];
    } else {
        header("Location: ../login.php");
        exit();
    }
}

$deliveryId = (int)$_SESSION['delivery_user_id'];

/* 📦 Fetch assigned orders */
$sql = "
    SELECT 
        o.order_id,
        o.status,
        c.Fname,
        c.Lname,
        o.delivery_address
    FROM order o
    JOIN Customer c ON o.cust_id = c.cust_id
    WHERE o.del_id = ?
    AND o.status = 'ready'
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
    <title>Assigned Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="delivery_person.css">
</head>
<body>

<header class="header">
    <span class="brand">Ella Kitchen Delivery</span>
</header>

<div class="layout">
<main class="content">

<h2>Assigned Orders</h2>

<div class="card">
<table>
<thead>
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Address</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($result) === 0): ?>
<tr>
    <td colspan="5" class="empty">No assigned orders</td>
</tr>
<?php endif; ?>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td data-label="Order ID"><?= $row['order_id'] ?></td>

    <td data-label="Customer">
        <?= htmlspecialchars($row['Fname'] . " " . $row['Lname']) ?>
    </td>

    <td data-label="Address">
        <?= htmlspecialchars($row['delivery_address']) ?>
    </td>

    <td data-label="Status"><?= $row['status'] ?></td>

    <td data-label="Action">
        <a href="accept_order.php?id=<?= $row['order_id'] ?>"
           class="action-btn btn-accept">Accept</a>

        <a href="reject_order.php?id=<?= $row['order_id'] ?>"
           class="action-btn btn-reject"
           onclick="return confirm('Reject this order?')">Reject</a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<br>
<a href="../logout.php" class="action-btn btn-reject">Logout</a>

</main>
</div>

</body>
</html>