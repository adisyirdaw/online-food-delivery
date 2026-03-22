<?php
require_once '../connection.php';
session_start();

function safe($str){
    global $connect;
    return mysqli_real_escape_string($connect,$str);
}
$action = $_POST['action'] ?? '';

/* ----------  CRUD  ---------- */
if ($action==='add_category'){
    $name  = safe($_POST['cat_name']  ?? '');
    $img   = safe($_POST['cat_image'] ?? '');
    $act   = isset($_POST['cat_active'])   ? 'yes' : 'no';
    $feat  = isset($_POST['cat_featured']) ? 'yes' : 'no';
    if ($name){
        mysqli_query($connect,
          "INSERT INTO Categories (name,image,active,featured) VALUES ('$name','$img','$act','$feat')");
        $_SESSION['toast']='Category added';
    }
}
if ($action==='update_category' && isset($_POST['category_id'])){
    $id    = (int)$_POST['category_id'];
    $name  = safe($_POST['cat_name']  ?? '');
    $img   = safe($_POST['cat_image'] ?? '');
    $act   = isset($_POST['cat_active'])   ? 'yes' : 'no';
    $feat  = isset($_POST['cat_featured']) ? 'yes' : 'no';
    mysqli_query($connect,
      "UPDATE Categories SET name='$name',image='$img',active='$act',featured='$feat' WHERE category_id=$id");
    $_SESSION['toast']='Category updated';
}
if ($action==='delete_category' && isset($_POST['category_id'])){
    $id=(int)$_POST['category_id'];
    $row=mysqli_fetch_assoc(mysqli_query($connect,
       "SELECT COUNT(*) AS c FROM Foods WHERE category_id=$id"));
    if ((int)$row['c']===0){
        mysqli_query($connect,"DELETE FROM Categories WHERE category_id=$id");
        $_SESSION['toast']='Category deleted';
    } else {
        $_SESSION['toast']='Category in use – cannot delete';
    }
    header('Location: category.php'); 
    exit;
}

/* ----------  READ  ---------- */
$categories = mysqli_query($connect,
   "SELECT category_id,name,image,active,featured FROM Categories ORDER BY name");

$edit = [];
if (isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($connect,
                  "SELECT * FROM Categories WHERE category_id=$id"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Manager</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;z-index:999}
        .modal-content{background:#fff;border-radius:12px;padding:25px 30px;width:90%;max-width:500px;box-shadow:0 10px 30px rgba(0,0,0,.25)}
        .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .modal-header h2{margin:0;font-size:1.3rem}
        .close-modal{background:none;border:none;font-size:1.3rem;cursor:pointer}
        .hide{display:none}
        .cat-img-thumb{height:40px;border-radius:4px;object-fit:cover;}
    </style>
</head>
<body class="admin-dashboard">
<?php include 'sidebar.php'; ?>

<div class="admin-main">
    <header class="admin-header">
        <h1>Category Manager</h1>
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

        <!-- ADD CATEGORY -->
        <div class="content-section">
            <h2><span>➕</span> Add New Category</h2>
            <form class="form-container" method="POST">
                <input type="hidden" name="action" value="add_category">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="cat_name" placeholder="e.g. Pizza" required>
                </div>
                <div class="form-group">
                    <label>Image file</label>
                    <input type="text" name="cat_image" placeholder="e.g. pizza.jpg"
                           oninput="previewImage(this.value,'catAddPreview')">
                    <img id="catAddPreview" class="image-preview hide" style="margin-top:8px">
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="cat_active" checked> Active
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="cat_featured"> Featured
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><span>➕</span> Add Category</button>
                    <button type="reset" class="btn btn-secondary"><span>🧹</span> Clear</button>
                </div>
            </form>
        </div>

        <!-- LIST CATEGORIES -->
        <div class="content-section">
            <h2><span>📋</span> Existing Categories (<?= mysqli_num_rows($categories) ?>)</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Image</th><th>Name</th><th>Active</th><th>Featured</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($c = mysqli_fetch_assoc($categories)): ?>
                        <tr>
                            <td>#<?= $c['category_id'] ?></td>
                            <td>
                                <?php if($c['image']): ?>
                                    <img src="../images/<?= htmlspecialchars(basename($c['image'])) ?>" class="cat-img-thumb">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= ucfirst($c['active']) ?></td>
                            <td><?= ucfirst($c['featured']) ?></td>
                            <td>
                                <a href="?edit=<?= $c['category_id'] ?>#catEditModal" class="btn btn-small btn-primary" title="Edit">
                                    ✏️
                                </a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete category?')">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="category_id" value="<?= $c['category_id'] ?>">
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

     <!-- CATEGORY EDIT MODAL -->
<?php if ($edit): ?>
<div class="modal-overlay" id="catEditModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><span>✏️</span> Edit Category</h2>
            <button class="close-modal" onclick="closeCatEdit()">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update_category">
            <input type="hidden" name="category_id" value="<?= $edit['category_id'] ?>">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="cat_name" value="<?= htmlspecialchars($edit['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Image file</label>
                <input type="text" name="cat_image" value="<?= htmlspecialchars(basename($edit['image'])) ?>"
                       oninput="previewImage(this.value,'catEditPreview')">
                <img id="catEditPreview" class="image-preview <?= $edit['image']?'':'hide' ?>" src="../images/<?= htmlspecialchars(basename($edit['image'])) ?>" style="margin-top:8px">
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="cat_active" <?= $edit['active']==='yes'?'checked':'' ?>> Active
                </label>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="cat_featured" <?= $edit['featured']==='yes'?'checked':'' ?>> Featured
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><span>💾</span> Save changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeCatEdit()"><span>❌</span> Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>window.onload=()=>document.getElementById('catEditModal').classList.remove('hide');</script>
<?php endif; ?>

<script src="Javascript/admin.js"></script>
</body>
</html>