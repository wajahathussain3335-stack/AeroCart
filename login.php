<?php
include('config.php');
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        
        // 1. Fetch user data using PREPARED STATEMENT to prevent SQL injection injections
        $login_stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $login_stmt->bind_param("s", $email);
        $login_stmt->execute();
        $result = $login_stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 2. CRUCIAL: VERIFY ENCRYPTED HASH MATCH
            if (password_verify($password, $user['password'])) {
                // Initialize global session state tokens safely
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Route according to access permission rules safely
                if ($user['role'] === 'seller') {
                    header("Location: seller_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Invalid password credential validation failure.";
            }
        } else {
            $error = "No active account found matched with this email identity.";
        }
        $login_stmt->close();
    } else {
        $error = "Please fill out all credential input entries.";
    }
}
include('includes/header.php');
?>

<main class="max-w-md mx-auto my-20 p-6 bg-white border rounded-2xl shadow-sm">
    <h2 class="text-2xl font-black text-gray-900 mb-6 text-center">Log In to Marketplace</h2>
    
    <?php if(!empty($error)): ?>
        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm font-medium mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" name="email" required class="mt-1 w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" required class="mt-1 w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <button type="submit" name="login" class="w-full bg-gray-900 text-white font-bold py-2.5 rounded-lg hover:bg-blue-600 transition shadow-sm">
            Access Account
        </button>
    </form>
    <p class="text-xs text-center text-gray-500 mt-4">New to our platform? <a href="register.php" class="text-blue-600 font-semibold hover:underline">Register an account</a></p>
</main>

<?php include('includes/footer.php'); ?>