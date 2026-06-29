<?php
include('config.php');
session_start();

// Security Check: Agar user logged in nahi hai, toh login page par bhejo
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Agar cart khali hai, toh checkout par aane ki zaroorat nahi
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

include('includes/header.php');

$cart_items = $_SESSION['cart'];
$grand_total = 0;

// Total calculation ke liye dobara loop
$ids = implode(',', array_keys($cart_items));
$sql = "SELECT price, id FROM products WHERE id IN ($ids)";
$result = $conn->query($sql);
while($item = $result->fetch_assoc()) {
    $grand_total += $item['price'] * $cart_items[$item['id']];
}
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Checkout</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-100 shadow-sm h-fit">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Shipping Information</h2>
            
            <form action="place_order.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" disabled value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" class="mt-1 w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input name="phone_number" type="tel" required placeholder="e.g. 03001234567" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Complete Shipping Address</label>
                    <textarea name="shipping_address" rows="4" required placeholder="House#, Street Name, Area, City..." class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <div class="p-4 border border-blue-200 bg-blue-50 rounded-lg flex items-center space-x-3">
                        <input type="radio" checked name="payment_method" value="COD" class="h-4 w-4 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Cash on Delivery (COD)</p>
                            <p class="text-xs text-gray-500">Pay with cash upon delivery to your doorstep.</p>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="total_amount" value="<?php echo $grand_total; ?>">

                <button type="submit" name="confirm_order" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition shadow-md text-sm mt-6">
                    Confirm & Place Order (PKR <?php echo number_format($grand_total, 2); ?>)
                </button>
            </form>
        </div>

        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 h-fit">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Items Summary</h2>
            <div class="divide-y divide-gray-200">
                <?php
                // Items display karne ke liye query dobara chalayein complete details ke sath
                $sql_details = "SELECT * FROM products WHERE id IN ($ids)";
                $res_details = $conn->query($sql_details);
                while($prod = $res_details->fetch_assoc()):
                    $qty = $cart_items[$prod['id']];
                ?>
                    <div class="flex justify-between py-3 text-sm">
                        <div class="pr-4">
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($prod['title']); ?></p>
                            <p class="text-xs text-gray-500">Qty: <?php echo $qty; ?></p>
                        </div>
                        <span class="font-medium text-gray-900">PKR <?php echo number_format($prod['price'] * $qty); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="border-t pt-4 mt-2 flex justify-between items-center">
                <span class="text-base font-bold text-gray-900">Total Amount</span>
                <span class="text-lg font-black text-blue-600">PKR <?php echo number_format($grand_total); ?></span>
            </div>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>