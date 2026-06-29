<?php
include('config.php');
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = intval($_GET['id']);

// --- REVIEW SUBMISSION LOGIC ---
if (isset($_POST['submit_review']) && isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'buyer') {
    $rating = intval($_POST['rating']);
    $review_text = $conn->real_escape_string($_POST['review_text']);
    $buyer_id = $_SESSION['user_id'];
    
    $ins_rev = "INSERT INTO reviews (product_id, buyer_id, rating, review_text) VALUES ($product_id, $buyer_id, $rating, '$review_text')";
    $conn->query($ins_rev);
    header("Location: product_details.php?id=$product_id");
    exit();
}

// --- Q&A HANDLING ---
if (isset($_POST['submit_question']) && isset($_SESSION['user_id'])) {
    $q_text = $conn->real_escape_string($_POST['question_text']);
    $conn->query("INSERT INTO questions (product_id, user_id, question_text) VALUES ($product_id, {$_SESSION['user_id']}, '$q_text')");
    header("Location: product_details.php?id=$product_id"); 
    exit();
}

if (isset($_POST['submit_answer']) && isset($_SESSION['user_id'])) {
    $q_id = intval($_POST['question_id']);
    $a_text = $conn->real_escape_string($_POST['answer_text']);
    $conn->query("INSERT INTO answers (question_id, user_id, answer_text) VALUES ($q_id, {$_SESSION['user_id']}, '$a_text')");
    header("Location: product_details.php?id=$product_id"); 
    exit();
}

// --- FETCH PRODUCT DETAILS ---
$sql = "SELECT p.*, u.name AS seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = $product_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "<h2 class='text-center mt-10'>Product not found!</h2>"; 
    exit();
}

// --- FETCH AVERAGE RATING ---
$rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(id) as total_reviews FROM reviews WHERE product_id = $product_id";
$rating_res = $conn->query($rating_sql);
$rating_data = $rating_res->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'], 1);
$total_reviews = $rating_data['total_reviews'];

// --- FETCH ALL REVIEWS ---
$reviews_result = $conn->query("SELECT r.*, u.name FROM reviews r JOIN users u ON r.buyer_id = u.id WHERE r.product_id = $product_id ORDER BY r.created_at DESC");

// --- FETCH QUESTIONS ---
$questions_res = $conn->query("SELECT q.*, u.name FROM questions q JOIN users u ON q.user_id = u.id WHERE q.product_id = $product_id ORDER BY q.id DESC");

include('includes/header.php');
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <nav class="text-sm text-gray-500 mb-6">
        <a href="index.php" class="hover:text-blue-600">Home</a> &gt; 
        <span class="text-gray-500"><?php echo htmlspecialchars($product['category'] ?? 'General'); ?></span> &gt; 
        <span class="text-gray-800 font-medium"><?php echo htmlspecialchars($product['title']); ?></span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 bg-white p-8 rounded-2xl shadow-sm border border-gray-100 mb-10">
        <div class="flex items-center justify-center bg-gray-50 rounded-xl overflow-hidden p-4 max-h-[400px]">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="max-w-full max-h-full object-contain">
        </div>

        <div class="flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wide text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                        In Stock (<?php echo $product['stock']; ?>)
                    </span>
                    <?php if($product['is_flash_sale']): ?>
                        <span class="text-xs font-bold uppercase tracking-wide text-white bg-yellow-500 px-3 py-1 rounded-full shadow-sm">
                            ⚡ Flash Sale
                        </span>
                    <?php endif; ?>
                </div>
                
                <h1 class="text-3xl font-extrabold text-gray-900 mt-2 tracking-tight">
                    <?php echo htmlspecialchars($product['title']); ?>
                </h1>

                <div class="flex items-center space-x-2 mt-2">
                    <span class="text-yellow-400 text-lg">★</span>
                    <span class="font-bold text-gray-800"><?php echo $total_reviews > 0 ? $avg_rating : 'No ratings yet'; ?></span>
                    <span class="text-sm text-gray-500">(<?php echo $total_reviews; ?> Reviews)</span>
                </div>

                <div class="mt-4 p-3 bg-gray-50 rounded-lg inline-flex flex-col space-y-2 border border-gray-100">
                    <div>
                        <span class="text-xs text-gray-500">Verified Seller:</span>
                        <span class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($product['seller_name']); ?></span>
                    </div>
                    <a href="messages.php?receiver_id=<?php echo $product['seller_id']; ?>" class="bg-blue-100 text-blue-700 text-xs font-bold px-4 py-2 rounded-lg hover:bg-blue-200 transition text-center shadow-sm">
                        Chat with Seller 💬
                    </a>
                </div>

                <div class="mt-6 border-t border-b border-gray-100 py-4">
                    <span class="text-3xl font-black text-gray-900">PKR <?php echo number_format($product['price'], 2); ?></span>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Description</h3>
                    <p class="text-gray-600 text-sm mt-2 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-100 pt-6">
                <?php if ($product['stock'] > 0): ?>
                    <form action="cart_action.php" method="POST" class="flex items-center space-x-4">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="w-24">
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Qty</label>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="w-full border border-gray-300 rounded-lg p-2 text-center text-sm">
                        </div>
                        <div class="flex-1 pt-5">
                            <button type="submit" name="add_to_cart" class="w-full bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 transition shadow-md flex justify-center items-center">
                                Add to Cart
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <button disabled class="w-full bg-gray-300 text-gray-500 font-bold py-3 px-6 rounded-lg cursor-not-allowed">Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Customer Reviews</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-6">
                <?php if($reviews_result->num_rows > 0): ?>
                    <?php while($review = $reviews_result->fetch_assoc()): ?>
                        <div class="border-b border-gray-100 pb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-gray-900"><?php echo htmlspecialchars($review['name']); ?></span>
                                <span class="text-xs text-gray-400"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="text-yellow-400 text-sm mb-2">
                                <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                            </div>
                            <p class="text-gray-600 text-sm"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500">No reviews yet. Be the first to review this product!</p>
                <?php endif; ?>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 h-fit">
                <h3 class="font-bold text-gray-900 mb-4">Write a Review</h3>
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'buyer'): ?>
                    <form action="product_details.php?id=<?php echo $product_id; ?>" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rating (Out of 5)</label>
                            <select name="rating" required class="mt-1 w-full p-2 border border-gray-300 rounded-lg text-sm bg-white">
                                <option value="5">★★★★★ (5/5)</option>
                                <option value="4">★★★★☆ (4/5)</option>
                                <option value="3">★★★☆☆ (3/5)</option>
                                <option value="2">★★☆☆☆ (2/5)</option>
                                <option value="1">★☆☆☆☆ (1/5)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Your Review</label>
                            <textarea name="review_text" rows="3" required placeholder="What did you like or dislike?" class="mt-1 w-full p-2 border border-gray-300 rounded-lg text-sm"></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="w-full bg-gray-900 text-white font-semibold py-2 rounded-lg hover:bg-blue-600 transition text-sm">
                            Submit Review
                        </button>
                    </form>
                <?php elseif(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'seller'): ?>
                    <p class="text-sm text-gray-500">You are logged in as a Seller. Please log in as a Buyer to leave a review.</p>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Please <a href="login.php" class="text-blue-600 underline">Login</a> to write a review.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Questions & Answers</h2>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <form action="product_details.php?id=<?php echo $product_id; ?>" method="POST" class="mb-6 flex gap-2">
                <input type="text" name="question_text" required placeholder="Ask seller a question about this product..." class="flex-1 p-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                <button type="submit" name="submit_question" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition shadow-sm">Ask Question</button>
            </form>
        <?php else: ?>
            <p class="text-sm text-gray-500 mb-6">Please <a href="login.php" class="text-blue-600 underline">Login</a> to ask questions.</p>
        <?php endif; ?>

        <div class="space-y-4">
            <?php if($questions_res->num_rows > 0): ?>
                <?php while($q = $questions_res->fetch_assoc()): ?>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <p class="text-sm font-bold text-gray-900">Q: <?php echo htmlspecialchars($q['question_text']); ?> <span class="text-xs font-normal text-gray-400 ml-2">- by <?php echo htmlspecialchars($q['name']); ?></span></p>
                        
                        <div class="ml-4 mt-2 pl-3 border-l-2 border-blue-500 space-y-1">
                            <?php 
                            $ans_res = $conn->query("SELECT a.*, u.name FROM answers a JOIN users u ON a.user_id = u.id WHERE a.question_id = {$q['id']}");
                            if($ans_res->num_rows > 0):
                                while($a = $ans_res->fetch_assoc()): ?>
                                    <p class="text-sm text-gray-700"><strong>A:</strong> <?php echo htmlspecialchars($a['answer_text']); ?> <span class="text-xs text-gray-400 ml-1">(<?php echo htmlspecialchars($a['name']); ?>)</span></p>
                                <?php endwhile;
                            else: ?>
                                <p class="text-xs text-gray-400 italic">No answers yet.</p>
                            <?php endif; ?>
                        </div>

                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form action="product_details.php?id=<?php echo $product_id; ?>" method="POST" class="mt-3 ml-4 flex gap-2">
                                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                <input type="text" name="answer_text" required placeholder="Type a reply..." class="flex-1 p-1.5 border rounded text-xs focus:outline-none">
                                <button type="submit" name="submit_answer" class="bg-gray-800 text-white px-3 py-1 rounded text-xs hover:bg-gray-900 transition">Reply</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-sm text-gray-500">No questions asked yet about this product.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>