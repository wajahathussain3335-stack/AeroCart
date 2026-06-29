<?php
include('config.php');
include('includes/header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$grand_total = 0;
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Your Shopping Cart</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            <?php if (!empty($cart_items)): ?>
                <?php 
                // Cart ki keys (product IDs) nikal kar SQL IN clause banana
                $ids = implode(',', array_keys($cart_items));
                $sql = "SELECT p.*, u.name AS seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id IN ($ids)";
                $result = $conn->query($sql);
                
                while($item = $result->fetch_assoc()): 
                    $qty = $cart_items[$item['id']];
                    $item_total = $item['price'] * $qty;
                    $grand_total += $item_total;
                ?>
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center space-x-4 w-full sm:w-auto">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" class="w-20 h-20 object-cover rounded-lg border">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="text-xs text-gray-400">Sold by: <span class="text-gray-600 font-medium"><?php echo htmlspecialchars($item['seller_name']); ?></span></p>
                                <p class="text-sm font-semibold text-blue-600 mt-1">PKR <?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-8 w-full sm:w-auto border-t sm:border-t-0 pt-3 sm:pt-0">
                            <div class="text-sm text-gray-600">
                                <span class="font-medium text-gray-500">Qty:</span> <?php echo $qty; ?>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-400">Total</p>
                                <p class="font-bold text-gray-900">PKR <?php echo number_format($item_total, 2); ?></p>
                            </div>
                            <a href="cart_action.php?remove=<?php echo $item['id']; ?>" class="text-red-500 hover:text-red-700 p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-14v4G19 7h-14" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white p-8 rounded-xl border text-center text-gray-500">
                    <p class="text-lg font-medium mb-2">Your cart is empty!</p>
                    <a href="index.php" class="text-blue-600 hover:underline text-sm font-semibold">Continue Shopping &rarr;</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm h-fit">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Order Summary</h2>
            <div class="space-y-3 text-sm text-gray-600 border-b pb-4">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-900">PKR <?php echo number_format($grand_total, 2); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping Fee</span>
                    <span class="text-green-600 font-semibold">FREE</span>
                </div>
            </div>
            <div class="flex justify-between items-center my-4">
                <span class="text-base font-bold text-gray-900">Grand Total</span>
                <span class="text-xl font-black text-blue-600">PKR <?php echo number_format($grand_total, 2); ?></span>
            </div>

            <?php if (!empty($cart_items)): ?>
                <a href="checkout.php" class="block text-center w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition shadow-md text-sm">
                    Proceed to Checkout
                </a>
            <?php else: ?>
                <button disabled class="w-full bg-gray-200 text-gray-400 font-bold py-3 px-4 rounded-lg cursor-not-allowed text-sm text-center">
                    Cart is Empty
                </button>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>