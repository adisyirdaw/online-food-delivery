<?php
require_once '../connection.php';
session_start();

$success = $error = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {

        /* ===== REGISTER STAFF ===== */
        case 'register':
            $fname    = mysqli_real_escape_string($connect, $_POST['fname'] ?? '');
            $lname    = mysqli_real_escape_string($connect, $_POST['lname'] ?? '');
            $email    = mysqli_real_escape_string($connect, $_POST['email'] ?? '');
            $phone    = mysqli_real_escape_string($connect, $_POST['phone'] ?? '');
            $address  = mysqli_real_escape_string($connect, $_POST['address'] ?? '');
            $username = mysqli_real_escape_string($connect, $_POST['username'] ?? '');
            $role     = mysqli_real_escape_string($connect, $_POST['role'] ?? '');
            $pass     = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            $formData = compact('fname','lname','email','phone','address','role');
            $errors = [];

            /* ---------- VALIDATION ---------- */
            if (empty($fname) || empty($lname) || empty($email) || empty($phone)
                || empty($address) || empty($username) || empty($pass)) {
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
            if (!in_array($role, ['manager','waiter'])) {
                $errors[] = "Invalid staff role selected!";
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
                    VALUES ('$username','$email','$hash','$role')
                ";
                if (mysqli_query($connect, $sqlUser)) {
                    $user_id = mysqli_insert_id($connect);

                    // 2. Insert into Staff (linked by user_id)
                    $sqlStaff = "
                        INSERT INTO Staff (staff_id, Fname, Lname, phone, address)
                        VALUES ($user_id, '$fname','$lname','$phone','$address')
                    ";
                    if (mysqli_query($connect, $sqlStaff)) {
                        $success = ucfirst($role) . " registered successfully!";
                        $formData = [];
                    } else {
                        $error = "Staff table error: " . mysqli_error($connect);
                    }
                } else {
                    $error = "Users table error: " . mysqli_error($connect);
                }
            } else {
                $error = implode('<br>', $errors);
            }
        break;

        /* ===== DELETE STAFF ===== */
        case 'delete':
            $id = (int)$_POST['staff_id'];
            // Delete from Users cascades to Staff
            mysqli_query($connect, "DELETE FROM Users WHERE user_id=$id");
            $success = "Staff deleted successfully.";
        break;
    }
}

/* ---------- FETCH STAFF BY ROLE ---------- */
$staffByRole = [];
$roles = ['manager','waiter'];

foreach ($roles as $r) {
    $staffByRole[$r] = mysqli_query($connect,
        "SELECT u.user_id AS staff_id,
                CONCAT(s.Fname,' ',s.Lname) AS name,
                u.email,
                s.phone,
                s.created_at
         FROM Users u
         JOIN Staff s ON u.user_id = s.staff_id
         WHERE u.role='$r'
         ORDER BY u.user_id DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Registration – Ella Kitchen Cafe</title>
<link rel="stylesheet" href="css/admin.css">
</head>

<body class="admin-dashboard">
<?php include 'sidebar.php'; ?>

<div class="admin-main">

<header class="admin-header">
<h1>Staff Registration</h1>
<div class="profile-box">
<div class="avatar">👤</div>
<p class="username"><?= htmlspecialchars($_SESSION['adminUsername'] ?? 'Admin') ?></p>
</div>
</header>

<div class="admin-content">

<?php if ($success): ?>
<div class="alert alert-success">✅ <?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error">⚠️ <?= $error ?></div>
<?php endif; ?>

<!-- ===== REGISTER STAFF FORM ===== -->
<div class="content-section">
<h2><span>👤➕</span> Register New Staff</h2>

<form class="form-container" method="POST">
<input type="hidden" name="action" value="register">

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
<input type="email" name="email" placeholder="staff@example.com" required>
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
<label>Role *</label>
<select name="role" required>
<option value="manager">Manager</option>
<option value="waiter">Waiter</option>
</select>
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
<textarea name="address" rows="3" placeholder="e.g., Hossaina, Ethiopia" required></textarea>
</div>

<div class="form-actions">
<button type="submit" class="btn btn-primary">
<span>👤➕</span> Register Staff
</button>
<button type="reset" class="btn btn-secondary">
<span>🧹</span> Clear
</button>
</div>
</form>
</div>

<!-- ===== STAFF LIST ===== -->
<div class="content-section">
<h2><span>👥</span> Registered Staff</h2>

<?php foreach ($roles as $r): ?>
<h3 style="margin-top:25px;border-bottom:2px solid #f6b11a;padding-bottom:5px">
<?= ucfirst($r) ?>s
</h3>

<table class="admin-table">
<thead>
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Action</th>
</tr>
</thead>
<tbody>

<?php
mysqli_data_seek($staffByRole[$r], 0);
if (mysqli_num_rows($staffByRole[$r]) > 0):
while ($s = mysqli_fetch_assoc($staffByRole[$r])):
?>
<tr>
<td>#<?= $s['staff_id'] ?></td>
<td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
<td><?= htmlspecialchars($s['email']) ?></td>
<td><?= htmlspecialchars($s['phone']) ?></td>
<td><?= date('Y-m-d', strtotime($s['created_at'])) ?></td>
<td>
<form method="POST" onsubmit="return confirm('Delete staff?')">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="staff_id" value="<?= $s['staff_id'] ?>">
<button class="btn btn-small btn-danger">🗑️</button>
</form>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="6" style="text-align:center;color:#666;">No <?= $r ?>s registered.</td></tr>
<?php endif; ?>

</tbody>
</table>
<?php endforeach; ?>
</div>

</div>
</div>

<script src="Javascript/admin.js"></script>

</body>
</html>