<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config.php');

// Cart array ko initialize karna agar pehle se nahi hai
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// --- CASE 1: AJAX REQUEST (Home Page ke "Add to Cart" Button Se) ---
if (isset($_POST['ajax_add'])) {
    $product_id = intval($_POST['product_id']);
    
    // Check stock first
    $res = $conn->query("SELECT stock FROM products WHERE id = $product_id");
    if($res && $res->num_rows > 0) {
        $prod = $res->fetch_assoc();
        
        // Cart me pehle se maujood qty check karein
        $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
        
        if ($current_qty < $prod['stock']) {
            $_SESSION['cart'][$product_id] = $current_qty + 1;
            
            // Naya total count return karna JS ko
            echo array_sum($_SESSION['cart']);
        } else {
            echo "out_of_stock";
        }
    }
    exit();
}

// --- CASE 2: FORM SUBMIT (Product Details Page Se) ---
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if($quantity <= 0) { $quantity = 1; }

    $_SESSION['cart'][$product_id] = $quantity;
    
    header("Location: cart.php");
    exit();
}

// --- CASE 3: REMOVE ITEM FROM CART ---
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    header("Location: cart.php");
    exit();
}