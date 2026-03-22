<?php
require_once '../connection.php';
session_start();

/* ----------  1. HANDLE STATUS UPDATE ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status  = mysqli_real_escape_string($connect, $_POST['status']);
    $validStatuses = ['pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled'];

    if (in_array($status, $validStatuses)) {
        $stmt = $connect->prepare("UPDATE `Order` SET status=? WHERE order_id=?");
        $stmt->bind_param("si", $status, $orderId);
        $stmt->execute();
    }
    header("Location: order.php");
    exit;
}

/* ----------  2. FETCH ORDERS WITH CUSTOMER + USER ---------- */
$sql = "
 SELECT o.order_id,
        CONCAT(c.Fname,' ',c.Lname) AS customer,
        u.email,
        o.order_date,
        o.total_price,
        o.status
 FROM `Order` o
 JOIN Customer c ON o.cust_id = c.cust_id
 JOIN Users u ON c.cust_id = u.user_id
 WHERE u.role='customer'
 ORDER BY o.order_id DESC";

$ordersRes = mysqli_query($connect, $sql) or die(
    '<pre style="color:red">MySQL error: '.mysqli_error($connect).'</pre>'
);

/* ----------  3. STATUS MAP (for dropdown) ---------- */
$statusMap = [
    'pending'=>'Pending','confirmed'=>'Confirmed','preparing'=>'Preparing',
    'ready'=>'Ready','out_for_delivery'=>'Out','delivered'=>'Delivered','cancelled'=>'Cancelled'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders – Ella Kitchen Cafe</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-dashboard">
<?php include 'sidebar.php'; ?>

<div class="admin-main">
  <header class="admin-header">
    <h1>Orders (<?= mysqli_num_rows($ordersRes) ?>)</h1>
    <div class="profile-box">
        <div class="avatar">👤</div>
        <p class="username"><?= htmlspecialchars($_SESSION['adminUsername'] ?? 'Admin') ?></p>
    </div>
  </header>

  <div class="admin-content">
    <div class="content-section">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Email</th>
              <th>Date</th>
              <th>Total</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($ordersRes)): ?>
            <tr>
              <td>#<?= $row['order_id'] ?></td>
              <td><?= htmlspecialchars($row['customer']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= date('Y-m-d H:i', strtotime($row['order_date'])) ?></td>
              <td><?= number_format($row['total_price']) ?> Birr</td>
              <td>
                <form method="POST" action="" style="display:inline">
                  <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                  <select name="status" class="status-select" onchange="this.form.submit()">
                    <?php foreach ($statusMap as $k => $v): ?>
                      <option value="<?= $k ?>" <?= $k==$row['status']?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td>
                <a href="orderDetail.php?id=<?= $row['order_id'] ?>" class="btn btn-small btn-primary">
                  👁️ View
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="Javascript/admin.js"></script>
</body>
</html>