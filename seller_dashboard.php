<?php
include('config.php');
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$seller_name = $_SESSION['user_name'];
$message = "";
$messageClass = "";

// --- DELETE PRODUCT ACTION ---
if (isset($_GET['delete_product_id'])) {
    $del_id = intval($_GET['delete_product_id']);
    // Check taake koi doosra seller kisi aur ki product delete na kar sake
    $sql_del = "DELETE FROM products WHERE id = $del_id AND seller_id = $seller_id";
    if ($conn->query($sql_del) === TRUE) {
        $message = "Product successfully deleted!";
        $messageClass = "text-green-600 bg-green-50";
    } else {
        $message = "Error deleting product: " . $conn->error;
        $messageClass = "text-red-600 bg-red-50";
    }
}

// --- DELETE REVIEW ACTION ---
if (isset($_GET['delete_review_id'])) {
    $del_rev_id = intval($_GET['delete_review_id']);
    // Sirf wahi review delete ho jo is seller ki kisi product par ho
    $sql_rev_del = "DELETE r FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.id = $del_rev_id AND p.seller_id = $seller_id";
    if ($conn->query($sql_rev_del) === TRUE) {
        $message = "Review successfully deleted!";
        $messageClass = "text-green-600 bg-green-50";
    } else {
        $message = "Error deleting review: " . $conn->error;
        $messageClass = "text-red-600 bg-red-50";
    }
}

// --- PRODUCT ADD LOGIC ---
if (isset($_POST['add_product'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']); 
    $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0; 
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $new_file_name = time() . '_' . uniqid() . '.' . $file_extension; 
    $target_file = $target_dir . $new_file_name;
    
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO products (seller_id, title, description, category, price, is_flash_sale, image, stock) 
                    VALUES ('$seller_id', '$title', '$description', '$category', '$price', '$is_flash_sale', '$target_file', '$stock')";
            
            if ($conn->query($sql) === TRUE) {
                $message = "Product successfully listed!";
                $messageClass = "text-green-600 bg-green-50";
            } else {
                $message = "Database Error: " . $conn->error;
                $messageClass = "text-red-600 bg-red-50";
            }
        } else {
            $message = "Image upload failed.";
            $messageClass = "text-red-600 bg-red-50";
        }
    } else {
        $message = "File is not an image.";
        $messageClass = "text-red-600 bg-red-50";
    }
}

// Fetch seller products
$products_sql = "SELECT * FROM products WHERE seller_id = '$seller_id' ORDER BY id DESC";
$products_result = $conn->query($products_sql);

// Fetch reviews for this seller's products only
$reviews_sql = "SELECT r.*, p.title AS product_title, u.name AS buyer_name 
                FROM reviews r 
                JOIN products p ON r.product_id = p.id 
                JOIN users u ON r.buyer_id = u.id 
                WHERE p.seller_id = '$seller_id' 
                ORDER BY r.id DESC";
$reviews_result = $conn->query($reviews_sql);
$unread_msg_res = $conn->query("SELECT COUNT(*) as unread FROM messages WHERE receiver_id = $seller_id AND is_read = 0");
$unread_msg_row = $unread_msg_res->fetch_assoc();
$unread_count = $unread_msg_row['unread'];

include('includes/header.php'); 
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-200 pb-5 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-gray-900">Seller Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back, <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($seller_name); ?></span>.</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-2">
        <a href="messages.php" class="relative inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            Open Messages Chat 💬
            <?php if($unread_count > 0): ?>
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-black rounded-full h-5 w-5 flex items-center justify-center animate-bounce shadow-md border-2 border-white">
                    <?php echo $unread_count; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="seller_orders.php" class="inline-flex items-center bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
            View Received Orders
        </a>
    </div>
</div>
    <?php if(!empty($message)): ?>
        <div class="p-4 mb-6 rounded-lg text-sm font-medium <?php echo $messageClass; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Add New Product</h2>
            <form action="seller_dashboard.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product Title</label>
                    <input name="title" type="text" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="Electronics">Electronics</option>
                        <option value="Clothing">Clothing & Fashion</option>
                        <option value="Home & Garden">Home & Garden</option>
                        <option value="Sports">Sports & Outdoors</option>
                        <option value="Health & Beauty">Health & Beauty</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="2" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price (PKR)</label>
                        <input name="price" type="number" step="0.01" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock</label>
                        <input name="stock" type="number" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center space-x-2 bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                    <input type="checkbox" name="is_flash_sale" id="flash" value="1" class="w-4 h-4 text-yellow-600 rounded cursor-pointer">
                    <label for="flash" class="text-sm font-bold text-yellow-800 cursor-pointer">Mark as Flash Sale Product ⚡</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product Image</label>
                    <input name="image" type="file" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm cursor-pointer">
                </div>
                <button type="submit" name="add_product" class="w-full py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition">
                    Publish Product
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900">Your Active Listings</h2>
            </div>
            <div class="overflow-x-auto">
                <?php if ($products_result->num_rows > 0): ?>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Price & Stock</th>
                                <th class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php while($product = $products_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 flex items-center space-x-3">
                                        <img src="<?php echo $product['image']; ?>" class="w-12 h-12 rounded-lg object-cover border shadow-sm">
                                        <div>
                                            <span class="font-semibold text-gray-900 block"><?php echo htmlspecialchars($product['title']); ?></span>
                                            <?php if($product['is_flash_sale']): ?>
                                                <span class="text-[10px] font-bold bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">Flash Sale ⚡</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900 block">PKR <?php echo number_format($product['price']); ?></span>
                                        <span class="text-xs text-gray-500">Qty: <?php echo $product['stock']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center space-x-3">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="text-blue-600 font-bold hover:underline">Edit</a>
                                        <a href="seller_dashboard.php?delete_product_id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="text-red-600 font-bold hover:underline">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-8 text-center text-gray-500">No products listed yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-xl font-bold text-gray-900">Manage Customer Reviews on Your Products</h2>
        </div>
        <div class="overflow-x-auto">
            <?php if ($reviews_result->num_rows > 0): ?>
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <th class="px-6 py-3">Product Name</th>
                            <th class="px-6 py-3">Buyer</th>
                            <th class="px-6 py-3">Rating</th>
                            <th class="px-6 py-3">Review Text</th>
                            <th class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while($rev = $reviews_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900"><?php echo htmlspecialchars($rev['product_title']); ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($rev['buyer_name']); ?></td>
                                <td class="px-6 py-4 text-yellow-500 font-bold">★ <?php echo $rev['rating']; ?>/5</td>
                                <td class="px-6 py-4 text-gray-500 max-w-xs truncate"><?php echo htmlspecialchars($rev['review_text']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <a href="seller_dashboard.php?delete_review_id=<?php echo $rev['id']; ?>" onclick="return confirm('Delete this buyer review from your product?')" class="text-red-600 font-bold hover:underline">Delete Review</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-8 text-center text-gray-500">No customer reviews on your products yet.</div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>