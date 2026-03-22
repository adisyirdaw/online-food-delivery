<?php 
include('../connection.php');
// The session is started inside db_connect.php, so we don't call it here.

if(!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: categories.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Order - Ella Kitchen</title>
    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php if(isset($_GET['error']) && $_GET['error'] == 'insufficient_balance'): ?>
    <div style="background: #ff7675; color: white; padding: 10px; border-radius: 5px; margin: 15px auto; max-width: 600px; text-align: center;">
        ❌ Insufficient balance in your digital wallet. Please choose Cash or top up.
    </div>
<?php endif; ?>

<div class="container order-container">
    <div class="confirm-order-box">
        <h2>Confirm Your Order</h2>
        
        <form action="confirm_order_action.php" method="POST" id="orderForm">
            <div class="order-details-card">
                <?php 
                $total = 0;
                foreach($_SESSION['cart'] as $id => $qty):
                    $res = mysqli_query($conn, "SELECT * FROM Foods WHERE food_id=$id");
                    $food = mysqli_fetch_assoc($res);
                    $subtotal = $food['price'] * $qty;
                    $total += $subtotal;
                ?>
                <div class="order-item-row">
                    <img src="images/<?php echo $food['image']; ?>" class="order-img" style="width:50px;">
                    <div class="order-info">
                        <h4><?php echo $food['name']; ?></h4>
                        <p><?php echo $food['price']; ?> Birr x <?php echo $qty; ?></p>
                    </div>
                    <div class="order-subtotal"><?php echo number_format($subtotal, 2); ?> Birr</div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="input-group">
                <label>Delivery Address</label>
                <textarea name="delivery_address" required placeholder="Enter your full address"></textarea>
            </div>

            <div class="grand-total">
                <h3>Total: <?php echo number_format($total, 2); ?> Birr</h3>
            </div>

            <div class="payment-methods">
                <label class="section-label">Select Payment Method</label>
                <div class="payment-options">
                    <label><input type="radio" name="payment_method" value="cash" checked> Cash</label>
                    <label><input type="radio" name="payment_method" value="CBEbirr"> CBE Birr</label>
                    <label><input type="radio" name="payment_method" value="telebirr"> telebirr</label>
                </div>
            </div>
            <div class="location-section" style="margin-top: 15px;">
            <div class="tracking-permission-box" style="background: #f8f9fa; padding: 15px; border-radius: 10px; border: 1px solid #e0e0e0; margin-top: 15px;">
    <h4 style="margin-top: 0;"><i class="fas fa-map-marker-alt" style="color: #e67e22;"></i> Delivery Tracking</h4>
    <p style="font-size: 0.85em; color: #666;">Allow delivery person to see your real-time location for faster delivery?</p>
    
    <div style="display: flex; gap: 20px; align-items: center;">
        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <input type="radio" name="location_permission" value="allow" onclick="requestBrowserLocation()" required> 
            <span style="color: #27ae60; font-weight: bold;">Allow Permission</span>
        </label>
        
        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <input type="radio" name="location_permission" value="deny" onclick="clearLocation()" checked> 
            <span style="color: #c0392b; font-weight: bold;">Deny / Use Address Only</span>
        </label>
    </div>

    <input type="hidden" name="latitude" id="lat_val">
    <input type="hidden" name="longitude" id="lng_val">
    <div id="perm_status" style="margin-top: 10px; font-size: 0.8em; font-weight: bold;"></div>
</div>


             
            <button type="submit" name="submit" class="btn-confirm-order">Confirm Order</button>
        </form>
    </div>
</div>
<script>
function requestBrowserLocation() {
    const status = document.getElementById('perm_status');
    
    if (!navigator.geolocation) {
        status.innerHTML = "❌ Browser does not support geolocation.";
        return;
    }

    status.innerHTML = "⏳ Requesting permission...";
    status.style.color = "#f39c12";

    navigator.geolocation.getCurrentPosition(
        (position) => {
            document.getElementById('lat_val').value = position.coords.latitude;
            document.getElementById('lng_val').value = position.coords.longitude;
            status.innerHTML = "✅ Permission Granted. Coordinates captured.";
            status.style.color = "#27ae60";
        },
        (error) => {
            status.innerHTML = "❌ Permission Denied by browser.";
            status.style.color = "#c0392b";
            // Re-check the Deny radio if they blocked the browser popup
            document.querySelector('input[value="deny"]').checked = true;
        }
    );
}

function clearLocation() {
    document.getElementById('lat_val').value = "";
    document.getElementById('lng_val').value = "";
    document.getElementById('perm_status').innerHTML = "ℹ️ Delivery person will use your text address only.";
    document.getElementById('perm_status').style.color = "#666";
}
</script>
</body>
</html>