<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete the remember me cookie if it exists
if (isset($_COOKIE['remembered_email'])) {
    setcookie('remembered_email', '', time() - 3600, '/');
}

// Redirect to login page
header("Location: index.php");
exit();
?>