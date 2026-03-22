<?php
include('../connection.php');
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit_login'])) {
    // 1. Sanitize Input
    $user_input = mysqli_real_escape_string($connect, $_POST['email']); 
    $password = $_POST['password'];

    // 2. Query the 'users' table
    $sql = "SELECT * FROM users WHERE (email='$user_input' OR username='$user_input') AND is_active=1 LIMIT 1";
    $res = mysqli_query($connect, $sql);

    if ($res && mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        
        // 3. Verify password against 'password_hash' column
        if (password_verify($password, $row['password_hash'])) {
            
            // 4. Set Sessions based on 'users' table structure
            $_SESSION['user_id'] = $row['user_id']; 
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];

            // Redirect to home
            header("Location: home.php?login=success");
            exit();
        } else {
            // Wrong password
            header("Location: login.php?error=wrong_credentials");
            exit();
        }
    } else {
        // User not found or inactive
        header("Location: login.php?error=user_not_found");
        exit();
    }
}
?>