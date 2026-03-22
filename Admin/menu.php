<?php
require_once '../connection.php';
session_start();

/* ----------  FOOD CRUD  ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function safe($str) {
        global $connect;
        return mysqli_real_escape_string($connect, $str);
    }
    $action = $_POST['action'] ?? '';

    /*  ADD  */
    if ($action === 'add') {
        $n  = safe($_POST['name'] ?? '');
        $d  = safe($_POST['description'] ?? '');
        $p  = (float)($_POST['price'] ?? 0);
        $catName = safe($_POST['category_name'] ?? '');
        $i  = safe($_POST['image_path'] ?? '');
        $a  = isset($_POST['active']) ? 'yes' : 'no';

        if ($n && $p > 0 && $catName) {
            // create category if not exists
            $row = mysqli_fetch_assoc(mysqli_query($connect,
                "SELECT category_id FROM Categories WHERE name='$catName' LIMIT 1"));
            if (!$row) {
                mysqli_query($connect, "INSERT INTO Categories (name,active,featured) VALUES ('$catName','yes','no')");
                $c = mysqli_insert_id($connect);
            } else {
                $c = (int)$row['category_id'];
            }
            mysqli_query($connect,
                "INSERT INTO Foods (name,description,price,category_id,image,active)
                 VALUES ('$n','$d',$p,$c,'$i','$a')");
            $_SESSION['toast'] = 'Item added';
        }
    }

    /*  UPDATE  */
    if ($action === 'update' && isset($_POST['food_id'])) {
        $id = (int)$_POST['food_id'];
        $n  = safe($_POST['name'] ?? '');
        $d  = safe($_POST['description'] ?? '');
        $p  = (float)($_POST['price'] ?? 0);
        $c  = (int)($_POST['category_id'] ?? 0);
        $i  = safe($_POST['image_path'] ?? '');
        $a  = isset($_POST['active']) ? 'yes' : 'no';
        mysqli_query($connect,
            "UPDATE Foods
             SET name='$n',description='$d',price=$p,category_id=$c,image='$i',active='$a'
             WHERE food_id=$id");
        $_SESSION['toast'] = 'Item updated';
    }

    /*  DELETE  */
    if ($action === 'delete' && isset($_POST['food_id'])) {
        $id = (int)$_POST['food_id'];
        mysqli_query($connect, "DELETE FROM Foods WHERE food_id=$id");
        $_SESSION['toast'] = 'Item deleted';
    }

    /*  TOGGLE  */
    if ($action === 'toggle' && isset($_POST['food_id'])) {
        $id = (int)$_POST['food_id'];
        mysqli_query($connect, "UPDATE Foods SET active=IF(active='yes','no','yes') WHERE food_id=$id");
        $_SESSION['toast'] = 'Status toggled';
    }

    /*  FEATURED TOGGLE  */
    if ($action === 'feature_toggle' && isset($_POST['food_id'])) {
        $id = (int)$_POST['food_id'];
        mysqli_query($connect,
            "UPDATE Foods SET featured=IF(featured='yes','no','yes') WHERE food_id=$id");
        $_SESSION['toast'] = 'Featured status toggled';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

/* ----------  READ  ---------- */
$categories = mysqli_query($connect, "SELECT category_id,name FROM Categories WHERE active='yes' ORDER BY name");
$foods      = mysqli_query($connect,
    "SELECT f.*, c.name cat_name
     FROM Foods f
     JOIN Categories c ON f.category_id=c.category_id
     ORDER BY f.food_id DESC");

$edit = [];
if (isset($_GET['edit'])) {
    $id   = (int)$_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Foods WHERE food_id=$id"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu Management</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;z-index:999}
        .modal-content{background:#fff;border-radius:12px;padding:25px 30px;width:90%;max-width:500px;box-shadow:0 10px 30px rgba(0,0,0,.25)}
        .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .modal-header h2{margin:0;font-size:1.3rem}
        .close-modal{background:none;border:none;font-size:1.3rem;cursor:pointer}
        .hide{display:none}
        .status-toggle.featured      {background:#28a745;color:#fff;}
        .status-toggle.not-featured  {background:#6c757d;color:#fff;}
    </style>
</head>
<body class="admin-dashboard">
<?php include 'sidebar.php'; ?>

<div class="admin-main">
    <header class="admin-header">
        <h1>Menu Management</h1>
        <div class="profile-box">
            <div class="avatar">👤</div>
            <p class="username"><?= htmlspecialchars($_SESSION['adminUsername'] ?? 'Admin') ?></p>
        </div>
    </header>

    <div class="admin-content">
        <?php if (isset($_SESSION['toast'])): ?>
            <div class="alert alert-success">✅ <?= $_SESSION['toast'] ?></div>
            <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>

        <!-- ADD ITEM -->
        <div class="content-section">
            <h2><span>➕</span> Add New Menu Item</h2>
            <form class="form-container" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label>Item Name *</label>
                        <input type="text" name="name" placeholder="e.g. Margherita Pizza" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_name" required>
                            <option value="" disabled selected>Choose category</option>
                            <?php
                            mysqli_data_seek($categories,0);
                            while($c=mysqli_fetch_assoc($categories)):
                                echo '<option>'.htmlspecialchars($c['name']).'</option>';
                            endwhile;
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (Birr) *</label>
                        <input type="number" step="0.01" min="0" name="price" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Image file</label>
                        <input type="text" name="image_path" placeholder="e.g. burger.jpg"
                               oninput="previewImage(this.value,'addPreview')">
                        <img id="addPreview" class="image-preview hide" style="margin-top:8px">
                        <input type="hidden" id="imgBase" value="../images/">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Short description..."></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="active" checked> Active (available for order)
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><span>➕</span> Add Item</button>
                    <button type="reset" class="btn btn-secondary"><span>🧹</span> Clear</button>
                </div>
            </form>
        </div>

        <!-- LIST ITEMS -->
        <div class="content-section">
            <h2><span>📋</span> Existing Items (<?= mysqli_num_rows($foods) ?>)</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Image</th><th>Name</th><th>Description</th>
                            <th>Price</th><th>Category</th>
                            <th>Food Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($f = mysqli_fetch_assoc($foods)): ?>
                        <tr>
                            <td>#<?= $f['food_id'] ?></td>
                            <td>
                                <img src="../images/<?= htmlspecialchars(basename($f['image'])) ?>"
                                     class="table-img-small"
                                     onerror="this.src='../images/default-food.jpg'">
                            </td>
                            <td><?= htmlspecialchars($f['name']) ?></td>
                            <td><?= htmlspecialchars($f['description']) ?></td>
                            <td><?= number_format($f['price'],2) ?> Birr</td>
                            <td><?= htmlspecialchars($f['cat_name']) ?></td>

                            <!-- Food Status toggle -->
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="food_id" value="<?= $f['food_id'] ?>">
                                    <button type="submit"
                                            class="status-toggle <?= $f['active']==='yes'?'available':'unavailable' ?>"
                                            title="Toggle availability">
                                        <?= $f['active']==='yes'?'✅ Available':'❌ Unavailable' ?>
                                    </button>
                                </form>
                            </td>

                            <!-- Featured toggle -->
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="feature_toggle">
                                    <input type="hidden" name="food_id" value="<?= $f['food_id'] ?>">
                                    <button type="submit"
                                            class="status-toggle <?= $f['featured']==='yes'?'featured':'not-featured' ?>"
                                            title="Toggle featured">
                                        <?= $f['featured']==='yes'?'⭐ Featured':'📌 Not Featured' ?>
                                    </button>
                                </form>
                            </td>

                            <td>
                                <a href="?edit=<?= $f['food_id'] ?>#editModal" class="btn btn-small btn-primary" title="Edit">
                                    ✏️
                                </a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this item?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="food_id" value="<?= $f['food_id'] ?>">
                                    <button type="submit" class="btn btn-small btn-danger" title="Delete">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<?php if ($edit): ?>
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><span>✏️</span> Edit Item</h2>
            <button class="close-modal" onclick="closeEdit()">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="food_id" value="<?= $edit['food_id'] ?>">
            <div class="form-row">
                <div class="form-group"><label>Name *</label><input type="text" name="name" value="<?= htmlspecialchars($edit['name']) ?>" required></div>
                <div class="form-group"><label>Price (Birr) *</label><input type="number" step="0.01" min="0" name="price" value="<?= $edit['price'] ?>" required></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <?php
                        mysqli_data_seek($categories,0);
                        while ($c=mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $c['category_id'] ?>" <?= $c['category_id']==$edit['category_id']?'selected':'' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image file</label>
                    <input type="text" name="image_path" value="<?= htmlspecialchars(basename($edit['image'])) ?>"
                           oninput="previewImage(this.value,'editPreview')">
                    <img id="editPreview" class="image-preview <?= $edit['image']?'':'hide' ?>" src="../images/<?= htmlspecialchars(basename($edit['image'])) ?>" style="margin-top:8px">
                </div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= htmlspecialchars($edit['description']) ?></textarea></div>
            <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="active" <?= $edit['active']==='yes'?'checked':'' ?>> Active</label></div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><span>💾</span> Save changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeEdit()"><span>❌</span> Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>window.onload=()=>document.getElementById('editModal').classList.remove('hide');</script>
<?php endif; ?>

<script src="Javascript/admin.js"></script>
</body>
</html>