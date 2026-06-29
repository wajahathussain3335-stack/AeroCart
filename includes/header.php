<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once('config.php'); 

// ==========================================
// ⚡ BECOME A SELLER LOGIC (INSTANT UPGRADE)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'become_seller' && isset($_SESSION['user_id'])) {
    $auth_user_id = intval($_SESSION['user_id']);
    
    // Database me user ka role direct update karein
    $upgrade_query = $conn->query("UPDATE users SET role = 'seller' WHERE id = $auth_user_id");
    
    if ($upgrade_query) {
        $_SESSION['user_role'] = 'seller'; // Session role update
        header("Location: seller_dashboard.php?status=welcome_merchant"); // Redirect to dashboard
        exit();
    }
}

// Unread Messages Counter Logic
$header_unread = 0;
if (isset($_SESSION['user_id'])) {
    $header_uid = intval($_SESSION['user_id']);
    $header_unread_res = $conn->query("SELECT COUNT(*) as unread FROM messages WHERE receiver_id = $header_uid AND is_read = 0");
    if($header_unread_res) { $header_unread = $header_unread_res->fetch_assoc()['unread']; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroCart - Premium E-Commerce Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

<div class="bg-gray-900 text-white text-[11px] text-center py-2 font-medium tracking-wide px-4">
    🚀 Welcome to AeroCart: Next-Gen Fast Shipping System!
</div>

<header class="bg-white shadow-sm sticky top-0 z-50 w-full">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-2 sm:gap-4">
        
        <a href="index.php" class="flex items-center gap-1.5 text-lg sm:text-2xl font-black text-blue-600 tracking-tight shrink-0">
            <i class="fa-solid fa-bolt-lightning text-yellow-400"></i> AeroCart.
        </a>

        <form action="index.php" method="GET" class="hidden md:flex flex-1 max-w-xl mx-4">
            <div class="relative w-full">
                <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search products, categories, brands..." class="w-full bg-gray-100 border border-transparent text-gray-900 text-sm rounded-l-lg p-2.5 outline-none focus:bg-white focus:border-blue-500 transition">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-5 rounded-r-lg hover:bg-blue-700 transition flex items-center justify-center">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>

        <div class="flex items-center space-x-2.5 sm:space-x-4 shrink-0">
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_role'] !== 'seller'): ?>
                    <a href="header.php?action=become_seller" onclick="return confirm('Are you sure you want to upgrade your account to a Seller Profile?')" class="text-[11px] sm:text-xs bg-amber-500 hover:bg-amber-600 text-white font-bold py-1.5 px-2.5 rounded-md transition shadow-sm shrink-0">
                        <i class="fa-solid fa-store mr-1"></i> Become a Seller
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="register-as-seller.php" class="hidden lg:inline-block text-xs text-gray-600 hover:text-blue-600 font-medium transition">
                    Want to Sell? Register
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="messages.php" class="relative text-gray-600 hover:text-blue-600 transition p-1">
                    <i class="fa-regular fa-comment-dots text-xl sm:text-2xl"></i>
                    <?php if($header_unread > 0): ?>
                        <span class="absolute top-0 right-0 bg-red-500 text-white text-[9px] font-bold rounded-full h-4 w-4 flex items-center justify-center animate-pulse border-2 border-white">
                            <?php echo $header_unread; ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <div class="flex items-center gap-1.5 sm:gap-2 border-l pl-2 sm:pl-4 border-gray-200">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="text-right hidden sm:block max-w-[100px]">
                        <p class="text-[10px] text-gray-400 truncate">Hi, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></p>
                        <?php if($_SESSION['user_role'] == 'seller'): ?>
                            <a href="seller_dashboard.php" class="text-xs font-bold text-blue-600 hover:underline block">Dashboard</a>
                        <?php endif; ?>
                    </div>
                    
                    <a href="logout.php" title="Logout" class="text-sm text-red-500 hover:text-red-700 p-1 transition">
                        <i class="fa-solid fa-power-off text-base sm:text-lg"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="text-xs sm:text-sm font-bold text-gray-700 hover:text-blue-600 px-1">Log In</a>
                    <a href="register.php" class="bg-blue-600 text-white px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-sm">Sign Up</a>
                <?php endif; ?>
            </div>
            
            <a href="cart.php" class="relative flex items-center text-gray-600 hover:text-blue-600 transition p-1">
                <i class="fa-solid fa-cart-shopping text-xl sm:text-2xl"></i>
                <span id="cart-count" class="absolute top-0 right-0 bg-yellow-400 text-gray-900 text-[9px] font-black rounded-full h-4 w-4 flex items-center justify-center border border-white shadow-xs">0</span>
            </a>
        </div>
    </div>

    <div class="block md:hidden px-3 pb-3.5 pt-1 border-t border-gray-100">
        <form action="index.php" method="GET" class="flex w-full shadow-xs rounded-lg overflow-hidden">
            <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search AeroCart products..." class="w-full bg-gray-100 text-gray-900 text-xs block p-2.5 outline-none transition focus:bg-white border border-transparent focus:border-blue-500 rounded-l-lg">
            <button type="submit" class="bg-blue-600 text-white px-4 hover:bg-blue-700 transition flex items-center justify-center rounded-r-lg">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </button>
        </form>
    </div>
</header>