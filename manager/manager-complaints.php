<?php
/* =====  AJAX  ===== */
if (isset($_GET['action'])) {          // AJAX call
    session_start();
    require_once '../connection.php';  // adjust path if necessary
    header('Content-Type: application/json');
    if (!($_SESSION['manager_logged_in'] ?? false)) {
        http_response_code(401);
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }

    $action = $_GET['action'];
    switch ($action) {
        case 'list':
            $res = $connect->query(
                "SELECT comp_id          AS id,
                        CONCAT('#ORD', LPAD(order_id,3,'0')) AS order_id,
                        type             AS title,
                        text             AS description,
                        status,
                        DATE_FORMAT(created_at,'%b %d, %Y') AS created_at
                 FROM   Complaint
                 ORDER  BY status='open' DESC, created_at DESC"
            );
            $rows = [];
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            echo json_encode($rows);
            break;

        case 'resolve':
            $id = (int)($_POST['complaint_id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }
            $stmt = $connect->prepare('UPDATE Complaint SET status="resolved", resolved_at=NOW() WHERE comp_id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Resolved']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;   // stop here for AJAX
}
/* =====  NORMAL PAGE  ===== */
session_start();
if (!($_SESSION['manager_logged_in'] ?? false)) {
    header('Location: ../login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Complaints – Ella Kitchen</title>
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
        <li><a href="manager-dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="active"><a href="manager-complaints.php"><i class="fas fa-exclamation-circle"></i><span>Complaints</span></a></li>
        <li><a href="manager-sales.php"><i class="fas fa-chart-line"></i><span>Sales Report</span></a></li>
       
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
      <div class="content-area" style="padding-left:2cm">
        <div class="page-header" style="display:flex; align-items:center; justify-content:center">
          <h2>Customer Complaints</h2>
          <select id="filterStatus" style="margin-left:auto; padding:0.3rem 0.6rem; font-size:0.9rem">
            <option value="all">All</option>
            <option value="open">Open</option>
            <option value="resolved">Resolved</option>
          </select>
        </div>
        <div class="complaints-list"></div>
      </div>
    </div>
  </div>
  <script src="js/manager.js"></script>
</body>
</html>