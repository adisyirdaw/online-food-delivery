<?php
session_start();
$cur = basename($_SERVER['PHP_SELF']);
?>

<div class="admin-sidebar">
    <div class="sidebar-header">
        <img src="../images/logo.png" alt="Logo">
        <h3>Ella Kitchen Cafe</h3>
        <p>Admin Panel</p>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <li class="<?= $cur == 'Dashboard.php' ? 'active' : '' ?>">
                <a href="Dashboard.php">📊 Dashboard</a>
            </li>
            <li class="<?= $cur == 'menu.php' ? 'active' : '' ?>">
                <a href="menu.php">🍽️ Menu</a>
            </li>
            <li class="<?= $cur == 'category.php' ? 'active' : '' ?>">
                <a href="category.php">🏷️ Categories</a>
            </li>
            <li class="<?= $cur == 'Registration.php' ? 'active' : '' ?>">
                <a href="Registration.php">👤➕ Staff Reg.</a>
            </li>
            <li class="<?= $cur == 'customers.php' ? 'active' : '' ?>">
                <a href="customers.php">👥 Customers</a>
            </li>
            <li class="<?= $cur == 'orders.php' ? 'active' : '' ?>">
                <a href="orders.php">🛒 Orders</a>
            </li>
            <li class="<?= $cur == 'Delivery-person.php' ? 'active' : '' ?>">
                <a href="Delivery-person.php">🏍️ Delivery person</a>
            </li>
            <li class="<?= $cur == 'report.php' ? 'active' : '' ?>">
                <a href="report.php">📈 Reports</a>
            </li>
            <li>
                <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                    🚪 Logout</a>
            </li>
        </ul>
    </nav>
</div>