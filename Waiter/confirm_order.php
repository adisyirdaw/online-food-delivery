<?php
// Use shared DB connection
include_once __DIR__ . '/../connection.php';
if (!isset($connect) || !$connect) {
    die(json_encode(["success" => false]));
}
$conn = $connect;

$order_id = $_POST['order_id'] ?? 0;

/* 1. Find an available delivery person */
$dp = $conn->query("
    SELECT del_id 
    FROM Delivery_person 
    WHERE status = 'Available' 
    LIMIT 1
");

if ($dp->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "No available delivery person"]);
    exit;
}

$del = $dp->fetch_assoc();
$del_id = $del['del_id'];

/* 2. Assign delivery person to order */
$conn->query("
    UPDATE `Order`
    SET del_id = $del_id, status = 'ready'
    WHERE order_id = $order_id
");

/* 3. Set delivery person inactive */
$conn->query("
    UPDATE Delivery_person 
    SET status = 'Inactive'
    WHERE del_id = $del_id
");

echo json_encode(["success" => true]);
