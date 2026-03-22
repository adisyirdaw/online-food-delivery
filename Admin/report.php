<?php
require_once '../connection.php';
session_start();

   // DATE PERIOD LOGIC
$period = $_GET['period'] ?? 'this_month';

switch ($period) {
    case 'today':
        $dStart = date('Y-m-d');
        $dEnd   = $dStart;
        break;
    case 'yesterday':
        $dStart = date('Y-m-d', strtotime('-1 day'));
        $dEnd   = $dStart;
        break;
    case 'this_week':
        $dStart = date('Y-m-d', strtotime('monday this week'));
        $dEnd   = date('Y-m-d');
        break;
    case 'last_week':
        $dStart = date('Y-m-d', strtotime('monday last week'));
        $dEnd   = date('Y-m-d', strtotime('sunday last week'));
        break;
    case 'last_month':
        $dStart = date('Y-m-01', strtotime('-1 month'));
        $dEnd   = date('Y-m-t', strtotime('-1 month'));
        break;
    default:
        $dStart = date('Y-m-01');
        $dEnd   = date('Y-m-d');
}

    //MAIN STATS
$stats = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) orders,
            COALESCE(SUM(final_price),0) revenue
     FROM `Order`
     WHERE status='delivered'
     AND order_date BETWEEN '$dStart 00:00:00' AND '$dEnd 23:59:59'"
));

     // TOP ITEMS
$topItems = mysqli_query($connect,
    "SELECT f.name, f.image,
            c.name category,
            SUM(oi.quantity) sold,
            SUM(oi.quantity*oi.price) revenue
     FROM Order_item oi
     JOIN Foods f ON oi.food_id=f.food_id
     JOIN Categories c ON f.category_id=c.category_id
     JOIN `Order` o ON oi.order_id=o.order_id
     WHERE o.status='delivered'
     AND o.order_date BETWEEN '$dStart 00:00:00' AND '$dEnd 23:59:59'
     GROUP BY oi.food_id
     ORDER BY sold DESC
     LIMIT 5"
);

   // NEW CUSTOMERS (Users + Customer)
$newCustomers = mysqli_query($connect,
    "SELECT c.Fname, c.Lname, u.email, u.created_at
     FROM Customer c
     JOIN Users u ON c.cust_id = u.user_id
     WHERE u.role='customer'
     ORDER BY u.created_at DESC
     LIMIT 5"
);

   // LATEST ORDERS (Users + Customer)
$newOrders = mysqli_query($connect,
    "SELECT o.order_id,
            CONCAT(c.Fname,' ',c.Lname) customer,
            o.status,
            o.final_price,
            o.order_date
     FROM `Order` o
     JOIN Customer c ON o.cust_id=c.cust_id
     JOIN Users u ON c.cust_id=u.user_id
     WHERE u.role='customer'
     ORDER BY o.order_date DESC
     LIMIT 5"
);

    // CATEGORY PERFORMANCE
$categorySales = mysqli_query($connect,
    "SELECT c.name category,
            SUM(oi.quantity) qty,
            SUM(oi.quantity*oi.price) revenue
     FROM Order_item oi
     JOIN Foods f ON oi.food_id=f.food_id
     JOIN Categories c ON f.category_id=c.category_id
     JOIN `Order` o ON oi.order_id=o.order_id
     WHERE o.status='delivered'
     AND o.order_date BETWEEN '$dStart 00:00:00' AND '$dEnd 23:59:59'
     GROUP BY c.category_id
     ORDER BY revenue DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports</title>
<link rel="stylesheet" href="css/admin.css">
</head>

<body class="admin-dashboard">

<?php include 'sidebar.php'; ?>

<div class="admin-main">

<header class="admin-header">
    <h1>Business Reports</h1>
    <div class="profile-box">
        <div class="avatar">👤</div>
        <p class="username"><?= htmlspecialchars($_SESSION['adminUsername'] ?? 'Admin') ?></p>
    </div>
</header>

<div class="admin-content">

<!-- PERIOD SELECT -->
<div class="content-section">
<form method="get">
<label>Report Period</label>
<select name="period" onchange="this.form.submit()">
<?php foreach (['today','yesterday','this_week','last_week','this_month','last_month'] as $p): ?>
<option value="<?= $p ?>" <?= $period==$p?'selected':'' ?>>
<?= ucfirst(str_replace('_',' ',$p)) ?>
</option>
<?php endforeach; ?>
</select>
</form>
</div>

<!-- STATS -->
<div class="stats-grid">
<div class="stat-card"><h3>Orders</h3><p><?= $stats['orders'] ?></p></div>
<div class="stat-card"><h3>Revenue</h3><p><?= number_format($stats['revenue']) ?> Birr</p></div>
</div>

<!-- TOP ITEMS -->
<div class="content-section">
<h2><span>⭐</span> Top Performing Items</h2>
<table class="admin-table">
<thead>
<tr><th>#</th><th>Item</th><th>Category</th><th>Sold</th><th>Revenue</th></tr>
</thead>
<tbody>
<?php $i=1; while($t=mysqli_fetch_assoc($topItems)): ?>
<tr>
<td><?= $i++ ?></td>
<td>
<img src="../images/foods/<?= htmlspecialchars($t['image']) ?>" width="40">
<?= htmlspecialchars($t['name']) ?>
</td>
<td><?= htmlspecialchars($t['category']) ?></td>
<td><?= $t['sold'] ?></td>
<td><?= number_format($t['revenue']) ?> Birr</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- NEW CUSTOMERS -->
<div class="content-section">
<h2><span>👤➕</span> Newly Registered Customers</h2>
<table class="admin-table">
<tr><th>Name</th><th>Email</th><th>Joined</th></tr>
<?php while($c=mysqli_fetch_assoc($newCustomers)): ?>
<tr>
<td><?= htmlspecialchars($c['Fname'].' '.$c['Lname']) ?></td>
<td><?= htmlspecialchars($c['email']) ?></td>
<td><?= date('d M Y',strtotime($c['created_at'])) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- LATEST ORDERS -->
<div class="content-section">
<h2><span>⏰</span> Latest Orders</h2>
<table class="admin-table">
<tr><th>Order</th><th>Customer</th><th>Status</th><th>Amount</th><th>Date</th></tr>
<?php while($o=mysqli_fetch_assoc($newOrders)): ?>
<tr>
<td>#<?= $o['order_id'] ?></td>
<td><?= htmlspecialchars($o['customer']) ?></td>
<td><span class="badge"><?= $o['status'] ?></span></td>
<td><?= number_format($o['final_price']) ?> Birr</td>
<td><?= date('d M Y H:i',strtotime($o['order_date'])) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- CATEGORY PERFORMANCE -->
<div class="content-section">
<h2><span>📊</span> Category Performance</h2>
<table class="admin-table">
<tr><th>Category</th><th>Items Sold</th><th>Revenue</th></tr>
<?php while($c=mysqli_fetch_assoc($categorySales)): ?>
<tr>
<td><?= htmlspecialchars($c['category']) ?></td>
<td><?= $c['qty'] ?></td>
<td><?= number_format($c['revenue']) ?> Birr</td>
</tr>
<?php endwhile; ?>
</table>
</div>

</div> <!-- admin-content -->
</div> <!-- admin-main -->

</body>
</html>