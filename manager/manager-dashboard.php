<?php
session_start();
if (!($_SESSION['manager_logged_in'] ?? false)) {
    header('Location: ../login.php');
    exit;
}
require_once '../connection.php';
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* =====  AJAX  ===== */
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    switch ($action) {
        case 'stats':
            $total_orders = $connect->query("SELECT COUNT(*) FROM `Order`")->fetch_row()[0];
            $revenue      = $connect->query("SELECT COALESCE(SUM(final_price),0) FROM `Order`")->fetch_row()[0];
            $customers    = $connect->query("SELECT COUNT(DISTINCT cust_id) FROM `Order`")->fetch_row()[0];
            $complaints   = $connect->query("SELECT COUNT(*) FROM Complaint WHERE status='open'")->fetch_row()[0];
            echo json_encode([
                'total_orders'       => (int)$total_orders,
                'revenue'            => (float)$revenue,
                'customers'          => (int)$customers,
                'pending_complaints' => (int)$complaints
            ]);
            break;

        case 'recent_orders':
            $res = $connect->query(
                "SELECT CONCAT('#ORD', LPAD(order_id,3,'0')) AS order_id,
                        cust_id,
                        final_price,
                        status
                 FROM   `Order`
                 ORDER BY order_date DESC
                 LIMIT 10"
            );
            $rows = [];
            while ($r = $res->fetch_assoc()) {
                $rows[] = [
                    'order_id'    => $r['order_id'],
                    'customer'    => 'Customer #'.$r['cust_id'],
                    'amount'      => '$'.number_format($r['final_price'],2),
                    'status'      => ucfirst($r['status']),
                    'status_class'=> match($r['status']){
                        'delivered'=>'delivered','pending'=>'pending',default=>'processing'
                    }
                ];
            }
            echo json_encode($rows);
            break;

        case 'manager_info':
            echo json_encode([
                'name'  => $_SESSION['manager_name'] ?? 'Manager',
                'email' => $_SESSION['manager_email'] ?? ''
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dashboard – Ella Kitchen</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/manager.css">
</head>
<body>
<div class="manager-container">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header"
         style="background:url('images/logo.png') center/contain no-repeat; opacity:0.2; height:150px;
                display:flex; align-items:center; justify-content:center">
      <h3 style="margin:0; color:#333; position:relative; z-index:1">Manager Panel</h3>
    </div>
    <ul class="sidebar-menu">
      <li class="active"><a href="manager-dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
      <li><a href="manager-complaints.php"><i class="fas fa-exclamation-circle"></i><span>Complaints</span></a></li>
      <li><a href="manager-sales.php"><i class="fas fa-chart-line"></i><span>Sales Report</span></a></li>
      
    </ul>
    <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
  </aside>

  <div class="main-content">
    <div class="top-bar" style="display:flex; align-items:center; justify-content:center">
      <button class="menu-toggle" id="menuToggle" style="position:absolute; left:1rem"><i class="fas fa-bars"></i></button>
      <h1>WELCOME ELLA KITCHEN CAFE MANAGER</h1>
    </div>
    <div class="top-bar" style="display:flex;align-items:center;justify-content:center">
  <button class="menu-toggle" id="menuToggle" style="position:absolute;left:1rem">
    <i class="fas fa-bars"></i>
  </button>
  <!-- ADD THIS LINE -->
  <a href="manager-profile.php" class="profile-link"
     style="margin-left:auto;margin-right:1rem;font-size:1.3rem;color:#333">
    <i class="fas fa-user-circle"></i>
  </a>
</div>
    <div class="content-area">
      <div style="display:flex; justify-content:center"><h2>View order</h2></div>
      <div class="stats-grid">
        <div class="stat-card"><i class="fas fa-shopping-bag"></i><h3 id="totalOrders">-</h3><p>Total Orders</p></div>
        <div class="stat-card"><i class="fas fa-dollar-sign"></i><h3 id="revenue">-</h3><p>Revenue</p></div>
        <div class="stat-card"><i class="fas fa-users"></i><h3 id="customers">-</h3><p>Customers</p></div>
        <div class="stat-card"><i class="fas fa-exclamation-triangle"></i><h3 id="pendingComplaints">-</h3><p>Open Complaints</p></div>
      </div>
      <div class="table-card">
        <div style="display:flex; justify-content:center"><h3>Recent Orders</h3></div>
        <table class="data-table" id="recentOrdersTable"><thead><tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead><tbody></tbody></table>
      </div>
    </div>
  </div>
</div>
<script src="js/manager.js"></script>
</body>
</html>