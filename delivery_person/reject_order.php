<?php
session_start();
include "../connection.php";

/* 🔐 Security check: must be logged in as delivery */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'delivery' ||
    !isset($_SESSION['delivery_user_id'])
) {
    header("Location: ../login.php");
    exit();
}

/* 🛑 Validate order id */
if (!isset($_GET['id'])) {
    header("Location: assigned_order.php");
    exit();
}

$orderId    = (int)$_GET['id'];
$deliveryId = (int)$_SESSION['delivery_user_id'];

/* 1️⃣ Unassign the order */
$updateOrder = "
    UPDATE `order`
    SET del_id = NULL,
        status = 'pending'
    WHERE order_id = ?
    AND del_id = ?
";
$stmt1 = mysqli_prepare($conn, $updateOrder);
mysqli_stmt_bind_param($stmt1, "ii", $orderId, $deliveryId);
mysqli_stmt_execute($stmt1);

/* 2️⃣ Update delivery person status → AVAILABLE */
$updateDelivery = "
    UPDATE delivery_person
    SET status = 'available'
    WHERE del_id = ?
";
$stmt2 = mysqli_prepare($conn, $updateDelivery);
mysqli_stmt_bind_param($stmt2, "i", $deliveryId);
mysqli_stmt_execute($stmt2);

/* 🚀 Redirect */
header("Location: assigned_order.php");
exit();

