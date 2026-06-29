<?php
include('includes/header.php');
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<main class="max-w-md mx-auto px-4 py-20 text-center">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center">
        <div class="h-16 w-16 bg-green-50 text-green-500 rounded-full flex items-center justify-center mb-6 border border-green-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Order Placed Successfully!</h1>
        <p class="text-sm text-gray-500 mt-2">Thank you for shopping with AeroCart. Your order has been registered.</p>
        
        <div class="bg-gray-50 p-3 rounded-lg w-full my-6 border border-gray-100 text-sm">
            <span class="text-gray-500">Order Reference ID:</span>
            <span class="font-bold text-gray-900">#AERO-<?php echo $order_id; ?></span>
        </div>

        <div class="space-y-2 w-full">
            <a href="index.php" class="block w-full bg-blue-600 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-blue-700 transition text-sm">
                Continue Shopping
            </a>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>