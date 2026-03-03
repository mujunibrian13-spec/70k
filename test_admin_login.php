<?php
/**
 * Test Admin Login Script
 * Debugs password hashing and verification
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

echo "<h2>Admin Login Debug</h2>";

$test_password = 'admin123';

echo "<h3>Step 1: Test Password Hashing</h3>";
$hash1 = password_hash($test_password, PASSWORD_BCRYPT);
echo "<p><strong>Generated Hash:</strong><br>" . htmlspecialchars($hash1) . "</p>";

echo "<h3>Step 2: Test Password Verification</h3>";
$verify1 = password_verify($test_password, $hash1);
echo "<p><strong>Hash verification result:</strong> " . ($verify1 ? '<span style="color: green;">✓ TRUE</span>' : '<span style="color: red;">✗ FALSE</span>') . "</p>";

echo "<h3>Step 3: Check Database Admin User</h3>";
$query = "SELECT id, username, password FROM users WHERE username = 'admin' AND role = 'admin' LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p><strong>✓ Found admin user in database</strong></p>";
    echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
    echo "<p><strong>Username:</strong> " . $user['username'] . "</p>";
    echo "<p><strong>Stored Password Hash:</strong><br>" . htmlspecialchars($user['password']) . "</p>";
    
    echo "<h3>Step 4: Test Verification with Stored Hash</h3>";
    $verify_stored = password_verify($test_password, $user['password']);
    echo "<p><strong>Stored hash verification result:</strong> " . ($verify_stored ? '<span style="color: green;">✓ TRUE - Password matches!</span>' : '<span style="color: red;">✗ FALSE - Password does not match</span>') . "</p>";
    
    if (!$verify_stored) {
        echo "<p style='color: red;'><strong>Problem:</strong> The password stored in database doesn't match 'admin123'</p>";
        echo "<p><strong>Solution:</strong> Run <a href='reset_admin_password.php'><strong>reset_admin_password.php</strong></a> to update the password</p>";
    }
} else {
    echo "<p style='color: red;'><strong>✗ Admin user NOT found in database</strong></p>";
    echo "<p><strong>Solution:</strong> Run <a href='setup_admin.php'><strong>setup_admin.php</strong></a> to create the admin user</p>";
}

echo "<h3>Step 5: PHP Information</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>password_hash() function:</strong> " . (function_exists('password_hash') ? 'Built-in PHP' : 'Custom compatibility function') . "</p>";
echo "<p><strong>password_verify() function:</strong> " . (function_exists('password_verify') ? 'Built-in PHP' : 'Custom compatibility function') . "</p>";

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h2 { color: #333; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        h3 { color: #555; margin-top: 20px; }
        p { line-height: 1.6; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        strong { font-weight: bold; }
        a { color: #1e40af; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
</body>
</html>
