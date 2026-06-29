<?php 
include('config.php');
include('includes/header.php'); 

// Filters handle karna (Search & Category)
$search_condition = "WHERE 1=1"; // Default condition

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = $conn->real_escape_string($_GET['search']);
    $search_condition .= " AND (p.title LIKE '%$search%' OR p.description LIKE '%$search%')";
}

if (isset($_GET['category']) && !empty(trim($_GET['category']))) {
    $category = $conn->real_escape_string($_GET['category']);
    $search_condition .= " AND p.category = '$category'";
}

// Database se normal products nikalna
$sql = "SELECT p.*, u.name AS seller_name FROM products p JOIN users u ON p.seller_id = u.id $search_condition ORDER BY p.id DESC";
$result = $conn->query($sql);

// Agar koi filter nahi laga toh Flash Sale products nikalna
$show_flash_sale = (!isset($_GET['search']) && !isset($_GET['category']));
if ($show_flash_sale) {
    $flash_sql = "SELECT p.*, u.name AS seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.is_flash_sale = 1 ORDER BY p.id DESC LIMIT 4";
    $flash_result = $conn->query($flash_sql);
}
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Hero Banner -->
    <?php if($show_flash_sale): ?>
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 md:p-12 text-white mb-8 shadow-lg">
        <h1 class="text-2xl md:text-5xl font-extrabold mb-4">Welcome to AeroCart</h1>
        <p class="text-sm md:text-lg text-blue-100 mb-6">Discover premium products from trusted local and global sellers.</p>
        <a href="#products-grid" class="inline-block bg-white text-blue-600 px-5 py-2.5 rounded-lg font-semibold hover:bg-gray-100 transition shadow-md text-sm">Shop Now</a>
    </div>
    <?php endif; ?>

    <!-- Category Filters -->
<div class="mb-10 overflow-x-auto pb-2">
    <div class="flex space-x-3 min-w-max">
        <a href="index.php" class="<?php echo !isset($_GET['category']) ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-200'; ?> px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800 hover:text-white transition shadow-sm">All</a>
        <a href="index.php?category=Electronics" class="<?php echo (isset($_GET['category']) && $_GET['category']=='Electronics') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-200'; ?> px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800 hover:text-white transition shadow-sm">Electronics</a>
        <a href="index.php?category=Clothing" class="<?php echo (isset($_GET['category']) && $_GET['category']=='Clothing') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-200'; ?> px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800 hover:text-white transition shadow-sm">Clothing</a>
        <a href="index.php?category=Home & Garden" class="<?php echo (isset($_GET['category']) && $_GET['category']=='Home & Garden') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-200'; ?> px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800 hover:text-white transition shadow-sm">Home & Garden</a>
        <a href="index.php?category=Sports" class="<?php echo (isset($_GET['category']) && $_GET['category']=='Sports') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-200'; ?> px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800 hover:text-white transition shadow-sm">Sports</a>
        <!-- ADDED HEALTH & BEAUTY HERE -->
        <a href="index.php?category=Health %26 Beauty" class="<?php echo (isset($_GET['category']) && $_GET['category']=='Health & Beauty') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-200'; ?> px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800 hover:text-white transition shadow-sm">Health & Beauty</a>
    </div>
</div>
    <!-- FLASH SALE SECTION (Sirf Home Page par dikhega) -->
    <?php if($show_flash_sale && $flash_result && $flash_result->num_rows > 0): ?>
    <div class="mb-12 bg-yellow-50 rounded-2xl p-6 border border-yellow-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
        
        <div class="flex items-center mb-6 space-x-2">
            <span class="text-2xl">⚡</span>
            <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-yellow-600">Flash Sale</h2>
            <span class="text-sm font-medium text-red-600 bg-red-100 px-3 py-1 rounded-full ml-4 animate-pulse">Ending Soon!</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 relative z-10">
            <?php while($flash = $flash_result->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-sm border-2 border-yellow-300 overflow-hidden hover:shadow-md transition flex flex-col justify-between transform hover:-translate-y-1">
                    <div>
                        <a href="product_details.php?id=<?php echo $flash['id']; ?>" class="w-full h-40 bg-white flex items-center justify-center overflow-hidden border-b border-gray-100 p-2 group">
                            <img src="<?php echo htmlspecialchars($flash['image']); ?>" class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-300">
                        </a>
                        <div class="p-4 pb-2">
                            <h3 class="text-sm font-bold text-gray-900 truncate">
                                <a href="product_details.php?id=<?php echo $flash['id']; ?>"><?php echo htmlspecialchars($flash['title']); ?></a>
                            </h3>
                        </div>
                    </div>
                    <div class="p-4 pt-0">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg font-extrabold text-red-600">PKR <?php echo number_format($flash['price']); ?></span>
                        </div>
                        <a href="product_details.php?id=<?php echo $flash['id']; ?>" class="block text-center bg-yellow-400 text-gray-900 py-2 rounded-lg text-xs font-bold hover:bg-yellow-500 transition">Grab Deal</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Product Grid Heading -->
    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
        <?php 
            if(isset($_GET['search'])) { echo 'Search Results for "'.htmlspecialchars($_GET['search']).'"'; }
            elseif(isset($_GET['category'])) { echo htmlspecialchars($_GET['category']) . ' Products'; }
            else { echo 'Just For You'; }
        ?>
    </h2>

    <!-- ALL PRODUCTS GRID -->
    <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($product = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="w-full h-48 bg-gray-50 flex items-center justify-center overflow-hidden border-b border-gray-100 p-2 cursor-pointer group relative">
                            <!-- Category Badge on Image -->
                            <span class="absolute top-2 left-2 text-[10px] font-bold text-gray-600 bg-white px-2 py-1 rounded shadow-sm opacity-80"><?php echo htmlspecialchars($product['category']); ?></span>
                            
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-300">
                        </a>
                        
                        <div class="p-4">
                            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-md">AeroCart Verified</span>
                            <h3 class="text-base font-bold text-gray-900 mt-2 truncate">
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="hover:text-blue-600">
                                    <?php echo htmlspecialchars($product['title']); ?>
                                </a>
                            </h3>
                            <p class="text-gray-400 text-xs mt-0.5 truncate"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="text-gray-500 text-[11px] mt-1">Sold by: <span class="font-medium text-gray-700"><?php echo htmlspecialchars($product['seller_name']); ?></span></p>
                        </div>
                    </div>

                    <div class="p-4 pt-0">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg font-extrabold text-gray-900">PKR <?php echo number_format($product['price']); ?></span>
                            <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-1 rounded">Stock: <?php echo $product['stock']; ?></span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-center bg-blue-600 text-white py-2 rounded-lg text-xs font-semibold hover:bg-blue-700 transition flex items-center justify-center">View</a>
                            <button class="add-to-cart-btn bg-gray-900 text-white py-2 rounded-lg text-xs font-semibold hover:bg-blue-600 transition" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full bg-white p-12 rounded-xl border text-center text-gray-500 shadow-sm">
                <p class="text-lg font-semibold text-gray-800">No products found!</p>
                <a href="index.php" class="text-blue-600 hover:underline mt-2 inline-block font-medium text-sm">Clear Filters</a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include('includes/footer.php'); ?>