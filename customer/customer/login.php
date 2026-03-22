<?php
session_start();
include('../connection.php');
$login_error = "";

if (isset($_POST['submit_login'])) {
    $user_input = mysqli_real_escape_string($connect, $_POST['email']); 
    $password = $_POST['password'];

    // Updated table name to 'users' and is_active to '1'
    $sql = "SELECT * FROM users WHERE (email='$user_input' OR username='$user_input') AND is_active=1";
    $res = mysqli_query($connect, $sql);

    if (mysqli_num_rows($res) == 1) {
        $user = mysqli_fetch_assoc($res);
        
        // Verify password hash
        if (password_verify($password, $user['password_hash'])) {
            // Set sessions based on your table columns
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: home.php");
            exit();
        } else {
            $login_error = "Invalid Password!";
        }
    } else {
        $login_error = "User not found or account inactive!";
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <form method="POST" action="login.php" style="max-width: 400px; margin: 50px auto; padding: 25px; border-radius: 12px; background-color: #ffffff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; border: 1px solid #eee;">
    
    <h2 style="text-align: center; color: #333; margin-bottom: 20px; font-weight: 600;">Login</h2>
    
    <?php if($login_error): ?>
        <p style="color: #d63031; background-color: #fff2f2; padding: 10px; border-radius: 6px; text-align: center; font-size: 14px; border: 1px solid #fab1a0; margin-bottom: 15px;">
            <?php echo $login_error; ?>
        </p>
    <?php endif; ?>

    <div style="margin-bottom: 15px;">
        <input type="text" name="email" placeholder="Email or Username" required 
               style="width: 100%; padding: 12px 15px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 16px; outline: none;">
    </div>

    <div style="margin-bottom: 20px;">
        <input type="password" name="password" placeholder="Password" required 
               style="width: 100%; padding: 12px 15px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 16px; outline: none;">
    </div>

    <button type="submit" name="submit_login" class="login-btn" 
            style="width: 100%; background-color: #f39c12; color: white; padding: 14px 20px; margin: 8px 0; border: none; border-radius: 8px; cursor: pointer; font-size: 18px; font-weight: bold; transition: background-color 0.3s ease;">
        Login
    </button>

    <p style="text-align: center; margin-top: 15px; font-size: 14px; color: #666;">
        Don't have an account? <a href="signup.php" style="color: #f39c12; text-decoration: none; font-weight: bold;">Create one here</a>
    </p>
</form>
</body>
</html>