<?php
session_start();

if (isset($_GET['action'])) {
    $id = $_GET['id'];
    
    if ($_GET['action'] == 'add') {
        $_SESSION['cart'][$id]++;
    } elseif ($_GET['action'] == 'remove') {
        if (isset($_SESSION['cart'][$id]) && $_SESSION['cart'][$id] > 1) {
            $_SESSION['cart'][$id]--;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    } elseif ($_GET['action'] == 'clear') {
        unset($_SESSION['cart']);
    }

    // NEW KNOWLEDGE: HTTP_REFERER sends the user back to EXACTLY where they clicked.
    // If they clicked + on the Cart Page, they stay on the Cart Page.
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: home.php"); // Fallback
    }
    exit();
}