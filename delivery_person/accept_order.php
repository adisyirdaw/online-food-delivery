<?php
session_start();
include "../connection.php";

/*  Security check */
if (
    !isset($_SESSION['delivery_user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'delivery'
) {
    header("Location: ../login.php");
    exit();
}

/*  Validate order id */
if (!isset($_GET['id'])) {
    header("Location: assigned_order.php");
    exit();
}

$orderId    = (int)$_GET['id'];
$deliveryId = (int)$_SESSION['delivery_user_id'];

/* 1️⃣ Update order status */
$updateOrder = "
    UPDATE `order`
    SET status = 'out_for_delivery'
    WHERE order_id = ?
    AND del_id = ?
";
$stmt1 = mysqli_prepare($conn, $updateOrder);
mysqli_stmt_bind_param($stmt1, "ii", $orderId, $deliveryId);
mysqli_stmt_execute($stmt1);

/* 2️⃣ Update delivery person status */
$updateDelivery = "
    UPDATE delivery_person
    SET status = 'busy'
    WHERE del_id = ?
";
$stmt2 = mysqli_prepare($conn, $updateDelivery);
mysqli_stmt_bind_param($stmt2, "i", $deliveryId);
mysqli_stmt_execute($stmt2);

/* 🚀 Redirect */
header("Location: current_order.php");
exit();

