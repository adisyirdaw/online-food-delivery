<?php
session_start();
include('../connection.php');

// 1. Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 2. Handle Form Submission
if (isset($_POST['update_profile'])) {
    $fname   = mysqli_real_escape_string($connect, $_POST['fname']);
    $lname   = mysqli_real_escape_string($connect, $_POST['lname']);
    $email   = mysqli_real_escape_string($connect, $_POST['email']);
    $phone   = mysqli_real_escape_string($connect, $_POST['phone']);
    $address = mysqli_real_escape_string($connect, $_POST['address']);

    // Update Query
    $update_sql = "UPDATE Customer SET 
                    Fname = ?, 
                    Lname = ?, 
                    email = ?, 
                    phone = ?, 
                    address = ? 
                   WHERE cust_id = ?";
    
    $stmt = $connect->prepare($update_sql);
    $stmt->bind_param("sssssi", $fname, $lname, $email, $phone, $address, $user_id);

    if ($stmt->execute()) {
        $_SESSION['full_name'] = $fname . " " . $lname; // Update session name
        $message = "<p style='color:green; font-weight:bold;'>Profile updated successfully!</p>";
    } else {
        $message = "<p style='color:red;'>Error updating profile. Email might already be in use.</p>";
    }
}

// 3. Fetch current data to pre-fill the form
$fetch_sql = "SELECT * FROM Customer WHERE cust_id = ?";
$fetch_stmt = $connect->prepare($fetch_sql);
$fetch_stmt->bind_param("i", $user_id);
$fetch_stmt->execute();
$user = $fetch_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Ella Kitchen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .edit-container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); font-family: 'Segoe UI', sans-serif; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .save-btn { background: #e67e22; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; }
        .save-btn:hover { background: #d35400; }
        .back-link { display: inline-block; margin-top: 15px; color: #7f8c8d; text-decoration: none; }
    </style>
</head>
<body style="background-color: #f4f7f6;">

<?php include('header.php'); ?>

<div class="edit-container">
    <h2><i class="fas fa-user-edit"></i> Edit Your Profile</h2>
    <?php echo $message; ?>

    <form method="POST" action="edit_profile.php">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="fname" value="<?php echo htmlspecialchars($user['Fname']); ?>" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lname" value="<?php echo htmlspecialchars($user['Lname']); ?>" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
        </div>

        <div class="form-group">
            <label>Delivery Address</label>
            <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
        </div>

        <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
    </form>

    <a href="my-account.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Account</a>
</div>

</body>
</html>