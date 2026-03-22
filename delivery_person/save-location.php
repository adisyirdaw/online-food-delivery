<?php
session_start();
require_once '../connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$order_id = (int)($data['order_id'] ?? 0);
$lat = isset($data['lat']) ? (float)$data['lat'] : null;
$lng = isset($data['lng']) ? (float)$data['lng'] : null;

if (!$order_id || $lat === null || $lng === null) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid data']);
    exit;
}

/* ensure ownership */
$check = mysqli_prepare(
    $connect,
    "SELECT order_id FROM `order` WHERE order_id = ? AND cust_id = ?"
);
mysqli_stmt_bind_param($check, "ii", $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($check);
$res = mysqli_stmt_get_result($check);

if (mysqli_num_rows($res) === 0) {
    echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
    exit;
}

/* update location */
$stmt = mysqli_prepare(
    $connect,
    "UPDATE `order`
     SET latitude = ?, longitude = ?
     WHERE order_id = ?"
);
mysqli_stmt_bind_param($stmt, "ddi", $lat, $lng, $order_id);

mysqli_stmt_execute($stmt);

echo json_encode(['ok' => true]);
exit;
?>
