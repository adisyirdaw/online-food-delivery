<?php 
include('header.php');
include('../connection.php'); // database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Foods</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/box.css">
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">
</head>
<body>
<section class="food-section">
<div class="container">
<div class="foods-row">
<?php
// Ensure database connection is included
include('../connection.php');

// Check if a category is selected and force integer
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if($category_id > 0){
    // Filter foods by category
    $sql = "SELECT * FROM Foods WHERE active='yes' AND category_id=$category_id";
} else {
    // Show all active foods
    $sql = "SELECT * FROM Foods WHERE active='yes'";
}

$res = mysqli_query($connect, $sql);

if(!$res){
    die("Query failed: " . mysqli_error($connect));
}

if(mysqli_num_rows($res) > 0){
    while($row = mysqli_fetch_assoc($res)){

        $id     = $row['food_id'];       
        $title  = $row['name'];          
        $price  = $row['price'];
        $detail = $row['description'];
        $image  = $row['image'];
        ?>

        <div class="food-menu-box">

            <div class="food-menu-img">
                <?php if(!empty($image)) { ?>
                    <img src="../images/<?php echo $image; ?>" alt="<?php echo htmlspecialchars($title); ?>" class="img-responsive img-curve">
                <?php } else { ?>
                    <img src="../images/default.jpg" alt="No Image" class="img-responsive img-curve">
                <?php } ?>
            </div>

            
<div class="food-menu-desc">
<h4><?php echo htmlspecialchars($title); ?></h4>
<p class="food-price"><?php echo number_format($price, 2); ?> Birr</p>
<p class="food-detail"><?php echo htmlspecialchars($detail); ?></p>
<br>

<form action="add_to_cart_action.php" method="POST">
<input type="hidden" name="food_id" value="<?php echo $id; ?>">

<?php 
    $current_url = "foods.php" . ($category_id > 0 ? "?category_id=$category_id" : "");
?>
<input type="hidden" name="redirect_to" value="<?php echo $current_url; ?>">

<button type="submit" name="submit" class="btn btn-primary">
    Add to Cart
</button>
</form>
</div>
</div>
<?php }
} else { ?>
    <p class="text-center">No foods available.</p>
<?php } ?>

<div class="clearfix"></div>
</div>

</div>
</section>

<?php 
mysqli_close($connect);
include('footer.php'); 
?>
</body>
</html>





