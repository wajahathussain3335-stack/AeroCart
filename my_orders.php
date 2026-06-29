<?php
include('config.php');
session_start();

// Security Check: Agar buyer logged in nahi hai to login par bhejo
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

// Buyer ke orders aur unke andar ke items fetch karne ki SQL Query
$sql = "SELECT o.id AS order_id, o.total_amount, o.status, o.created_at, o.shipping_address,
               oi.quantity, oi.price AS item_price, p.title AS prod_title, p.image AS prod_image
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.buyer_id = '$buyer_id'
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);

// Items ko orders ke mutabiq group karne ke liye array structure
$orders = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $oid = $row['order_id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = array(
                'total_amount' => $row['total_amount'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'shipping_address' => $row['shipping_address'],
                'items' => array()
            );
        }
        $orders[$oid]['items'][] = array(
            'title' => $row['prod_title'],
            'image' => $row['prod_image'],
            'quantity' => $row['quantity'],
            'price' => $row['item_price']
        );
    }
}

include('includes/header.php');
?>

<main class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Purchase History</h1>

    <?php if (!empty($orders)): ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order_id => $details): 
                // Status Color Conditions
                $statusColor = "bg-gray-100 text-gray-700";
                if ($details['status'] == 'pending') $statusColor = "bg-yellow-50 text-yellow-700 border-yellow-200";
                if ($details['status'] == 'processed') $statusColor = "bg-blue-50 text-blue-700 border-blue-200";
                if ($details['status'] == 'shipped') $statusColor = "bg-purple-50 text-purple-700 border-purple-200";
                if ($details['status'] == 'delivered') $statusColor = "bg-green-50 text-green-700 border-green-200";
                if ($details['status'] == 'cancelled') $statusColor = "bg-red-50 text-red-700 border-red-200";
            ?>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4 text-sm">
                        <div>
                            <span class="text-gray-400 block">Order Reference</span>
                            <span class="font-bold text-gray-900">#AERO-<?php echo $order_id; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Date Placed</span>
                            <span class="font-medium text-gray-700"><?php echo date('d M Y', strtotime($details['created_at'])); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Total Paid</span>
                            <span class="font-black text-gray-900 text-base">PKR <?php echo number_format($details['total_amount']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-1">Status</span>
                            <span class="px-3 py-1 text-xs font-bold rounded-full border <?php echo $statusColor; ?> uppercase tracking-wider">
                                <?php echo $details['status']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-6 divide-y divide-gray-100">
                        <?php foreach ($details['items'] as $item): ?>
                            <div class="flex items-center justify-between py-4 first:pt-0 last:pb-0">
                                <div class="flex items-center space-x-4">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" class="w-14 h-14 object-cover rounded-lg border">
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($item['title']); ?></h4>
                                        <p class="text-xs text-gray-500 mt-0.5">Qty: <?php echo $item['quantity']; ?> &times; PKR <?php echo number_format($item['price']); ?></p>
                                    </div>
                                </div>
                                <span class="font-semibold text-gray-900 text-sm">PKR <?php echo number_format($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white p-12 border rounded-xl text-center text-gray-500 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="text-lg font-semibold text-gray-800">You haven't ordered anything yet!</p>
            <a href="index.php" class="text-blue-600 hover:underline mt-2 inline-block font-medium text-sm">Explore Products &rarr;</a>
        </div>
    <?php endif; ?>
</main>

<?php include('includes/footer.php'); ?>