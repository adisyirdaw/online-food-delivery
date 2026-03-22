<?php
require_once '../connection.php';
session_start();

$search = mysqli_real_escape_string($connect, $_GET['search'] ?? '');
$status = mysqli_real_escape_string($connect, $_GET['status'] ?? 'all');

$where = "WHERE u.role='customer'";
if ($search) {
    $where .= " AND (c.Fname LIKE '%$search%' OR c.Lname LIKE '%$search%' OR u.email LIKE '%$search%' OR c.phone LIKE '%$search%')";
}
if ($status !== 'all') {
    $where .= " AND u.is_active = " . ($status === 'active' ? 1 : 0);
}

$rowsPer = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $rowsPer;

$total = (int)(mysqli_fetch_assoc(
    mysqli_query($connect, "SELECT COUNT(*) c 
                            FROM Users u 
                            JOIN Customer c ON u.user_id=c.cust_id 
                            $where")
)['c'] ?? 0);

$pages = ceil($total / $rowsPer);

if (isset($_POST['action'], $_POST['cust_id'])) {
    $id = (int)$_POST['cust_id'];
    switch ($_POST['action']) {
        case 'toggle':
            mysqli_query($connect, "UPDATE Users SET is_active = NOT is_active WHERE user_id = $id");
            break;
        case 'delete':
            mysqli_query($connect, "DELETE FROM Users WHERE user_id = $id");
            break;
    }
    header('Location: customers.php');
    exit;
}

$customers = mysqli_query(
    $connect,
    "SELECT u.user_id AS cust_id,
            CONCAT(c.Fname,' ',c.Lname) AS name,
            u.email,
            c.phone,
            u.is_active,
            u.created_at
     FROM Users u
     JOIN Customer c ON u.user_id=c.cust_id
     $where
     ORDER BY u.user_id DESC 
     LIMIT $offset, $rowsPer"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Management</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-dashboard">
    <?php include 'sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-header">
            <h1>Customer Management</h1>
            <div class="profile-box">
                <div class="avatar">👤</div>
                <p class="username"><?= htmlspecialchars($_SESSION['adminUsername'] ?? 'Admin') ?></p>
            </div>
        </header>

        <div class="admin-content">
            
            <!-- Search & Filter Section -->
            <div class="content-section search-section">
                <form method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="search" placeholder="Name, email, phone..." 
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="form-group">
                            <select name="status">
                                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Customers</option>
                                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active Only</option>
                                <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>Blocked Only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                🔍 Filter
                            </button>
                            <?php if ($search || $status !== 'all'): ?>
                                <a href="customers.php" class="btn btn-secondary">
                                    ❌ Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="content-section">
                <h2>All Customers <small>(<?= $total ?>)</small></h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Orders</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($c = mysqli_fetch_assoc($customers)):
                                $orderCount = (int)(mysqli_fetch_assoc(
                                    mysqli_query($connect, "SELECT COUNT(*) c FROM `Order` WHERE cust_id={$c['cust_id']}")
                                )['c'] ?? 0);
                            ?>
                                <tr>
                                    <td>#CUST-<?= $c['cust_id'] ?></td>
                                    <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($c['email']) ?></td>
                                    <td><?= htmlspecialchars($c['phone']) ?></td>
                                    <td><?= $orderCount ?></td>
                                    <td>
                                        <span class="status <?= $c['is_active'] ? 'completed' : 'cancelled' ?>">
                                            <?= $c['is_active'] ? 'Active' : 'Blocked' ?>
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($c['created_at'])) ?></td>
                                    <td>
                                        <!-- Toggle Active/Blocked -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="cust_id" value="<?= $c['cust_id'] ?>">
                                            <input type="hidden" name="action" value="toggle">
                                            <button type="submit" 
                                                    class="btn btn-small <?= $c['is_active'] ? 'btn-danger' : 'btn-success' ?>" 
                                                    title="<?= $c['is_active'] ? 'Block' : 'Unblock' ?>">
                                                <?= $c['is_active'] ? '🔒' : '🔓' ?>
                                            </button>
                                        </form>

                                        <!-- View Orders -->
                                        <a href="orders.php?customer=<?= $c['cust_id'] ?>" 
                                           class="btn btn-small btn-primary" title="View orders">
                                            👁️
                                        </a>

                                        <!-- Delete Customer -->
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('Delete customer & all history?')">
                                            <input type="hidden" name="cust_id" value="<?= $c['cust_id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-small btn-danger">
                                                🗑️
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                               class="btn btn-small btn-secondary">
                                ← Prev
                            </a>
                        <?php endif; ?>

                        <span>Page <?= $page ?> of <?= $pages ?></span>

                        <?php if ($page < $pages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                               class="btn btn-small btn-secondary">
                                Next →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="Javascript/admin.js"></script>
</body>
</html>