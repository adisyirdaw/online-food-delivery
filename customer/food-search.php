<?php
// Include database connection
include('../connection.php');  

// Check if connection exists
if(!isset($connect) || !$connect){
    die("DB Connection Failed: " . mysqli_connect_error());
}

// Initialize search variable
$search = "";
if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($connect, $_GET['search']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Food Search</title>
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">
<link rel="stylesheet" href="css/search.css"> <!-- search specific styles -->
</head>
<body>
<?php include('header.php'); ?>

<section class="food-menu">
    <div class="container">
        <h2>Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>
        <div class="food-menu-list">
            <?php
            $sql = "SELECT * FROM foods 
                    WHERE active='yes'
                    AND (name LIKE '%$search%' 
                    OR description LIKE '%$search%')";
            $res = mysqli_query($connect, $sql);

            if($res && mysqli_num_rows($res) > 0){
                while($row = mysqli_fetch_assoc($res)){
            ?>
                <div class="food-menu-card">
                    <div class="food-menu-img">
                        <img src="../images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                    </div>
                    <div class="food-menu-info">
                        <h4><?php echo $row['name']; ?></h4>
                        <p class="food-price"><?php echo $row['price']; ?> Birr</p>
                        <p class="food-detail"><?php echo $row['description']; ?></p>
                        <a href="order.php?food_id=<?php echo $row['id']; ?>" class="btn btn-primary">Order Now</a>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='text-center'>No food found.</p>";
            }
            ?>
        </div>
    </div>
</section>

<?php include('footer.php'); ?>

</body>
</html>
