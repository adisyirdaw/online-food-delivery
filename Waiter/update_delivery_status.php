<?php
// Use shared DB connection
include_once __DIR__ . '/../connection.php';
if (!isset($connect) || !$connect) exit(json_encode(["success"=>false]));
$conn = $connect;

$del_id = $_POST['del_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$del_id || !in_array($status, ['available','busy','offline'])) {
    exit(json_encode(["success"=>false]));
}

$stmt = $conn->prepare(
    "UPDATE Delivery_person SET status=? WHERE del_id=?"
);
$stmt->bind_param("si", $status, $del_id);

echo json_encode(["success"=>$stmt->execute()]);
