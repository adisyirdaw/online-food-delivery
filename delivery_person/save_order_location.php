<?php
require_once '../connection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Customer not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
$lat     = isset($input['lat']) ? (float)$input['lat'] : null;
$lon     = isset($input['lon']) ? (float)$input['lon'] : null;

if ($orderId === 0 || $lat === null || $lon === null) {
    echo json_encode(['ok' => false, 'msg' => 'Missing data']);
    exit;
}

/* ✅ ensure this order belongs to this customer */
$check = mysqli_prepare(
    $connect,
    "SELECT order_id FROM `Order` WHERE order_id = ? AND cust_id = ?"
);
mysqli_stmt_bind_param($check, "ii", $orderId, $_SESSION['customer_id']);
mysqli_stmt_execute($check);
$res = mysqli_stmt_get_result($check);

if (mysqli_num_rows($res) === 0) {
    echo json_encode(['ok' => false, 'msg' => 'Unauthorized order']);
    exit;
}

/* ✅ store lat/lon in Order */
$stmt = mysqli_prepare(
    $connect,
    "UPDATE `Order`
     SET latitude = ?, longitude = ?
     WHERE order_id = ?"
);
mysqli_stmt_bind_param($stmt, "ddi", $lat, $lon, $orderId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'msg' => mysqli_error($connect)]);
}
exit;
