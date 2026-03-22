<?php 
session_start();
include('../connection.php');

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_signup'])) {
    // 1. Collect & Sanitize
    $fname    = mysqli_real_escape_string($connect, $_POST['first_name']);
    $lname    = mysqli_real_escape_string($connect, $_POST['last_name']);
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $email    = mysqli_real_escape_string($connect, $_POST['email']);
    $phone    = "+251" . ltrim($_POST['phone'], '0'); 
    $address  = mysqli_real_escape_string($connect, $_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 2. Validation
    if ($password !== $confirm_password) {
        $error = "❌ Passwords do not match!";
    } else {
        // 3. Check if exists in 'users' (lowercase as per your DB image)
        $check = mysqli_query($connect, "SELECT user_id FROM users WHERE email='$email' OR username='$username'");
        
        if (mysqli_num_rows($check) > 0) {
            $error = "⚠️ Email or Username already exists!";
        } else {
            // 4. Insert into users
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql_users = "INSERT INTO users (username, email, password_hash, role, is_active) 
                          VALUES ('$username', '$email', '$hashed', 'customer', 1)";
            
            if (mysqli_query($connect, $sql_users)) {
                $new_user_id = mysqli_insert_id($connect);

                // 5. Insert into Customer
                $sql_customer = "INSERT INTO Customer (cust_id, Fname, Lname, phone, address) 
                                 VALUES ('$new_user_id', '$fname', '$lname', '$phone', '$address')";
                
                if (mysqli_query($connect, $sql_customer)) {
                    $_SESSION['user_id']  = $new_user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role']     = 'customer';

                    header("Location: home.php?signup=success");
                    exit();
                } else {
                    $error = "❌ Failed to insert customer details: " . mysqli_error($connect);
                }
            } else {
                $error = "❌ Failed to insert user: " . mysqli_error($connect);
            }
        }
    }
}
?>

<form method="POST" action="" style="max-width: 500px; margin: 30px auto; padding: 30px; border-radius: 15px; background-color: #ffffff; box-shadow: 0 8px 20px rgba(0,0,0,0.1); font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; border: 1px solid #eee;">
    
    <h2 style="text-align: center; color: #333; margin-bottom: 25px; font-weight: 700;">Create Account</h2>

    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
        <div style="flex: 1;">
            <input type="text" name="first_name" placeholder="First Name" required 
                   style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 15px; outline: none; box-sizing: border-box;">
        </div>
        <div style="flex: 1;">
            <input type="text" name="last_name" placeholder="Last Name" required 
                   style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 15px; outline: none; box-sizing: border-box;">
        </div>
    </div>

    <div style="margin-bottom: 15px;">
        <input type="email" name="email" placeholder="Email" required 
               style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 15px; outline: none;">
    </div>

    <div style="margin-bottom: 15px;">
        <input type="text" name="username" placeholder="Username" required 
               style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 15px; outline: none;">
    </div>

    <div style="margin-bottom: 15px;">
        <input type="text" name="phone" placeholder="Phone (e.g. 0912...)" required 
               style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 15px; outline: none;">
    </div>

    <div style="margin-bottom: 15px;">
        <textarea name="address" placeholder="Address" required 
                  style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 15px; outline: none; height: 70px; resize: vertical;"></textarea>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 25px;">
        <div style="flex: 1;">
            <input type="password" name="password" placeholder="Password" required 
                   style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 15px; outline: none; box-sizing: border-box;">
        </div>
        <div style="flex: 1;">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required 
                   style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 15px; outline: none; box-sizing: border-box;">
        </div>
    </div>

    <button type="submit" name="submit_signup" 
            style="width: 100%; background-color: #f39c12; color: white; padding: 15px; border: none; border-radius: 8px; cursor: pointer; font-size: 18px; font-weight: bold; transition: background 0.3s ease;">
        Create Account
    </button>

    <p style="text-align: center; margin-top: 20px; font-size: 14px; color: #666;">
        Already have an account? <a href="login.php" style="color: #f39c12; text-decoration: none; font-weight: bold;">Log In</a>
    </p>
</form>
    </div>
</div>