<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css">
 
    </head>
    <body>

<header class="header">
    <div class="header-logo-part">
        <a href="home.php">
            <img class="logo" src="../images/logo.png" alt="Logo" style="height: 50px;">
        </a>
    </div>

    <div class="search-area">
        <form action="food-search.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search for food..." class="search-input" required>
            <button type="submit" class="search-btn">
                <img src="../images/search-icon.png" class="search-icon" style="width: 20px;">
            </button>
        </form>
    </div>

    <div class="header-icons-group">
        
        <a href="javascript:void(0)" onclick="toggleCart()" class="cart-box">
            <i class="fa-solid fa-cart-shopping" style="font-size: 20px; color: white;"></i>
            <span class="badge" style="position: absolute; top: -10px; right: -10px; background: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 10px;">
                <?php 
                    $total_items = 0;
                    if(isset($_SESSION['cart'])) {
                        foreach($_SESSION['cart'] as $qty) { $total_items += $qty; }
                    }
                    echo $total_items; 
                ?>
            </span>
        </a>

          <div class="user-area" style="position: relative;">
              <?php if (isset($_SESSION['user_id'])): ?>
                  <button onclick="toggleUserMenu()" class="user-name-btn">
                      <?php 
                        // FIX: Get first letter of username and make it uppercase
                        $displayName = isset($_SESSION['username']) ? $_SESSION['username'] : 'U';
                        echo strtoupper(substr($displayName, 0, 1)); 
                      ?> 
                  </button>
                  <div id="userDropdownContent" class="dropdown-content">


                      <a href="logout.php" style="color: red;">Logout</a>
                  </div>
              <?php else: ?>
                  <button onclick="openLogin()" class="user-name-btn">
                       Login
                  </button>
              <?php endif; ?>
          </div>
    </div>
</header>
<div id="cartModal" class="modal" style="display:none; position:fixed; z-index:1000; left:50%; top:50%; transform:translate(-50%, -50%); width:90%; max-width:450px; background:#fff; box-shadow:0 0 20px rgba(0,0,0,0.3); padding:20px; border-radius:15px; max-height:80vh; overflow-y:auto;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h2 style="color:#333;">🛒 Your Selection</h2>
        <span onclick="document.getElementById('cartModal').style.display='none'" style="cursor:pointer; font-size:28px; font-weight:bold;">&times;</span>
    </div>
    <hr style="border:0; border-top:1px solid #eee;">

    <div class="cart-content">
        <?php
        if(!empty($_SESSION['cart'])) {
            include('../connection.php');
            $total_price = 0;
            
            foreach($_SESSION['cart'] as $food_id => $qty) {
                $sql = "SELECT * FROM Foods WHERE food_id = $food_id";
                $res = mysqli_query($connect, $sql);
                if($row = mysqli_fetch_assoc($res)) {
                    $subtotal = $row['price'] * $qty;
                    $total_price += $subtotal;
                    ?>
                    <div style="display:flex; align-items:center; margin-bottom:15px; background:#f9f9f9; padding:10px; border-radius:10px;">
                        <img src="../images/<?php echo $row['image']; ?>" width="70" height="70" style="border-radius:10px; object-fit:cover;">
                        <div style="margin-left:15px; flex-grow:1;">
                            <h4 style="margin:0; font-size:16px;"><?php echo htmlspecialchars($row['name']); ?></h4>
                            <p style="margin:5px 0; color:#e67e22; font-weight:bold;"><?php echo number_format($row['price'], 2); ?> Birr</p>
                            
                            <div style="display:flex; align-items:center; gap:10px;">
                                <a href="cart_helper.php?action=remove&id=<?php echo $food_id; ?>" style="background:#eee; padding:2px 10px; border-radius:5px; text-decoration:none; color:#333;">-</a>
                                <span style="font-weight:bold;"><?php echo $qty; ?></span>
                                <a href="cart_helper.php?action=add&id=<?php echo $food_id; ?>" style="background:#eee; padding:2px 10px; border-radius:5px; text-decoration:none; color:#333;">+</a>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <p style="font-weight:bold;"><?php echo number_format($subtotal, 2); ?></p>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
            <div style="margin-top:20px; border-top:2px solid #eee; pt:10px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:15px;">
                    <span style="font-size:18px; font-weight:bold;">Total Amount:</span>
                    <span style="font-size:18px; font-weight:bold; color:#27ae60;"><?php echo number_format($total_price, 2); ?> Birr</span>
                </div>
                <a href="javascript:void(0)" onclick="openCheckout()" class="btn btn-primary" style="display:block; text-align:center; padding:12px; text-decoration:none; background:#ff6b6b; color:#fff; border-radius:8px; font-weight:bold;">Proceed to Checkout</a>
                <div style="text-align:center; margin-top:10px;">
                    <a href="cart_helper.php?action=clear" style="color:#999; font-size:13px; text-decoration:none;">Clear Entire Cart</a>
                </div>
            </div>
            <?php
        } else {
            echo "<div style='text-align:center; padding:40px;'><p style='color:#666;'>Your cart is lonely. Add some food!</p></div>";
        }
        ?>
    </div>
</div>

<div id="modalOverlay" onclick="document.getElementById('cartModal').style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;"></div>
<div id="checkoutModal" class="modal" style="display:none; position:fixed; z-index:1100; left:50%; top:50%; transform:translate(-50%, -50%); width:95%; max-width:600px; background:#fff; box-shadow:0 0 30px rgba(0,0,0,0.5); padding:25px; border-radius:15px; max-height:90vh; overflow-y:auto;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h2 style="margin:0;">Confirm Your Order</h2>
        <span onclick="document.getElementById('checkoutModal').style.display='none'" style="cursor:pointer; font-size:30px; font-weight:bold;">&times;</span>
    </div>
    <hr>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'insufficient_balance'): ?>
        <div style="background: #ff7675; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
            ❌ Insufficient balance in your digital wallet. Please choose Cash or top up.
        </div>
    <?php endif; ?>

    <form action="confirm_order_action.php" method="POST" id="orderForm">
        <div class="order-details-card" style="background:#f8f9fa; padding:15px; border-radius:10px; margin-bottom:20px;">
            <?php 
            include('../connection.php'); 
            $total = 0;
            if(!empty($_SESSION['cart'])):
                foreach($_SESSION['cart'] as $id => $qty):
                    $res = mysqli_query($connect, "SELECT * FROM Foods WHERE food_id=$id");
                    $food = mysqli_fetch_assoc($res);
                    $subtotal = $food['price'] * $qty;
                    $total += $subtotal;
            ?>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid #ddd; padding-bottom:5px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <img src="../images/<?php echo $food['image']; ?>" style="width:50px; height:50px; border-radius:5px; object-fit:cover;">
                    <div>
                        <h4 style="margin:0; font-size:14px;"><?php echo $food['name']; ?></h4>
                        <small><?php echo $food['price']; ?> Birr x <?php echo $qty; ?></small>
                    </div>
                </div>
                <div style="font-weight:bold;"><?php echo number_format($subtotal, 2); ?> Birr</div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:5px;">Delivery Address</label>
            <textarea name="delivery_address" required placeholder="Street, House No, Landmark..." style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; height:80px;"></textarea>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; background:#eee; padding:15px; border-radius:10px; margin-bottom:20px;">
            <h3 style="margin:0;">Total:</h3>
            <h3 style="margin:0; color:#27ae60;"><?php echo number_format($total, 2); ?> Birr</h3>
        </div>

        <div class="payment-methods" style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:10px;">Select Payment Method</label>
            <div style="display:flex; gap:15px; flex-wrap:wrap;">
                <label><input type="radio" name="payment_method" value="cash" checked> Cash</label>
                <label><input type="radio" name="payment_method" value="CBEbirr"> CBE Birr</label>
                <label><input type="radio" name="payment_method" value="telebirr"> telebirr</label>
            </div>
        </div>

        <div style="background: #fffcf0; padding: 15px; border-radius: 10px; border: 1px solid #ffeaa7; margin-bottom: 20px;">
            <h4 style="margin-top: 0; color:#d35400;"><i class="fas fa-map-marker-alt"></i> Delivery Tracking</h4>
            <p style="font-size: 0.8em; color: #666;">Allow real-time location for faster delivery?</p>
            
            <div style="display: flex; gap: 15px;">
                <label style="cursor: pointer;">
                    <input type="radio" name="location_permission" value="allow" onclick="requestBrowserLocation()" required> Allow
                </label>
                <label style="cursor: pointer;">
                    <input type="radio" name="location_permission" value="deny" onclick="clearLocation()" checked> Deny
                </label>
            </div>
            <input type="hidden" name="latitude" id="lat_val">
            <input type="hidden" name="longitude" id="lng_val">
            <div id="perm_status" style="margin-top: 10px; font-size: 0.8em; font-weight: bold;"></div>
        </div>

        <button type="submit" name="submit" style="width:100%; padding:15px; background:#27ae60; color:white; border:none; border-radius:10px; font-size:18px; font-weight:bold; cursor:pointer;">Confirm Order</button>
    </form>
</div>

<div id="loginModal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <span class="close-btn" onclick="closeModals()">&times;</span>
        <h2 class="text-center">Login</h2>
        <form method="POST" action="login_process.php">
            <input type="text" name="email" placeholder="Email or Username" required class="modal-input">
            <input type="password" name="password" placeholder="Password" required class="modal-input">
            <button type="submit" name="submit_login" class="btn-orange">Log in</button>
        </form>
        <p class="text-center mt-10">
            <a href="javascript:void(0)" onclick="openSignup()">Create an account</a>
        </p>
    </div>
</div>

<div id="signupModal" class="modal-overlay" style="display:none;">
    <div class="modal-card signup-card">
        <span class="close-btn" onclick="closeModals()">&times;</span>
        <h2 class="text-center">Create Account</h2>
        <form method="POST" action="signup_process.php">
            <div class="form-row">
                <input type="text" name="first_name" placeholder="First Name *" required class="modal-input">
                <input type="text" name="last_name" placeholder="Last Name *" required class="modal-input">
            </div>
            <input type="text" name="username" placeholder="Username *" required class="modal-input">
            <input type="email" name="email" placeholder="Email *" required class="modal-input">
            <textarea name="address" placeholder="Delivery Address *" required class="modal-input"></textarea>
            <input type="password" name="password" placeholder="Password *" required class="modal-input">
            <input type="password" name="confirm_password" placeholder="confirm_Password *" required class="modal-input">

            <button type="submit" name="submit_signup" class="btn-orange">Create Account</button>
        </form>
        <p class="text-center mt-10">
            <a href="javascript:void(0)" onclick="openLogin()">Already have account? Login</a>
        </p>
    </div>
</div>

<script>
function openLogin() {
    closeModals();
    document.getElementById('loginModal').style.display = 'flex';
}
function openSignup() {
    closeModals();
    document.getElementById('signupModal').style.display = 'flex';
}
function closeModals() {
    document.getElementById('loginModal').style.display = 'none';
    document.getElementById('signupModal').style.display = 'none';
    if(document.getElementById('cartModal')) document.getElementById('cartModal').style.display = 'none';
    if(document.getElementById('modalOverlay')) document.getElementById('modalOverlay').style.display = 'none';
    if(document.getElementById('checkoutModal')) document.getElementById('checkoutModal').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target.className === 'modal-overlay') {
        closeModals();
    }
}
</script>
<script>
document.querySelector('.cart-box').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('cartModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
});
function openCheckout() {
    document.getElementById('cartModal').style.display = 'none';
    document.getElementById('checkoutModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}
function requestBrowserLocation() {
    const status = document.getElementById('perm_status');
    if (!navigator.geolocation) { status.innerHTML = "❌ Browser does not support geolocation."; return; }
    navigator.geolocation.getCurrentPosition(
        (position) => {
            document.getElementById('lat_val').value = position.coords.latitude;
            document.getElementById('lng_val').value = position.coords.longitude;
            status.innerHTML = "✅ Location captured.";
            status.style.color = "#27ae60";
        },
        (error) => { status.innerHTML = "❌ Permission Denied."; status.style.color = "#c0392b"; }
    );
}
function clearLocation() {
    document.getElementById('lat_val').value = "";
    document.getElementById('lng_val').value = "";
    document.getElementById('perm_status').innerHTML = "ℹ️ Using text address only.";
}
</script>
<script>
function toggleUserMenu() {
    document.getElementById("userDropdownContent").classList.toggle("show");
}
window.onclick = function(event) {
    if (!event.target.matches('.user-name-btn') && !event.target.closest('.user-name-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}
</script>

<div class="header-space"></div>

</body>
</html>