<?php
session_start();
// Use shared DB connection
include_once __DIR__ . '/../connection.php';
if (!isset($connect) || !$connect) {
    die('DB connection not available');
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: Dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Enter username and password';
    } else {
        $stmt = $connect->prepare('SELECT staff_id, username, password_hash, role FROM Staff WHERE username = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->bind_result($staff_id, $db_user, $db_pass, $role);
            if ($stmt->fetch()) {
                $stmt->close();
                $ok = false;
                // Support hashed passwords and plain-text (fallback)
                if (password_verify($password, $db_pass)) {
                    $ok = true;
                } elseif ($password === $db_pass) {
                    $ok = true;
                }

                if ($ok) {
                    // Successful login
                    $_SESSION['user_id'] = $staff_id;
                    $_SESSION['username'] = $db_user;
                    $_SESSION['role'] = $role;
                    header('Location: Dashboard.php');
                    exit;
                }
            } else {
                $stmt->close();
            }
        }
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Waiters Panel</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        :root{--brand:#8b5a2b;--brand-dark:#6f441e;--bg:#f7f3ef;--card:#ffffff;--muted:#666}
        body{font-family:Arial, Helvetica, sans-serif;background:#f6b11a;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .login-box{max-width:380px;margin:40px auto;padding:28px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.08);background:var(--card);border:1px solid rgba(139,90,43,0.12);text-align:center}
        .login-logo{width:110px;margin:0 auto 10px;display:block}
        .login-box h2{color:var(--brand);margin-bottom:8px}
        .login-box .error{color:#b00020;text-align:center;margin-bottom:12px}
        .login-box label{display:block;text-align:left;margin-top:8px;color:var(--muted);font-size:0.9rem}
        .login-box input{width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;margin:6px 0;font-size:1rem}
        .login-box button{width:100%;padding:10px;margin-top:12px;background:linear-gradient(180deg,var(--brand),var(--brand-dark));color:#fff;border:0;border-radius:4px;font-weight:600;cursor:pointer}
        .login-box .small{font-size:0.9rem;color:var(--muted);margin-top:12px}
    </style>
</head>
<body>
    <div class="login-box">
        <img src="../images/logo.png" alt="Ella Kitchen Cafe" class="login-logo">
        <h2>Waiters Panel Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label>Username</label>
            <input type="text" name="username" placeholder="Username" required>
            <label>Password</label>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
