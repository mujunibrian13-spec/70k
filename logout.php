<?php
/**
 * Logout Page
 * Destroys user session and logs out
 */

session_start();

// Destroy session
session_destroy();

// Redirect to login
header("Location: login.php?logout=true");
exit();
?>
