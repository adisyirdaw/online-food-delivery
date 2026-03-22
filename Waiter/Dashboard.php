<?php
session_start();
// Use shared DB connection
include_once __DIR__ . '/../connection.php';
if (!isset($connect) || !$connect) {
    die('Database connection not available');
}
$conn = $connect;

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Query orders joined with items, foods and delivery person
$query = "
SELECT 
    o.order_id,
    MIN(f.name) AS food_name,
    SUM(oi.quantity) AS total_quantity,
    o.total_price,
    o.status,
    CONCAT(d.Fname, ' ', d.Lname) AS delivery_person
FROM `Order` o
LEFT JOIN Order_item oi ON oi.order_id = o.order_id
LEFT JOIN Foods f ON f.food_id = oi.food_id
LEFT JOIN Delivery_person d ON d.del_id = o.del_id
GROUP BY o.order_id
ORDER BY o.order_date DESC

";

$result = $conn->query($query);
if ($result === false) {
    die('Database query failed: ' . htmlspecialchars($conn->error));
}

$deliveryQuery = 'SELECT del_id, Fname, Lname, status FROM Delivery_person';
$deliveryResult = $conn->query($deliveryQuery);
if ($deliveryResult === false) {
    // non-fatal: just set to null so later code can handle empty delivery table
    $deliveryResult = null;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Waiters Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body class="admin-dashboard">
    <!-- Sidebar Navigation -->
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <div class="logo-admin">
                <img src="../images/logo.png" alt="">
                <h3>Ellaa Kitchen Cafe</h3>
                <p>Waiters Panel</p>
            </div>
        </div>
        <nav class="sidebar-menu">
            <ul>
                <li class="active">
                    <a href="dashboard.html">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a class="Logout" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Rest of your dashboard content remains the same -->
    <div class="admin-main">
        <header class="admin-header">
            <button id="sidebarToggle" class="hamburger-btn" aria-label="Toggle sidebar"><i
                    class="fas fa-bars"></i>
            </button>
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span>Welcome, Admin</span>
            </div>
        </header>
        <div class="admin-content">
           
            <!-- Recent Orders Table -->
            <div class="content-section">
                <h2>Recent Orders</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Food Name</th>
                                <th>Quantity</th>
                                <th>Delivery Person</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['order_id'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['food_name'] ?? ''); ?></td> 
                                    <td><?php echo htmlspecialchars($row['total_quantity'] ?? '0'); ?></td>


                                    <td>
                                        <div class="delivery-wrap">
                                                <input class="delivery-input"
                                                    name="delivery-<?php echo htmlspecialchars($row['order_id'] ?? ''); ?>"
                                                    type="text"
                                                    value="<?php echo htmlspecialchars($row['delivery_person'] ?? ''); ?>"
                                                    placeholder="Unassigned">
                                            </div>
                                    </td>

                                    <td>
                                        <div class="status-radio-group">
                                            <select class="status-select" name="status-<?php echo htmlspecialchars($row['order_id'] ?? ''); ?>">
                                                <option value="Pending" <?php if(($row['status'] ?? '')=='Pending') echo 'selected'; ?>>Pending</option>
                                                <option value="Preparing" <?php if(($row['status'] ?? '')=='Preparing') echo 'selected'; ?>>Preparing</option>
                                                <option value="Ready" <?php if(($row['status'] ?? '')=='Ready') echo 'selected'; ?>>Ready</option>
                                                <option value="Out for Delivery" <?php if(($row['status'] ?? '')=='Out for Delivery') echo 'selected'; ?>>Out for Delivery</option>
                                                <option value="Delivered" <?php if(($row['status'] ?? '')=='Delivered') echo 'selected'; ?>>Delivered</option>
                                                <option value="Cancelled" <?php if(($row['status'] ?? '')=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </td>

                                    <td>
                                        <button class="confirm-btn <?php echo (!empty($row['Confiremation'] ?? 0)) ? 'unconfirmed' : 'confirmed'; ?>"
                                                data-order="<?php echo htmlspecialchars($row['order_id'] ?? ''); ?>">
                                            <?php echo (!empty($row['Confiremation'] ?? 0)) ? 'unConfirmed' : 'confirmed'; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
            <div class="content-section">
                <h2>Delivery Info</h2>

                <div class="table-responsive">
                    <table class="admin-table" id="delivery-info-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($deliveryResult) && $deliveryResult && $deliveryResult->num_rows > 0) {
                                while ($drow = $deliveryResult->fetch_assoc()) {
                                    $did = $drow['del_id'] ?? '';
                                    $dname = trim(($drow['Fname'] ?? '') . ' ' . ($drow['Lname'] ?? ''));
                                    $dstatusRaw = $drow['status'] ?? '';
                                    $dstatusLower = strtolower($dstatusRaw);
                                    if ($dstatusLower === 'available') {
                                        $btnClass = 'btn-active';
                                        $label = 'Available';
                                    } elseif ($dstatusLower === 'busy') {
                                        $btnClass = 'btn-inactive';
                                        $label = 'On Delivery';
                                    } else {
                                        $btnClass = 'btn-inactive';
                                        $label = 'Off Duty';
                                    }
                                    $dataId = 'd' . htmlspecialchars($did);
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($did) . '</td>';
                                    echo '<td>' . htmlspecialchars($dname) . '</td>';
                                    echo '<td><button class="status-btn ' . $btnClass . '" data-id="' . $dataId . '" data-status="' . htmlspecialchars($dstatusLower) . '">' . $label . '</button></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3">No delivery records found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <script>
document.querySelectorAll('.confirm-btn').forEach(button => {
    button.addEventListener('click', function () {

        const orderId = this.dataset.order;
        const input = document.querySelector(`input[name="delivery-${orderId}"]`);
        const deliveryValue = input ? input.value.trim() : '';

        if (!deliveryValue) {
            alert('Please enter delivery person id or name');
            return;
        }

        // if numeric id entered, send as delivery_id; otherwise send as delivery_name
        const isNumericId = /^\d+$/.test(deliveryValue);
        const body = isNumericId
            ? `order_id=${orderId}&delivery_id=${encodeURIComponent(deliveryValue)}`
            : `order_id=${orderId}&delivery_name=${encodeURIComponent(deliveryValue)}`;

        fetch('assign_delivery.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                return;
            }

            /* 🔄 Update Delivery Info table UI */
            document.querySelectorAll('#delivery-info-table tbody tr').forEach(row => {
                if (isNumericId) {
                    const idCell = row.cells[0];
                    if (!idCell) return;
                    if (idCell.textContent.trim() === deliveryValue) {
                        const btn = row.querySelector('.status-btn');
                        if (!btn) return;
                        btn.classList.remove('btn-active');
                        btn.classList.add('btn-inactive');
                        btn.textContent = 'On Delivery';
                        btn.setAttribute('data-status', 'busy');
                    }
                } else {
                    const nameCell = row.cells[1];
                    if (!nameCell) return;
                    const nameTxt = nameCell.textContent.trim();
                    if (nameTxt.toLowerCase() === deliveryValue.toLowerCase()) {
                        const btn = row.querySelector('.status-btn');
                        if (!btn) return;
                        btn.classList.remove('btn-active');
                        btn.classList.add('btn-inactive');
                        btn.textContent = 'On Delivery';
                        btn.setAttribute('data-status', 'busy');
                    }
                }
            });

            /* ✔ Confirm button UI */
            this.textContent = 'Confirmed';
            this.classList.add('confirmed');
        })
        .catch(() => {
            alert('Server error');
        });
    });
});
</script>

</body>

</html>