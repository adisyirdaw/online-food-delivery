<?php
require_once '../connection.php';
session_start();

/* ----------  REAL LOGOUT  ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION = [];                       // wipe session variables
    session_destroy();                    // destroy the session
    header('Location: login.php');        // redirect to login
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Logout – Ella Kitchen Cafe</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .logout-confirm-box {
            max-width: 420px;
            margin: 10% auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
            padding: 35px 30px;
            text-align: center;
        }
        .logout-confirm-box h2 {
            margin-top: 0;
        }
        .logout-confirm-box .btn {
            margin: 6px;
        }
    </style>
</head>
<body class="admin-dashboard">
    <div class="logout-confirm-box">
        <div style="font-size: 50px; color: #e74c3c; margin-bottom: 20px;">
            🚪
        </div>
        <h2>Are you sure you want to leave?</h2>

        <!-- Logout button submits POST form -->
        <form method="POST" style="display:inline">
            <button type="submit" class="btn btn-danger">
                🚪 Logout
            </button>
        </form>

        <!-- Cancel is just a link back -->
        <a href="Dashboard.php" class="btn btn-secondary">
            ❌ Cancel
        </a>
    </div>
</body>
</html>