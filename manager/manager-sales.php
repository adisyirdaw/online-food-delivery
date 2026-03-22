<?php
/* ----------  1.  SESSION / AUTH  ---------- */
session_start();
if (!($_SESSION['manager_logged_in'] ?? false)) {
    header('Location: ../login.php');
    exit;
}

/* ----------  2.  ERROR DISPLAY (remove in production)  ---------- */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ----------  3.  DB CONNECTION  ---------- */
require_once '../connection.php';   // gives $connect (MySQLi)

/* ----------  4.  AJAX ENDPOINTS  ---------- */
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    $action = $_GET['action'];
    switch ($action) {

        /*  TODAY / WEEK / MONTH  (fallback: ignore status while testing)  */
        case 'summary':
            $today = $connect->query("
                SELECT COALESCE(SUM(final_price),0)
                FROM `Order`
                WHERE DATE(order_date) = CURDATE()
                -- AND status = 'delivered'   <-- uncomment after test
            ")->fetch_row()[0];

            $week = $connect->query("
                SELECT COALESCE(SUM(final_price),0)
                FROM `Order`
                WHERE YEARWEEK(order_date,1) = YEARWEEK(CURDATE(),1)
                -- AND status = 'delivered'
            ")->fetch_row()[0];

            $month = $connect->query("
                SELECT COALESCE(SUM(final_price),0)
                FROM `Order`
                WHERE YEAR(order_date)  = YEAR(CURDATE())
                  AND MONTH(order_date) = MONTH(CURDATE())
                -- AND status = 'delivered'
            ")->fetch_row()[0];

            echo json_encode([
                'today' => (float)($today ?: 0),
                'week'  => (float)($week  ?: 0),
                'month' => (float)($month ?: 0)
            ]);
            break;

        /*  BY CATEGORY  (fallback: ignore status while testing)  */
        case 'categories':
            $res = $connect->query("
                SELECT c.name                         AS category,
                       COUNT(DISTINCT o.order_id)     AS orders,
                       SUM(oi.quantity * f.price)     AS revenue
                FROM   `Order`     o
                JOIN   Order_item  oi ON oi.order_id = o.order_id
                JOIN   Foods       f  ON f.food_id   = oi.food_id
                JOIN   Categories  c  ON c.category_id = f.category_id
                -- WHERE  o.status = 'delivered'   <-- uncomment after test
                GROUP  BY c.category_id
                ORDER  BY revenue DESC
            ");

            $rows  = [];
            $total = 0;
            while ($r = $res->fetch_assoc()) {
                $rows[] = $r;
                $total += $r['revenue'];
            }
            array_walk($rows, fn(&$r,$_)=> [
                $r['percentage'] = $total > 0 ? round($r['revenue']/$total*100,1) : 0,
                $r['revenue']    = (float)$r['revenue'],
                $r['orders']     => (int)$r['orders']
            ]);
            echo json_encode($rows);
            break;

        /*  CUSTOM DATE RANGE  */
        case 'range':
            $start = $connect->real_escape_string($_GET['start'] ?? '');
            $end   = $connect->real_escape_string($_GET['end'] ?? '');
            if (!$start || !$end)  { echo json_encode([]); exit; }

            $sql = "
                SELECT DATE(order_date)            AS sale_date,
                       COUNT(*)                    AS orders,
                       SUM(final_price)            AS revenue
                FROM   `Order`
                WHERE  order_date BETWEEN '$start' AND '$end'
                -- AND status = 'delivered'
                GROUP  BY DATE(order_date)
                ORDER  BY sale_date DESC
            ";
            $res = $connect->query($sql);
            $out = [];
            while ($r = $res->fetch_assoc()) $out[] = $r;
            echo json_encode($out);
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
  <title>Sales Report – Ella Kitchen</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/manager.css">
  <style>
    .date-range{margin-bottom:1rem;display:flex;gap:.5rem;align-items:center}
    .date-range input{padding:.3rem .5rem;font-size:.9rem}
  </style>
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
        <li><a href="manager-dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
      
        <li><a href="manager-complaints.php"><i class="fas fa-exclamation-circle"></i><span>Complaints</span></a></li>
        <li class="active"><a href="manager-sales.php"><i class="fas fa-chart-line"></i><span>Sales Report</span></a></li>
      </ul>
      <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
      </div>
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
  <a href="manager-profile.php" class="profile-link"
     style="margin-left:auto;margin-right:1rem;font-size:1.3rem;color:#333">
    <i class="fas fa-user-circle"></i>
  </a>
</div>
      <div class="content-area">
        <div class="page-header" style="display:flex; align-items:center; justify-content:space-between">
          <h2>Sales Report</h2>
          <span class="date-range-label" style="font-size:0.9rem; color:#666"><?= date('M d, Y') ?></span>
        </div>

        <!-- quick date picker -->
        <div class="date-range">
          <label>From</label><input type="date" id="from" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
          <label>To</label><input type="date" id="to" value="<?= date('Y-m-d') ?>">
          <button class="btn-primary" id="reloadRange">Reload</button>
        </div>

        <div class="sales-summary" style="display:flex; gap:1rem; margin-bottom:1.5rem">
          <div class="summary-card" style="flex:1"><h3>Today’s Sales</h3><p class="amount">0.00 €</p></div>
          <div class="summary-card" style="flex:1"><h3>This Week</h3><p class="amount">0.00 €</p></div>
          <div class="summary-card" style="flex:1"><h3>This Month</h3><p class="amount">0.00 €</p></div>
        </div>

        <div class="chart-card">
          <h3>Sales by Category</h3>
          <table class="data-table">
            <thead><tr><th>Category</th><th>Orders</th><th>Revenue</th><th>Share %</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

<script>
/* ----------  FETCH SUMMARY  ---------- */
fetch('?action=summary')
  .then(r=>r.json())
  .then(d=>{
    document.querySelector('.summary-card:nth-child(1) .amount').textContent = d.today.toFixed(2)+' €';
    document.querySelector('.summary-card:nth-child(2) .amount').textContent = d.week.toFixed(2)+' €';
    document.querySelector('.summary-card:nth-child(3) .amount').textContent = d.month.toFixed(2)+' €';
  });

/* ----------  FETCH CATEGORIES  ---------- */
fetch('?action=categories')
  .then(r=>r.json())
  .then(list=>{
    const tb = document.querySelector('.data-table tbody');
    tb.innerHTML='';
    list.forEach(c=>{
      tb.insertAdjacentHTML('beforeend',
       `<tr><td>${c.category}</td><td>${c.orders}</td><td>${c.revenue.toFixed(2)} €</td><td>${c.percentage}%</td></tr>`);
    });
    if(!list.length) tb.innerHTML='<tr><td colspan="4">No data for selected period</td></tr>';
  });

/* ----------  DATE-RANGE RELOAD  ---------- */
document.getElementById('reloadRange').onclick=()=>{
  const start=document.getElementById('from').value,
        end=document.getElementById('to').value;
  fetch(`?action=range&start=${start}&end=${end}`)
    .then(r=>r.json())
    .then(arr=>{
      const tb=document.querySelector('.data-table tbody');
      tb.innerHTML='';
      arr.forEach(d=>{
        tb.insertAdjacentHTML('beforeend',
         `<tr><td>${d.sale_date}</td><td>${d.orders}</td><td>${parseFloat(d.revenue).toFixed(2)} €</td><td>-</td></tr>`);
      });
      if(!arr.length) tb.innerHTML='<tr><td colspan="4">No data for selected range</td></tr>';
    });
};
</script>
</body>
</html>