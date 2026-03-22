<?php
require_once '../connection.php';
session_start();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {

        /* REGISTER DELIVERY PERSON */
        case 'register_delivery':
            $fname    = mysqli_real_escape_string($connect, $_POST['fname'] ?? '');
            $lname    = mysqli_real_escape_string($connect, $_POST['lname'] ?? '');
            $email    = mysqli_real_escape_string($connect, $_POST['email'] ?? '');
            $phone    = mysqli_real_escape_string($connect, $_POST['phone'] ?? '');
            $address  = mysqli_real_escape_string($connect, $_POST['address'] ?? '');
            $username = mysqli_real_escape_string($connect, $_POST['username'] ?? '');
            $vehicle  = mysqli_real_escape_string($connect, $_POST['vehicle_type'] ?? '');
            $pass     = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            $errors = [];

            /* ---------- VALIDATION ---------- */
            if (empty($fname) || empty($lname) || empty($email) || empty($phone)
                || empty($address) || empty($username) || empty($vehicle) || empty($pass)) {
                $errors[] = "All required fields must be filled!";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format!";
            }
            if ($pass !== $confirm) {
                $errors[] = "Passwords do not match!";
            }
            if (strlen($pass) < 6) {
                $errors[] = "Password must be at least 6 characters!";
            }

            /* ---------- DUPLICATE CHECK ---------- */
            if (empty($errors)) {
                $check = mysqli_query($connect,
                    "SELECT user_id FROM Users
                     WHERE username='$username' OR email='$email'
                     LIMIT 1"
                );
                if (mysqli_num_rows($check) > 0) {
                    $errors[] = "Username or email already exists!";
                }
            }

            /* ---------- INSERT ---------- */
            if (empty($errors)) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);

                // 1. Insert into Users
                $sqlUser = "
                    INSERT INTO Users (username, email, password_hash, role)
                    VALUES ('$username','$email','$hash','delivery')
                ";
                if (mysqli_query($connect, $sqlUser)) {
                    $user_id = mysqli_insert_id($connect);

                    // 2. Insert into Delivery_person (linked by user_id)
                    $sqlDel = "
                        INSERT INTO Delivery_person (del_id, Fname, Lname, phone, address, vehicle_type)
                        VALUES ($user_id, '$fname','$lname','$phone','$address','$vehicle')
                    ";
                    if (mysqli_query($connect, $sqlDel)) {
                        $success = "Delivery person registered successfully!";
                    } else {
                        $error = "Delivery table error: " . mysqli_error($connect);
                    }
                } else {
                    $error = "Users table error: " . mysqli_error($connect);
                }
            } else {
                $error = implode('<br>', $errors);
            }
        break;

        /* DELETE DELIVERY PERSON */
        case 'delete_delivery':
            $id = (int)$_POST['del_id'];
            // Delete from Users cascades to Delivery_person
            mysqli_query($connect, "DELETE FROM Users WHERE user_id=$id");
            $success = "Delivery person deleted successfully.";
        break;
    }
}

/* ---------- FETCH DELIVERY PERSONS ---------- */
$deliveries = mysqli_query($connect,
    "SELECT u.user_id AS del_id,
            CONCAT(d.Fname,' ',d.Lname) AS name,
            u.email,
            d.phone,
            d.vehicle_type,
            d.created_at
     FROM Users u
     JOIN Delivery_person d ON u.user_id = d.del_id
     WHERE u.role='delivery'
     ORDER BY u.user_id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Registration – Ella Kitchen Cafe</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body class="admin-dashboard">
<?php include 'sidebar.php'; ?>

<div class="admin-main">

<header class="admin-header">
    <h1>Delivery Person Management</h1>
    <div class="profile-box">
        <div class="avatar">👤</div>
        <p class="username"><?= htmlspecialchars($_SESSION['adminUsername'] ?? 'Admin') ?></p>
    </div>
</header>

<div class="admin-content">

<!-- ALERTS -->
<?php if ($success): ?>
<div class="alert alert-success">✅ <?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error">⚠️ <?= $error ?></div>
<?php endif; ?>

<!-- DELIVERY PERSON FORM -->
<div class="content-section">
<h2><span>🏍️</span> Register Delivery Person</h2>

<form class="form-container" method="POST">
<input type="hidden" name="action" value="register_delivery">

<div class="form-row">
  <div class="form-group">
    <label>First Name *</label>
    <input type="text" name="fname" placeholder="Enter first name" required>
  </div>
  <div class="form-group">
    <label>Last Name *</label>
    <input type="text" name="lname" placeholder="Enter last name" required>
  </div>
</div>

<div class="form-row">
  <div class="form-group">
    <label>Email *</label>
    <input type="email" name="email" placeholder="delivery@example.com" required>
  </div>
  <div class="form-group">
    <label>Phone *</label>
    <input type="tel" name="phone" placeholder="+251 (9) 123-4567" required>
  </div>
</div>

<div class="form-row">
  <div class="form-group">
    <label>Username *</label>
    <input type="text" name="username" placeholder="Unique username" required>
  </div>
  <div class="form-group">
    <label>Vehicle type *</label>
    <input type="text" name="vehicle_type" placeholder="e.g., Motorcycle, Car" required>
  </div>
</div>

<div class="form-row">
  <div class="form-group">
    <label>Password *</label>
    <input type="password" name="password" placeholder="Min. 6 characters" required oninput="checkStrength(this)">
  </div>
  <div class="form-group">
    <label>Confirm Password *</label>
    <input type="password" name="confirm_password" placeholder="Retype password" required>
  </div>
</div>

<div class="form-group">
  <label>Address *</label>
  <textarea name="address" rows="3" placeholder="Full address" required></textarea>
</div>

<div class="form-actions">
  <button type="submit" class="btn btn-primary">
    <span>👤➕</span> Register Delivery
  </button>
  <button type="reset" class="btn btn-secondary">
    <span>🧹</span> Clear
  </button>
</div>
</form>
</div>

<!-- DELIVERY LIST -->
<div class="content-section">
<h2><span>🏍️</span> Registered Deliveries</h2>

<table class="admin-table">
<thead>
<tr>
  <th>ID</th>
  <th>Name</th>
  <th>Email</th>
  <th>Phone</th>
  <th>Vehicle</th>
  <th>Joined</th>
  <th>Action</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($deliveries) > 0): ?>
<?php while ($d = mysqli_fetch_assoc($deliveries)): ?>
<tr>
  <td>#<?= $d['del_id'] ?></td>
  <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
  <td><?= htmlspecialchars($d['email']) ?></td>
  <td><?= htmlspecialchars($d['phone']) ?></td>
  <td><?= htmlspecialchars($d['vehicle_type']) ?></td>
  <td><?= date('Y-m-d', strtotime($d['created_at'])) ?></td>
  <td>
        <form method="POST" onsubmit="return confirm('Delete delivery staff?')">
      <input type="hidden" name="action" value="delete_delivery">
      <input type="hidden" name="del_id" value="<?= $d['del_id'] ?>">
      <button class="btn btn-small btn-danger">
        🗑️
      </button>
    </form>
  </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
  <td colspan="7" style="text-align:center;color:#666;">
    No delivery staff registered yet.
  </td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

</div>
</div>

<script src="Javascript/admin.js"></script>
</body>
</html>