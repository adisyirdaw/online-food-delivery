<?php
session_start();
require_once '../connection.php';

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

$order_id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$deliveryId = (int)$_SESSION['delivery_user_id'];

if ($order_id === 0) {
    echo "Invalid order.";
    exit;
}

$stmt = mysqli_prepare(
    $connect,
    "SELECT latitude, longitude
     FROM `order`
     WHERE order_id = ? AND del_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $deliveryId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($res)) {
    $lat = $row['latitude'];
    $lng = $row['longitude'];

    if (!empty($lat) && !empty($lng)) {
        header("Location: https://www.google.com/maps?q={$lat},{$lng}");
        exit();
    }
}

echo "Location not available.";
exit;

