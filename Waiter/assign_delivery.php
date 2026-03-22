<?php
header('Content-Type: application/json; charset=utf-8');

// Use shared DB connection
include_once __DIR__ . '/../connection.php';
if (!isset($connect) || !$connect) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}
$conn = $connect;

$order_id = $_POST['order_id'] ?? null;
$delivery_name = trim($_POST['delivery_name'] ?? '');
$delivery_id = $_POST['delivery_id'] ?? null;

// Basic validation: require order_id and either delivery_id or delivery_name
if ($order_id === null || (empty($delivery_name) && $delivery_id === null)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order_id or delivery identifier']);
    exit;
}

// ensure order_id is an integer
$order_id = filter_var($order_id, FILTER_VALIDATE_INT);
if ($order_id === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order_id']);
    exit;
}

// if delivery_id provided, validate it
if ($delivery_id !== null) {
    $delivery_id = filter_var($delivery_id, FILTER_VALIDATE_INT);
    if ($delivery_id === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid delivery_id']);
        exit;
    }
}

// Start transaction for consistency
$conn->begin_transaction();
try {
    // Lock delivery person row and verify availability (use bind_result to avoid get_result dependency)
    if ($delivery_id !== null) {
        $stmt = $conn->prepare("SELECT del_id, status FROM Delivery_person WHERE del_id = ? LIMIT 1 FOR UPDATE");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('i', $delivery_id);
    } else {
        $stmt = $conn->prepare("SELECT del_id, status FROM Delivery_person WHERE CONCAT(Fname, ' ', Lname) = ? LIMIT 1 FOR UPDATE");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('s', $delivery_name);
    }
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->bind_result($del_id, $del_status_db);
    if (!$stmt->fetch()) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Delivery person not found']);
        $stmt->close();
        exit;
    }
    // normalize status for comparison
    $del_status = strtolower(trim((string)$del_status_db));

    if ($del_status !== 'available') {
        $conn->rollback();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Delivery person is not available', 'status' => $del_status_db]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Lock order row (use bind_result)
    $stmt = $conn->prepare("SELECT del_id, status FROM `Order` WHERE order_id = ? LIMIT 1 FOR UPDATE");
    if (!$stmt) throw new Exception('Prepare failed');
    $stmt->bind_param('i', $order_id);
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->bind_result($order_del_id, $order_status_db);
    if (!$stmt->fetch()) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        $stmt->close();
        exit;
    }
    if (!empty($order_del_id)) {
        $conn->rollback();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Order already has a delivery person assigned']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Update order: assign del_id and set status (use DB enum value)
    $newOrderStatus = 'out_for_delivery';
    $stmt = $conn->prepare("UPDATE `Order` SET del_id = ?, status = ? WHERE order_id = ?");
    if (!$stmt) throw new Exception('Prepare failed');
    $stmt->bind_param('isi', $del_id, $newOrderStatus, $order_id);
    if (!$stmt->execute()) throw new Exception('Failed to update order');
    $stmt->close();

    // Update delivery person status
    $newDelStatus = 'busy';
    $stmt = $conn->prepare("UPDATE Delivery_person SET status = ? WHERE del_id = ?");
    if (!$stmt) throw new Exception('Prepare failed');
    $stmt->bind_param('si', $newDelStatus, $del_id);
    if (!$stmt->execute()) throw new Exception('Failed to update delivery person status');
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id, 'del_id' => $del_id, 'message' => 'Assigned successfully']);
    $conn->close();
    exit;

} 
catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
    $conn->close();
    exit;
}
