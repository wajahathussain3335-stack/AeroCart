<?php
include('config.php');
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // 'buyer' or 'seller'

    if (!empty($name) && !empty($email) && !empty($password) && !empty($role)) {
        
        // 1. Check if email already exists using PREPARED STATEMENT
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "This email address is already registered!";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // 2. SECURE PASSWORD HASHING (Blowfish / Argon2 standard)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // 3. Insert secure record into database using PREPARED STATEMENT
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            
            if ($insert_stmt->execute()) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Database Error execution failure. Please try again.";
            }
            $insert_stmt->close();
        }
    } else {
        $error = "All fields are strictly required!";
    }
}
include('includes/header.php');
?>

<main class="max-w-md mx-auto my-16 p-6 bg-white border rounded-2xl shadow-sm">
    <h2 class="text-2xl font-black text-gray-900 mb-6 text-center">Create an Account</h2>
    
    <?php if(!empty($error)): ?>
        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm font-medium mb-4"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div class="bg-green-50 text-green-600 p-3 rounded-lg text-sm font-medium mb-4"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" required class="mt-1 w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" name="email" required class="mt-1 w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" required class="mt-1 w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Select Account Type</label>
            <select name="role" required class="mt-1 w-full p-2 border rounded-lg text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="buyer">Buyer (Standard Customer)</option>
                <option selected value="seller">Seller (Store Merchant)</option>
            </select>
        </div>
        <button type="submit" name="register" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition shadow-sm">
            Sign Up
        </button>
    </form>
    <p class="text-xs text-center text-gray-500 mt-4">Already have an account? <a href="login.php" class="text-blue-600 font-semibold hover:underline">Log in here</a></p>
</main>

<?php include('includes/footer.php'); ?>