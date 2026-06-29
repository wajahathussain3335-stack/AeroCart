<?php
include('config.php');
session_start();

// Security Check: Agar seller nahi hai to login par bhejo
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$message = "";
$messageClass = "";

// --- STATUS UPDATE LOGIC ---
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    
    // Status update query according to your enum fields
    $update_sql = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
    if ($conn->query($update_sql) === TRUE) {
        $message = "Order status updated to successfully!";
        $messageClass = "text-green-600 bg-green-50";
    } else {
        $message = "Error updating status: " . $conn->error;
        $messageClass = "text-red-600 bg-red-50";
    }
}

// --- FETCH ORDERS RECEIVED FOR THIS SELLER ---
// Database Schema ke mutabiq tables ko join karna
$orders_sql = "SELECT oi.price as item_price, oi.quantity as item_qty, oi.order_id,
                      p.title as prod_title, p.image as prod_image,
                      o.shipping_address, o.phone_number, o.status, o.created_at,
                      u.name as buyer_name
               FROM order_items oi
               JOIN products p ON oi.product_id = p.id
               JOIN orders o ON oi.order_id = o.id
               JOIN users u ON o.buyer_id = u.id
               WHERE oi.seller_id = '$seller_id'
               ORDER BY o.created_at DESC";

$orders_result = $conn->query($orders_sql);

include('includes/header.php');
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-200 pb-5 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Received Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Track and manage orders placed by customers for your products.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="seller_dashboard.php" class="text-sm font-semibold text-blue-600 hover:underline">&larr; Back to Dashboard</a>
        </div>
    </div>

    <?php if(!empty($message)): ?>
        <div class="p-4 mb-6 rounded-lg text-sm font-medium <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <th class="px-6 py-4">Order ID & Date</th>
                            <th class="px-6 py-4">Product Details</th>
                            <th class="px-6 py-4">Customer & Shipping</th>
                            <th class="px-6 py-4">Total Earnings</th>
                            <th class="px-6 py-4">Status & Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        <?php while($order = $orders_result->fetch_assoc()): 
                            $total_earning = $order['item_price'] * $order['item_qty'];
                        ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-900 block">#AERO-<?php echo $order['order_id']; ?></span>
                                    <span class="text-xs text-gray-400 block mt-1"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <img src="<?php echo htmlspecialchars($order['prod_image']); ?>" class="w-12 h-12 rounded-lg object-cover border">
                                        <div>
                                            <span class="font-semibold text-gray-900 block"><?php echo htmlspecialchars($order['prod_title']); ?></span>
                                            <span class="text-xs text-gray-500">Qty: <?php echo $order['item_qty']; ?> &times; PKR <?php echo number_format($order['item_price']); ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900 block"><?php echo htmlspecialchars($order['buyer_name']); ?></span>
                                    <span class="text-xs text-gray-500 block"><?php echo htmlspecialchars($order['phone_number']); ?></span>
                                    <span class="text-xs text-gray-400 block max-w-xs truncate" title="<?php echo htmlspecialchars($order['shipping_address']); ?>">
                                        <?php echo htmlspecialchars($order['shipping_address']); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 font-bold text-gray-900">
                                    PKR <?php echo number_format($total_earning, 2); ?>
                                </td>

                                <td class="px-6 py-4">
                                    <form action="seller_orders.php" method="POST" class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        
                                        <select name="status" class="px-2 py-1 text-xs font-semibold rounded-md border bg-white cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processed" <?php echo $order['status'] == 'processed' ? 'selected' : ''; ?>>Processed</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        
                                        <button type="submit" name="update_status" class="bg-blue-600 text-white text-xs font-medium px-2 py-1 rounded hover:bg-blue-700 transition">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-12 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-base font-medium">No orders received yet.</p>
                    <p class="text-sm text-gray-400 mt-1">When customers buy your products, they will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>