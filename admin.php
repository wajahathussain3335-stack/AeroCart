<?php
include('config.php');
session_start();

// Admin credentials defined safely
define('ADMIN_EMAIL', 'wajahat.awan@gmail.com');
define('ADMIN_PASS', 'Kikar@Beri@7821');
define('ADMIN_NAME', 'Wajahat Awan');

// --- ADMIN LOGIN ACTION ---
if (isset($_POST['admin_login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === ADMIN_EMAIL && $password === ADMIN_PASS) {
        $_SESSION['super_admin_logged_in'] = true;
        $_SESSION['super_admin_name'] = ADMIN_NAME;
        header("Location: admin.php");
        exit();
    } else {
        $login_error = "Access Denied: Invalid Master Credentials.";
    }
}

// --- ADMIN LOGOUT ACTION ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['super_admin_logged_in']);
    unset($_SESSION['super_admin_name']);
    header("Location: admin.php");
    exit();
}

// Check if Admin is authenticated for actions
$is_admin = isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true;

// ==========================================
// GOD-MODE DELETION LOGIC (PREPARED STATEMENTS)
// ==========================================
if ($is_admin && isset($_GET['delete_target']) && isset($_GET['id'])) {
    $target = $_GET['delete_target'];
    $target_id = intval($_GET['id']);

    if ($target === 'user') {
        // Safe cascading: Delete user's messages, reviews, products first to avoid relational breakdown
        $conn->query("DELETE FROM messages WHERE sender_id = $target_id OR receiver_id = $target_id");
        $conn->query("DELETE FROM reviews WHERE buyer_id = $target_id");
        $conn->query("DELETE FROM questions WHERE user_id = $target_id");
        
        // Delete products owned by this user if they are a seller
        $conn->query("DELETE FROM products WHERE seller_id = $target_id");
        
        // Final user execution
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $target_id);
        $stmt->execute();
        $stmt->close();
    } 
    
    elseif ($target === 'product') {
        // Delete linked records first
        $conn->query("DELETE FROM reviews WHERE product_id = $target_id");
        $conn->query("DELETE FROM questions WHERE product_id = $target_id");
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $target_id);
        $stmt->execute();
        $stmt->close();
    } 
    
    elseif ($target === 'review') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $target_id);
        $stmt->execute();
        $stmt->close();
    } 
    
    elseif ($target === 'question') {
        // Also wipe answers associated with this question ID if table exists
        $conn->query("DELETE FROM answers WHERE question_id = $target_id");
        
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->bind_param("i", $target_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin.php?status=success");
    exit();
}

// Fetch lists for the control panel view if logged in
if ($is_admin) {
    $users_list = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
    $products_list = $conn->query("SELECT p.id, p.title, p.price, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.id ORDER BY p.id DESC");
    // Corrected Reviews Query with buyer_id
    $reviews_list = $conn->query("SELECT r.id, r.review_text, r.rating, u.name as user_name, p.title as product_title FROM reviews r JOIN users u ON r.buyer_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.id DESC");
    $reviews_error = !$reviews_list ? $conn->error : '';
    $questions_list = $conn->query("SELECT q.id, q.question_text, u.name as user_name, p.title as product_title FROM questions q JOIN users u ON q.user_id = u.id JOIN products p ON q.product_id = p.id ORDER BY q.id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Control Panel - Hidden Administrative Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans min-h-screen">

<?php if (!$is_admin): ?>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="max-w-md w-full bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700">
            <div class="text-center mb-6">
                <span class="text-4xl">🔒</span>
                <h1 class="text-2xl font-black mt-2 text-white">System Core Auth</h1>
                <p class="text-xs text-gray-400 mt-1">This terminal screen is restricted to authorized master accounts only.</p>
            </div>

            <?php if (isset($login_error)): ?>
                <div class="bg-red-900/50 border border-red-500 text-red-200 p-3 rounded-lg text-xs font-semibold mb-4 text-center">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>

            <form action="admin.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Root Email Identity</label>
                    <input type="email" name="email" required class="mt-1 w-full p-3 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">System Master Key</label>
                    <input type="password" name="password" required class="mt-1 w-full p-3 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500 transition">
                </div>
                <button type="submit" name="admin_login" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition tracking-wide text-sm shadow-lg">
                    Execute Verification
                </button>
            </form>
        </div>
    </div>

<?php else: ?>
    <header class="bg-gray-800 border-b border-gray-700 p-4 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="bg-red-600 text-white text-xs font-black px-2.5 py-1 rounded">ROOT</span>
                <h1 class="text-lg font-bold tracking-tight">Welcome, Administrator: <span class="text-red-400"><?php echo htmlspecialchars($_SESSION['super_admin_name']); ?></span></h1>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="text-sm bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg font-medium transition">View Main Website</a>
                <a href="admin.php?action=logout" class="text-sm bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg font-bold transition shadow">Terminate Session</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6 space-y-12">
        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-green-900/40 border border-green-500 text-green-200 p-4 rounded-xl text-sm font-semibold mb-6">
                ✔ Operation Completed Successfully. Target node terminated from system.
            </div>
        <?php endif; ?>

        <section class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-xl">
            <div class="p-4 bg-gray-750 border-b border-gray-700 flex justify-between items-center">
                <h2 class="text-base font-black uppercase text-gray-300 tracking-wider">👥 User & Seller Registration Directory</h2>
                <span class="text-xs bg-gray-900 px-2.5 py-1 rounded text-gray-400 font-mono"><?php echo $users_list->num_rows; ?> accounts</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="p-4">ID</th>
                            <th class="p-4">Name</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">System Role</th>
                            <th class="p-4 text-center">Action Permission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php while($u = $users_list->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-750 transition">
                                <td class="p-4 font-mono font-bold text-gray-500"><?php echo $u['id']; ?></td>
                                <td class="p-4 font-semibold text-white"><?php echo htmlspecialchars($u['name']); ?></td>
                                <td class="p-4 text-gray-300"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase <?php echo $u['role'] === 'seller' ? 'bg-blue-900/60 text-blue-300 border border-blue-700' : 'bg-green-900/60 text-green-300 border border-green-700'; ?>">
                                        <?php echo $u['role']; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="admin.php?delete_target=user&id=<?php echo $u['id']; ?>" onclick="return confirm('CRITICAL WARNING: Deleting this user will completely remove their profile, messages, products, and comments. Proceed?')" class="text-xs bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-1.5 rounded transition">
                                        Wipe Account
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-xl">
            <div class="p-4 bg-gray-750 border-b border-gray-700 flex justify-between items-center">
                <h2 class="text-base font-black uppercase text-gray-300 tracking-wider">📦 Global Marketplace Products Inventory</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="p-4">PID</th>
                            <th class="p-4">Title</th>
                            <th class="p-4">Price Value</th>
                            <th class="p-4">Merchant Node</th>
                            <th class="p-4 text-center">Action Permission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php while($p = $products_list->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-750 transition">
                                <td class="p-4 font-mono font-bold text-gray-500"><?php echo $p['id']; ?></td>
                                <td class="p-4 font-semibold text-white max-w-xs truncate"><?php echo htmlspecialchars($p['title']); ?></td>
                                <td class="p-4 text-yellow-400 font-bold">$<?php echo number_format($p['price'], 2); ?></td>
                                <td class="p-4 text-gray-400"><?php echo htmlspecialchars($p['seller_name']); ?></td>
                                <td class="p-4 text-center">
                                    <a href="admin.php?delete_target=product&id=<?php echo $p['id']; ?>" onclick="return confirm('Delete this product and all associated ratings/logs?')" class="text-xs bg-red-600/80 hover:bg-red-700 text-white font-bold px-3 py-1.5 rounded transition">
                                        Purge Product
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

       <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <section class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-xl">
                <div class="p-4 bg-gray-750 border-b border-gray-700">
                    <h2 class="text-base font-black uppercase text-gray-300 tracking-wider">⭐ Moderation: Product Reviews</h2>
                </div>
                <div class="p-4 space-y-4 max-h-[400px] overflow-y-auto">
                    <?php if($reviews_list && $reviews_list->num_rows > 0): ?>
                        <?php while($r = $reviews_list->fetch_assoc()): ?>
                            <div class="bg-gray-900 p-3 rounded-lg border border-gray-700 flex justify-between items-start gap-2">
                                <div class="text-xs space-y-1">
                                    <p class="text-gray-400 font-bold"><span class="text-white"><?php echo htmlspecialchars($r['user_name']); ?></span> on <span class="text-blue-400"><?php echo htmlspecialchars($r['product_title']); ?></span></p>
                                    <p class="text-yellow-400 font-bold">Rating: <?php echo $r['rating']; ?>/5</p>
                                    <p class="text-gray-300 italic">"<?php echo htmlspecialchars($r['review_text']); ?>"</p>
                                </div>
                                <a href="admin.php?delete_target=review&id=<?php echo $r['id']; ?>" onclick="return confirm('Delete this review item?')" class="text-[10px] bg-red-600 text-white px-2 py-1 rounded font-bold hover:bg-red-700 transition">Delete</a>
                            </div>
                        <?php endwhile; ?>
                    <?php elseif(!$reviews_list): ?>
                        <div class="bg-red-900/30 border border-red-700 p-3 rounded-lg text-xs text-red-300">
                            <strong>SQL Error:</strong> <?php echo $conn->error; ?> <br>
                            <span class="text-gray-400">Tip: Check if column names match your DB.</span>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-xs text-gray-500 py-10">No platform reviews logged yet.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-xl">
                <div class="p-4 bg-gray-750 border-b border-gray-700">
                    <h2 class="text-base font-black uppercase text-gray-300 tracking-wider">❓ Moderation: Public Inquiries & Questions</h2>
                </div>
                <div class="p-4 space-y-4 max-h-[400px] overflow-y-auto">
                    <?php if($questions_list && $questions_list->num_rows > 0): ?>
                        <?php while($q = $questions_list->fetch_assoc()): ?>
                            <div class="bg-gray-900 p-3 rounded-lg border border-gray-700 flex justify-between items-start gap-2">
                                <div class="text-xs space-y-1">
                                    <p class="text-gray-400 font-bold"><span class="text-white"><?php echo htmlspecialchars($q['user_name']); ?></span> asks on <span class="text-blue-400"><?php echo htmlspecialchars($q['product_title']); ?></span></p>
                                    <p class="text-gray-200">Q: <?php echo htmlspecialchars($q['question_text']); ?></p>
                                </div>
                                <a href="admin.php?delete_target=question&id=<?php echo $q['id']; ?>" onclick="return confirm('Delete this question node entirely?')" class="text-[10px] bg-red-600 text-white px-2 py-1 rounded font-bold hover:bg-red-700 transition">Delete</a>
                            </div>
                        <?php endwhile; ?>
                    <?php elseif(!$questions_list): ?>
                        <div class="bg-red-900/30 border border-red-700 p-3 rounded-lg text-xs text-red-300">
                            <strong>SQL Error:</strong> <?php echo $conn->error; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-xs text-gray-500 py-10">No community questions logged yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
<?php endif; ?>

</body>
</html>