<?php
session_start();
session_unset(); // Saare session variables khali karna
session_destroy(); // Session file delete karna

// Home page par wapas redirect karna
header("Location: index.php");
exit();
?>