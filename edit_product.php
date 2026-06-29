<?php
include('config.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$message = "";
$messageClass = "";

if (!isset($_GET['id'])) {
    header("Location: seller_dashboard.php");
    exit();
}

$product_id = intval($_GET['id']);

// Fetch active product data first to prefill form
$fetch_sql = "SELECT * FROM products WHERE id = $product_id AND seller_id = $seller_id";
$res = $conn->query($fetch_sql);
if($res && $res->num_rows > 0) {
    $product = $res->fetch_assoc();
} else {
    echo "<h2>Product not found or unauthorized access!</h2>";
    exit();
}

// --- UPDATE PRODUCT LOGIC ---
if (isset($_POST['update_product'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0;
    
    $image_path = $product['image']; // Default keep old image
    
    // Check if new image is uploaded
    if($_FILES['image']['size'] > 0) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_file_name = time() . '_' . uniqid() . '.' . $file_extension; 
        $target_file = $target_dir . $new_file_name;
        
        if(getimagesize($_FILES["image"]["tmp_name"]) !== false) {
            if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }
    }
    
    $update_sql = "UPDATE products SET title='$title', description='$description', category='$category', price='$price', stock='$stock', is_flash_sale='$is_flash_sale', image='$image_path' WHERE id=$product_id AND seller_id=$seller_id";
    
    if ($conn->query($update_sql) === TRUE) {
        header("Location: seller_dashboard.php");
        exit();
    } else {
        $message = "Update failed: " . $conn->error;
        $messageClass = "text-red-600 bg-red-50";
    }
}

include('includes/header.php');
?>

<main class="max-w-3xl mx-auto px-4 py-12">
    <div class="bg-white p-8 rounded-2xl shadow-md border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-black text-gray-900">Edit Product Details</h1>
            <a href="seller_dashboard.php" class="text-sm font-semibold text-blue-600 hover:underline">&larr; Back to Dashboard</a>
        </div>

        <?php if(!empty($message)): ?>
            <div class="p-4 mb-6 rounded-lg text-sm font-medium <?php echo $messageClass; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700">Product Title</label>
                <input name="title" type="text" value="<?php echo htmlspecialchars($product['title']); ?>" required class="mt-1 w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" class="mt-1 w-full px-3 py-2 border rounded-lg text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="Electronics" <?php if($product['category'] == 'Electronics') echo 'selected'; ?>>Electronics</option>
                    <option value="Clothing" <?php if($product['category'] == 'Clothing') echo 'selected'; ?>>Clothing & Fashion</option>
                    <option value="Home & Garden" <?php if($product['category'] == 'Home & Garden') echo 'selected'; ?>>Home & Garden</option>
                    <option value="Sports" <?php if($product['category'] == 'Sports') echo 'selected'; ?>>Sports & Outdoors</option>
                    <option value="Health & Beauty" <?php if($product['category'] == 'Health & Beauty') echo 'selected'; ?>>Health & Beauty</option>
                    <option value="Other" <?php if($product['category'] == 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="4" class="mt-1 w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price (PKR)</label>
                    <input name="price" type="number" step="0.01" value="<?php echo $product['price']; ?>" required class="mt-1 w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock</label>
                    <input name="stock" type="number" value="<?php echo $product['stock']; ?>" required class="mt-1 w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex items-center space-x-2 bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                <input type="checkbox" name="is_flash_sale" id="edit_flash" value="1" <?php if($product['is_flash_sale'] == 1) echo 'checked'; ?> class="w-4 h-4 text-yellow-600 rounded cursor-pointer">
                <label for="edit_flash" class="text-sm font-bold text-yellow-800 cursor-pointer">Mark as Flash Sale Product ⚡</label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Image (Leave empty to keep current image)</label>
                <div class="flex items-center space-x-4">
                    <img src="<?php echo $product['image']; ?>" class="w-16 h-16 object-cover rounded border shadow-sm">
                    <input name="image" type="file" class="w-full text-sm">
                </div>
            </div>

            <button type="submit" name="update_product" class="w-full py-3 px-4 text-sm font-bold rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition shadow-md">
                Save and Update Product
            </button>
        </form>
    </div>
</main>

<?php include('includes/footer.php'); ?>