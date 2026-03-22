<?php
session_start();
include('../connection.php');

// Force error reporting to see what is happening
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_signup'])) {

    // 1. Collect inputs
    $fname    = mysqli_real_escape_string($connect, $_POST['first_name']);
    $lname    = mysqli_real_escape_string($connect, $_POST['last_name']);
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $email    = mysqli_real_escape_string($connect, $_POST['email']);
    $address  = mysqli_real_escape_string($connect, $_POST['address']);
    $phone    = "+251" . ltrim($_POST['phone'], '0');
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // 2. Check Passwords
    if ($password !== $confirm) {
        die("Error: Passwords do not match. Go back and try again.");
    }

    // 3. Check if user exists (Table name changed to lowercase 'users')
    $check = mysqli_query($connect, "SELECT user_id FROM users WHERE email='$email' OR username='$username'");
    if (mysqli_num_rows($check) > 0) {
        die("Error: Username or Email already exists in the database.");
    }

    // 4. Insert into 'users' table
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sqlUser = "INSERT INTO users (username, email, password_hash, role, is_active)
                VALUES ('$username', '$email', '$hashed', 'customer', 1)";

    if (mysqli_query($connect, $sqlUser)) {
        $user_id = mysqli_insert_id($connect);

        // 5. Insert into 'Customer' table
        $sqlCustomer = "INSERT INTO Customer (cust_id, Fname, Lname, phone, address)
                        VALUES ('$user_id', '$fname', '$lname', '$phone', '$address')";

        if (mysqli_query($connect, $sqlCustomer)) {
            // 6. Set Sessions
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = 'customer';
            $_SESSION['username'] = $username;

            // SUCCESS: Redirect
            header("Location: home.php?signup=success");
            exit();
        } else {
            // If Customer table fails, show error
            die("Database Error (Customer Table): " . mysqli_error($connect));
        }
    } else {
        // If users table fails, show error
        die("Database Error (users Table): " . mysqli_error($connect));
    }
} else {
    die("Error: Form was not submitted correctly.");
}
?>