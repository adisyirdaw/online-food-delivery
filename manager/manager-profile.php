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

/* ----------  AJAX UPDATE  ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (($_POST['csrf'] ?? '') !== $_SESSION['csrf']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }

    /* 1.  password change (optional) */
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit;
        }
        // verify old password
        $stmt = $connect->prepare('SELECT password_hash FROM Users WHERE user_id = ?');
        $stmt->bind_param('i', $_SESSION['manager_id']);
        $stmt->execute();
        $hash = $stmt->get_result()->fetch_assoc()['password_hash'] ?? '';
        if (!password_verify($_POST['old_password'], $hash)) {
            echo json_encode(['success' => false, 'message' => 'Old password is incorrect']);
            exit;
        }
        // store new hash
        $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $connect->prepare('UPDATE Users SET password_hash = ? WHERE user_id = ?');
        $stmt->bind_param('si', $newHash, $_SESSION['manager_id']);
        $stmt->execute();
    }

    /* 2.  profile fields */
    $fname   = $connect->real_escape_string(trim($_POST['fname']   ?? ''));
    $lname   = $connect->real_escape_string(trim($_POST['lname']   ?? ''));
    $email   = $connect->real_escape_string(trim($_POST['email']   ?? ''));
    $phone   = $connect->real_escape_string(trim($_POST['phone']   ?? ''));
    $address = $connect->real_escape_string(trim($_POST['address'] ?? ''));

    $stmt = $connect->prepare(
        'UPDATE Staff SET Fname = ?, Lname = ?, email = ?, phone = ?, address = ? WHERE staff_id = ?'
    );
    $stmt->bind_param('sssssi', $fname, $lname, $email, $phone, $address, $_SESSION['manager_id']);
    $ok = $stmt->execute();

    echo json_encode([
        'success' => $ok,
        'message' => $ok ? 'Profile updated successfully.' : 'Update failed: '.$connect->error
    ]);
    exit;
}

/* ----------  PAGE LOAD  ---------- */
$stmt = $connect->prepare('SELECT Fname, Lname, email, phone, address FROM Staff WHERE staff_id = ?');
$stmt->bind_param('i', $_SESSION['manager_id']);
$stmt->execute();
$mgr = $stmt->get_result()->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Profile – Ella Kitchen</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/manager.css">
  <style>
    .toast{position:fixed;top:1rem;right:1rem;background:#28a745;color:#fff;padding:.75rem 1.25rem;border-radius:.25rem;display:none;z-index:1050}
    .toggle-pw{cursor:pointer}
  </style>
</head>
<body>
  <div class="toast" id="toast"></div>

  <div class="manager-container">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header"
           style="background:url('images/logo.png') center/contain no-repeat;opacity:0.2;height:150px;
                  display:flex;align-items:center;justify-content:center">
        <h3 style="margin:0;color:#333;position:relative;z-index:1">Manager Panel</h3>
      </div>
      <ul class="sidebar-menu">
        <li><a href="manager-dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li><a href="manager-complaints.php"><i class="fas fa-exclamation-circle"></i><span>Complaints</span></a></li>
        <li><a href="manager-sales.php"><i class="fas fa-chart-line"></i><span>Sales Report</span></a></li>
      </ul>
      <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
    </aside>

    <div class="main-content">
      <div class="top-bar" style="display:flex;align-items:center;justify-content:center">
        <button class="menu-toggle" id="menuToggle" style="position:absolute;left:1rem"><i class="fas fa-bars"></i></button>
        <h1 >WELCOME ELLA KITCHEN CAFE MANAGER TO UPDATE YOUR PROFILE SETTINGS</h1>
        <!-- PROFILE ICON TOP-RIGHT -->
        <a href="manager-profile.php" class="profile-link"
           style="margin-left:auto;margin-right:1rem;font-size:1.3rem;color:#333">
          <i class="fas fa-user-circle"></i>
        </a>
      </div>

      <div class="content-area">
        <div style="display:flex;"><h2>Profile Settings</h2></div>
        <div class="form-card">
          <form id="profileForm">
            <div class="form-group"><label>First Name</label>
              <input type="text" name="fname" value="<?= htmlspecialchars($mgr['Fname']) ?>" required></div>
            <div class="form-group"><label>Last Name</label>
              <input type="text" name="lname" value="<?= htmlspecialchars($mgr['Lname']) ?>" required></div>
            <div class="form-group"><label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($mgr['email']) ?>" required></div>
            <div class="form-group"><label>Phone</label>
              <input type="tel" name="phone" value="<?= htmlspecialchars($mgr['phone']) ?>"></div>

            <fieldset style="border:1px solid #ccc;padding:1rem;margin-bottom:1rem">
              <legend>Change Password (optional)</legend>
              <div class="form-group"><label>Old Password</label>
                <input type="password" name="old_password" id="old_password" placeholder="Current password"></div>
              <div class="form-group"><label>New Password</label>
                <div style="position:relative">
                  <input type="password" name="new_password" id="new_password" placeholder="Leave blank to keep current">
                  <i class="fas fa-eye toggle-pw" data-target="new_password"></i>
                </div>
              </div>
              <div class="form-group"><label>Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-type new password"></div>
            </fieldset>

            <div class="form-group"><label>Address</label>
              <textarea name="address" rows="3"><?= htmlspecialchars($mgr['address']) ?></textarea></div>

            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
            <button type="submit" class="btn-primary">Update Profile</button>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
/* ajax submit + toast */
document.getElementById('profileForm').addEventListener('submit', async e => {
  e.preventDefault();
  const body = new FormData(e.target);
  const res  = await fetch('', {method:'POST', body});
  const json = await res.json();

  const toast = document.getElementById('toast');
  toast.textContent = json.message;
  toast.style.background = json.success ? '#28a745' : '#dc3545';
  toast.style.display = 'block';
  setTimeout(()=> toast.style.display='none', 3000);
});

/* toggle password eye */
document.querySelectorAll('.toggle-pw').forEach(icon => {
  icon.addEventListener('click', () => {
    const id = icon.dataset.target;
    const inp = document.getElementById(id);
    inp.type = inp.type==='password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash');
  });
});
</script>
</body>
</html>