<?php
session_start();

if (isset($_POST['food_id'])) {
    $food_id = $_POST['food_id'];
    $redirect = $_POST['redirect_to'];

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Add item to cart (using food_id as key, quantity as value)
    if (isset($_SESSION['cart'][$food_id])) {
        $_SESSION['cart'][$food_id]++;
    } else {
        $_SESSION['cart'][$food_id] = 1;
    }

    // Redirect back to the page the user was on
    header("Location: " . $redirect);
    exit();
} else {
    header("Location: home.php");
    exit();
}