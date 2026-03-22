<?php
require_once 'connection.php';
session_start();

$msg = ''; // plain text only (will be passed to JS)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $user = mysqli_real_escape_string($connect, $_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Query unified Users table
    $res = mysqli_query($connect,
          "SELECT user_id, username, email, role, password_hash
           FROM Users
           WHERE username='$user'
           LIMIT 1");

    if ($res && mysqli_num_rows($res) === 1) {
        $row = mysqli_fetch_assoc($res);
        if (password_verify($pass, $row['password_hash'])) {
            // Set session variables
            $_SESSION['user_id']  = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];

            // Redirect based on role
            switch ($row['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'manager':
    $_SESSION['manager_logged_in'] = true;
    $_SESSION['manager_id']        = $row['user_id'];
    $_SESSION['manager_name']      = $row['username'];
    header('Location: manager/manager-dashboard.php');
    break;
                case 'waiter':
                    header('Location: waiter/waiter_dashboard.php');
                    break;
                case 'delivery':
                    header('Location: delivery_person/assigned_order.php');
                    break;
                case 'customer':
                    header('Location: customer/home.php');
                    break;
                default:
                    header('Location: Dashboard.php'); // fallback
            }
            exit;
        }
    }
    $msg = 'Invalid username or password'; // will be consumed by JS
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ella Hotel and Resort</title>

    <link rel="stylesheet" href="admin/css/admin.css">
</head>
<body>
    <div class="content-container">
        <div class="welcome-container">
            <h1 class="welcome-heading">Welcome to Ella Hotel and Resort</h1>
            <p class="welcome-subtitle">
                Access your management dashboard to monitor operations, update menus,
                track orders, and manage customer experiences.
            </p>
            <div class="welcome-divider"></div>
        </div>

        <button class="glass-login-btn" id="openLoginBtn">
            <span class="emoji">🔑</span>
            Access Dashboard
        </button>
    </div>

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <div class="login-box">
            <button class="close-modal" id="closeLoginBtn" aria-label="Close login modal">×</button>
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Login to Ella Hotel and Resort</p>
            </div>

            <form class="login-form" id="loginForm" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="login-btn" id="submitLoginBtn">
                    <span class="emoji">🔑</span>
                    Login to Dashboard
                </button>
            </form>

            <!-- Forgot Password link -->
            <p style="margin-top:10px; text-align:center;">
                <a href="#" id="openForgotBtn">Forgot Password?</a>
            </p>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="login-modal" id="forgotModal">
        <div class="login-box">
            <button class="close-modal" id="closeForgotBtn" aria-label="Close forgot modal">×</button>
            <div class="login-header">
                <h2>Reset Password</h2>
                <p>Enter your registered email to receive a reset link</p>
            </div>

            <form class="login-form" id="forgotForm" method="POST" action="forgot_password.php" autocomplete="off">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <button type="submit" class="login-btn">
                    <span class="emoji">📧</span>
                    Send Reset Link
                </button>
            </form>
        </div>
    </div>

    <!-- JS -->
    <script src="admin/javascript/admin.js"></script>
    <script>
        <?php if ($msg): ?>
            alert('<?= addslashes($msg) ?>');
        <?php endif; ?>
    </script>
</body>
</html>