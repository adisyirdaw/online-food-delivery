<?php
session_start();
if (!($_SESSION['manager_logged_in'] ?? false)) {
    header('Location: ../login.php');
    exit;
}
require_once '../connection.php';

/* ----------  AJAX POST HANDLER  ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    header('Content-Type: application/json');

    // basic sanitising
    $name     = $connect->real_escape_string(trim($_POST['full_name']));
    $email    = $connect->real_escape_string(trim($_POST['email']));
    $phone    = $connect->real_escape_string(trim($_POST['phone']));
    $years    = (int)($_POST['years'] ?? 0);
    $message  = $connect->real_escape_string(trim($_POST['message'] ?? ''));

    if (!$name || !$email || !$phone || $years < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $sql = "INSERT INTO admin_applications (manager_id, full_name, email, phone, years_experience, message, applied_at)
            VALUES ({$_SESSION['manager_id']}, '$name', '$email', '$phone', $years, '$message', NOW())";
    if ($connect->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Your message successfully applied.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB error: '.$connect->error]);
    }
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Application – Ella Kitchen</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/manager.css">
  <style>
    .toast{position:fixed;top:1rem;right:1rem;background:#28a745;color:#fff;padding:.75rem 1.25rem;border-radius:.25rem;display:none;z-index:1050}
  </style>
</head>
<body>
  <div class="toast" id="toast"></div>

  <div class="manager-container">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header"
           style="background:url('images/logo.png') center/contain no-repeat; opacity:0.2; height:150px;
                  display:flex; align-items:center; justify-content:center">
        <h3 style="margin:0; color:#333; position:relative; z-index:1">Manager Panel</h3>
      </div>
      <ul class="sidebar-menu">
        <li><a href="manager-dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="active"><a href="manager_application.php"><i class="fas fa-file-alt"></i><span>Application</span></a></li>
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
        <div style="display:flex; justify-content:center"><h2>Apply for Admin</h2></div>

        <div class="form-card">
          <form id="applicationForm">
            <div class="form-group"><label>Full Name</label><input name="full_name" type="text" required></div>
            <div class="form-group"><label>Email</label><input name="email" type="email" required></div>
            <div class="form-group"><label>Phone Number</label><input name="phone" type="tel" required></div>
            <div class="form-group"><label>Years of Experience</label><input name="years" type="number" min="0" required></div>
            <div class="form-group"><label>Message</label><textarea name="message" rows="4"></textarea></div>
            <button class="btn-primary">Submit Application</button>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
document.getElementById('applicationForm').addEventListener('submit', async e => {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);

  const res = await fetch('', {method:'POST', body:data});
  const json = await res.json();

  const toast = document.getElementById('toast');
  toast.textContent = json.message;
  toast.style.display = 'block';
  if (json.success) form.reset();
  setTimeout(()=> toast.style.display='none', 3000);
});
</script>
</body>
</html>