<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
      <link rel="stylesheet" href="css/header.css">
</head>
<body>
    
</body>
</html>


<?php
session_start();
include('../connection.php');

/**
 * 1. FIX: Better safety and Authentication check
 * Instead of a silent redirect, we tell home.php to ask the user to login/signup.
 */
if (!isset($_POST['submit'])) {
    header("Location: home.php"); 
    exit();
}

// If the customer is not logged in, redirect with a message trigger
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?ask_auth=true"); 
    exit();
}

$cust_id = $_SESSION['user_id'];
$delivery_address = mysqli_real_escape_string($connect, $_POST['delivery_address']);
$payment_method = mysqli_real_escape_string($connect, $_POST['payment_method']);
$lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
$lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

// 2. Calculate Total Price
$total_price = 0;
if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $res = mysqli_query($connect, "SELECT price FROM Foods WHERE food_id=$id");
        $food = mysqli_fetch_assoc($res);
        $total_price += ($food['price'] * $qty);
    }
} else {
    header("Location: home.php?error=empty_cart"); // Cart is empty
    exit();
}

mysqli_begin_transaction($connect);

try {
    // 3. Payment Handling
    if ($payment_method != 'cash') {
        // Updated to use cust_id matching your users table structure
        $wallet_sql = "SELECT balance FROM User_Wallets WHERE cust_id = $cust_id AND provider = '$payment_method' FOR UPDATE";
        $wallet_res = mysqli_query($connect, $wallet_sql);
        $wallet = mysqli_fetch_assoc($wallet_res);

        if (!$wallet || $wallet['balance'] < $total_price) {
            throw new Exception("insufficient_balance");
        }

        $new_balance = $wallet['balance'] - $total_price;
        mysqli_query($connect, "UPDATE User_Wallets SET balance = $new_balance WHERE cust_id = $cust_id AND provider = '$payment_method'");
    }

    // 4. Update Location
    if ($lat && $lng) {
        $loc_sql = "UPDATE Customer SET location = POINT($lat, $lng) WHERE cust_id = $cust_id";
        mysqli_query($connect, $loc_sql);
    }

    // 5. Insert Order
    $order_sql = "INSERT INTO `Order` (cust_id, total_price, status, delivery_address) 
                  VALUES ($cust_id, $total_price, 'pending', '$delivery_address')";
    mysqli_query($connect, $order_sql);
    $order_id = mysqli_insert_id($connect);

    // 6. Insert Items
    foreach ($_SESSION['cart'] as $food_id => $qty) {
        $food_res = mysqli_query($connect, "SELECT price FROM Foods WHERE food_id=$food_id");
        $food_data = mysqli_fetch_assoc($food_res);
        $item_price = $food_data['price'];

        $item_sql = "INSERT INTO Order_item (order_id, food_id, quantity, price) 
                     VALUES ($order_id, $food_id, $qty, $item_price)";
        mysqli_query($connect, $item_sql);
    }

    // 7. Insert Payment
    $p_status = ($payment_method == 'cash') ? 'pending' : 'completed';
    $pay_sql = "INSERT INTO Payment_Method (order_id, method, status, amount_paid) 
                VALUES ($order_id, '$payment_method', '$p_status', $total_price)";
    mysqli_query($connect, $pay_sql);

    mysqli_commit($connect);

    // 8. Finalize
    unset($_SESSION['cart']);
    header("Location: order_success.php?id=" . $order_id);
    exit();

} catch (Exception $e) {
    mysqli_rollback($connect);
    header("Location: home.php?error=" . $e->getMessage());
    exit();
}
?>
