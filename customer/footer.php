<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
<link rel="stylesheet" href="css/footer.css">
<!-- Add Font Awesome for icons -->

</head>
<body>

<!-- FOOTER SECTION -->
<footer class="footer">
    <div class="footer-container">
        <!-- Brand Column -->
        <div class="footer-col">
            <div class="footer-col-header">
                <h3>Ella Kitchen & Cafe</h3>
            </div>
            <p>Your best place to order delicious food online.</p>
           
        </div>

        <!-- Quick Links -->
        <div class="footer-col">
            <div class="footer-col-header">
                <h4>Quick Links</h4>
            </div>
            <ul>
                <li><a href="home.php"> Home</a></li>
                <li><a href="categories.php"> Categories</a></li>
                <li><a href="foods.php"></i>menu</a></li>
                <li><a href="#" id="openComplaint" class="complaint-link">Complaint</a></li>
            </ul>
        </div>

        <!-- Contact Info -->
        <div class="footer-col">
            <div class="footer-col-header">
                <h4>Contact Us</h4>
            </div>
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">
                       
                    </div>
                    <span>Hossana, Ethiopia</span>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                      
                    </div>
                    <span>+251 9-61-28-28-02</span>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                  
                    </div>
                    <span>yoseflire28@gmail.com</span>
                </div>
            </div>
        </div>

        <!-- Hours & Logout -->
        <div class="footer-col">
            <div class="footer-col-header">
                <h4>Opening Hours</h4>
            </div>
            <ul class="opening-hours">
                <li><strong>Mon - Fri:</strong> 8AM - 10PM</li>
                <li><strong>Saturday:</strong> 9AM - 11PM</li>
                <li><strong>Sunday:</strong> 10AM - 9PM</li>
            </ul>
            
            <!-- Logout Button (if logged in) -->
            <?php if(isset($_SESSION['user_id'])): ?>
            <div class="logout-section">
                <form action="logout.php" method="POST">
                    <button type="submit" class="logout-btn">
                        Log Out
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2025 Ella Kitchen & Cafe. All Rights Reserved.</p>
        <div class="footer-links">
            <a href="privacy.php">Privacy Policy</a>
            <a href="terms.php">Terms of Service</a>
            <a href="faq.php">FAQ</a>
        </div>
    </div>

    <a href="#" class="back-to-top" title="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </a>
</footer> 
<script src="script/complain.js"></script>

</body>
</html>
<?php
include('complain.php');
?>