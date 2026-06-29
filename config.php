<?php
$host = "localhost";
$user = "root";       // Agar aap XAMPP use kar rahe ho to default root hota hai
$pass = "";           // XAMPP me password default khali (empty) hota hai
$dbname = "aerocart_db";

// Connection banana
$conn = new mysqli($host, $user, $pass, $dbname);

// Connection check karna
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>