<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">

</head>
<body>
    <div class="header-space"></div>

    <section class="categories">
        <div class="container">

            <div class="clearfix"></div>

            <div id="category-container">

            <?php
            include('../connection.php'); // database connection

            // Select active and featured categories
            $sql = "SELECT * FROM Categories WHERE active='yes' AND featured='yes' ORDER BY created_at DESC";
            $res = mysqli_query($connect, $sql);

            if(mysqli_num_rows($res) > 0){
                while ($row = mysqli_fetch_assoc($res)) {

                    $id = $row['category_id'];
                    $name = $row['name'];
                    $image = $row['image'];

                    ?>
                    <!-- Link to foods.php with category_id parameter -->
                    <a href="foods.php?category_id=<?php echo $id; ?>">
                        <div class="box-3 float-container">
                            <?php if(!empty($image)){ ?>
                                <img src="../images/<?php echo $image; ?>" class="img-responsive img-curve" alt="<?php echo $name; ?>">
                            <?php } else { ?>
                                <img src="../images/default.jpg" class="img-responsive img-curve" alt="No image">
                            <?php } ?>
                            <h3 class="float-text text-white"><?php echo $name; ?></h3>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo "<p>No categories found.</p>";
            }

            mysqli_close($connect);
            ?>

            </div>

            <div class="clearfix"></div>
        </div>
    </section>

<?php include('footer.php'); ?>
</body>
</html>
