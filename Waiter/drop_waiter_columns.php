<?php
header('Content-Type: application/json');
// Use shared DB connection
include_once __DIR__ . '/../connection.php';
if (!isset($connect) || !$connect) {
    echo json_encode(['success' => false, 'error' => 'Connection failed']);
    exit;
}
$conn = $connect;
$db = 'delivery';
$table = 'waiter_dashboard';

// Check for the columns we want to drop
$want = [ 'payment_status', 'notes' ];
$found = [];
$schema = $conn->real_escape_string($db);
$tname = $conn->real_escape_string($table);
$q = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".$schema."' AND TABLE_NAME='".$tname."' AND COLUMN_NAME IN ('".implode("','", $want)."')";
$res = $conn->query($q);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $found[] = $r['COLUMN_NAME'];
    }
}

if (empty($found)) {
    echo json_encode(['success' => true, 'message' => 'No columns found to drop.']);
    exit;
}

// Build ALTER TABLE statement to drop only existing columns
$cols = array_map(function($c) use ($conn) { return '`' . $conn->real_escape_string($c) . '`'; }, $found);
$alter = 'ALTER TABLE `'.$tname.'` DROP COLUMN ' . implode(', DROP COLUMN ', $cols);

if ($conn->query($alter)) {
    echo json_encode(['success' => true, 'dropped' => $found]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error, 'query' => $alter]);
}

$conn->close();

// WARNING: This script will permanently remove columns from the database. Run only if you are sure.

?>