<?php
include('config.php');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['confirm_order'])) {
    header("Location: index.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];
$total_amount = floatval($_POST['total_amount']);
$shipping_address = $conn->real_escape_string($_POST['shipping_address']);
$phone_number = $conn->real_escape_string($_POST['phone_number']);
$payment_method = $conn->real_escape_string($_POST['payment_method']);
$cart_items = $_SESSION['cart'];

// --- 1. INSERT INTO ORDERS TABLE ---
$order_sql = "INSERT INTO orders (buyer_id, total_amount, shipping_address, phone_number, payment_method, status) 
              VALUES ('$buyer_id', '$total_amount', '$shipping_address', '$phone_number', '$payment_method', 'pending')";

if ($conn->query($order_sql) === TRUE) {
    // Naye bane hue order ki ID nikalna
    $order_id = $conn->insert_id;

    // --- 2. LOOP THROUGH CART AND INSERT INTO ORDER_ITEMS ---
    foreach ($cart_items as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);

        // Product ki price aur seller_id database se nikalna
        $prod_res = $conn->query("SELECT price, seller_id, stock FROM products WHERE id = $product_id");
        if ($prod_res && $prod_res->num_rows > 0) {
            $prod_data = $prod_res->fetch_assoc();
            $price = $prod_data['price'];
            $seller_id = $prod_data['seller_id'];
            $current_stock = $prod_data['stock'];

            // Order items mein record insert karna (according to your constraints)
            $item_sql = "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) 
                         VALUES ('$order_id', '$product_id', '$seller_id', '$quantity', '$price')";
            $conn->query($item_sql);

            // --- 3. INVENTORY MANAGEMENT (Stock Update) ---
            $new_stock = $current_stock - $quantity;
            if($new_stock < 0) { $new_stock = 0; } // Safety check
            $conn->query("UPDATE products SET stock = $new_stock WHERE id = $product_id");
        }
    }

    // --- 4. CLEAR THE CART SESSION ---
    unset($_SESSION['cart']);

    // Order Success page par bhej dena
    header("Location: order_success.php?id=" . $order_id);
    exit();

} else {
    echo "Error placing order: " . $conn->error;
}
?>