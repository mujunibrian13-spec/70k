<?php
/**
 * Debug Password Script
 * Tests password hashing and verification
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

$test_password = 'admin123';

echo "<h2>Testing Password Functions</h2>";

// Test hashing
$hash1 = password_hash($test_password, PASSWORD_BCRYPT);
echo "<p><strong>Hashed password:</strong> " . htmlspecialchars($hash1) . "</p>";

// Test verification
$verify1 = password_verify($test_password, $hash1);
echo "<p><strong>Verify result:</strong> " . ($verify1 ? 'TRUE' : 'FALSE') . "</p>";

// Check database
echo "<h2>Database Admin User</h2>";
$query = "SELECT id, username, password FROM users WHERE username = 'admin' AND role = 'admin' LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p><strong>Found admin user:</strong> " . htmlspecialchars($user['username']) . "</p>";
    echo "<p><strong>Stored hash:</strong> " . htmlspecialchars($user['password']) . "</p>";
    
    // Test verification with stored hash
    $verify_stored = password_verify($test_password, $user['password']);
    echo "<p><strong>Verify with stored hash:</strong> " . ($verify_stored ? 'TRUE' : 'FALSE') . "</p>";
} else {
    echo "<p><strong>No admin user found in database</strong></p>";
}

// Show PHP info
echo "<h2>PHP Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Function exists - password_hash:</strong> " . (function_exists('password_hash') ? 'YES (built-in)' : 'NO (using compatibility)') . "</p>";
echo "<p><strong>Function exists - password_verify:</strong> " . (function_exists('password_verify') ? 'YES (built-in)' : 'NO (using compatibility)') . "</p>";
echo "<p><strong>Function exists - crypt:</strong> " . (function_exists('crypt') ? 'YES' : 'NO') . "</p>";

$conn->close();
?>
