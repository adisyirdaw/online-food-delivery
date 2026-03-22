<?php
include('header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<!-- Important to make website responsive -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restaurant Website</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Link our CSS file -->
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/box.css">
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">
</head>
<body>
<!-- Link our header-part-start-->   

<section class="food-search text-center">
<div class="container">

<div class="menu  text-right">
<ul>
<li>
<a href="home.php">Home</a>
</li>
<li>
<a href="categories.php">Categories</a>
</li>
<li>
<a href="foods.php">Menu</a>
</li>
<li>
<a href="#">About</a>
</li>
</ul>
</div>
</div>
</section>
<!-- fOOD sEARCH Section Ends Here -->
<!-- Categories Header -->
<div class="header-space"></div>

<section class="promo-section">
    <div class="container">
        <!-- Section Header -->
        <div class="promo-header">
            <h2>Featured Categories</h2>
            <p>Explore our delicious food categories, each crafted with passion and quality ingredients</p>
        </div>
        
        <!-- Categories Grid -->
        <div class="promo-row">
            <?php
            include('../connection.php');
            
            // Modified query to get category count
            $sql = "SELECT c.*, 
                    (SELECT COUNT(*) FROM foods f WHERE f.category_id = c.category_id AND f.active='yes') as food_count 
                    FROM Categories c 
                    WHERE c.active='yes' AND c.featured='yes' 
                    ORDER BY c.created_at DESC 
                    LIMIT 6";
            $res = mysqli_query($connect, $sql);
            
            if(mysqli_num_rows($res) > 0){
                while($row = mysqli_fetch_assoc($res)){
                    $id    = $row['category_id'];
                    $name  = $row['name'];
                    $image = $row['image'];
                    $count = $row['food_count'] ?? 0;
                    
                    $imgPath = !empty($image)
                        ? "../images/".$image
                        : "../images/default.jpg";
            ?>
            
            <a href="foods.php?category_id=<?php echo $id; ?>" class="promo-link">
                <div class="promo-item">
                    <!-- Items Count Badge -->
                    <div class="promo-count"><?php echo $count; ?> items</div>
                    
                    <!-- Category Image -->
                    <img src="<?php echo $imgPath; ?>" 
                         class="promo-img-circle" 
                         alt="<?php echo htmlspecialchars($name); ?>"
                         loading="lazy">
                    
                    <!-- Category Name -->
                    <p class="promo-title"><?php echo $name; ?></p>
                </div>
            </a>
            
            <?php
                }
            } else {
                echo "<p>No categories found. Please check back later!</p>";
            }
            
            mysqli_close($connect);
            ?>
        </div>
        
        <!-- View All Button -->
        <div style="text-align: center; margin-top: 50px;">
            <a href="categories.php" class="promo-view-more">
                <i class="fas fa-chevron-right"></i> View All Categories
            </a>
        </div>
    </div>
</section>

  </div>
</div>








<section class="foods-section">
    <h2 class="section-title">Our Foods</h2>
    <div class="foods-row">
        <?php
       include('../connection.php');
        if(!$connect){
            die("Connection failed: " . mysqli_connect_error());
        }

        // Fetch random foods from database
        $sql2 = "SELECT * FROM foods WHERE active='Yes' AND featured='Yes' ORDER BY RAND() LIMIT 10";
        $res2 = mysqli_query($connect, $sql2);

        if(mysqli_num_rows($res2) > 0){
            while($items = mysqli_fetch_assoc($res2)){
                $title = $items['title'];
                $price = $items['price'];
                $detail = $items['description'];
                $image = $items['image']; // filename from DB
                ?>
                <div class="section-divider">
                    <div class="food-menu-box">
                        <div class="food-menu-img">
                            <?php if(!empty($image)) { ?>
                                <img src="../images/<?php echo $image; ?>" alt="<?php echo $title; ?>" class="img-responsive img-curve">
                            <?php } else { ?>
                                <img src="../images/default.jpg" alt="No image" class="img-responsive img-curve">
                            <?php } ?>
                        </div>

                                        <div class="food-menu-desc">
    <h4><?php echo htmlspecialchars($items['name']); ?></h4>
    <p class="food-price"><?php echo number_format($items['price'], 2); ?> Birr</p>
    
    <form action="add_to_cart_action.php" method="POST">
        <input type="hidden" name="food_id" value="<?php echo $items['food_id']; ?>">
        <input type="hidden" name="redirect_to" value="home.php">
        <button type="submit" name="submit" class="btn btn-primary">
            Add to Cart
        </button>
    </form>
</div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No foods found.</p>";
        }

        mysqli_close($connect);
        ?>
    </div>
</section>

<section class="food-menu"> 



</section>















<script src="script/foodsjs"> </script>
<?php
include('footer.php');
?>
</body>
</html>
 

