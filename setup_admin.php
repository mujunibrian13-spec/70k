<?php
/**
 * Admin Setup Script
 * Creates/updates admin user account for login
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Admin credentials
$admin_username = 'admin';
$admin_password = 'admin123';
$admin_email = 'admin@70k.local';
$admin_name = 'Administrator';

// Hash the password using our compatibility function
$hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);

echo "<h2>Admin Setup Script</h2>";
echo "<p>Setting up admin account...</p>";

// Check if admin user exists
$check_query = "SELECT id FROM users WHERE username = ? AND role = 'admin'";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param('s', $admin_username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Insert admin user
    $insert_query = "INSERT INTO users (username, email, password, full_name, role, status, created_at) 
                     VALUES (?, ?, ?, ?, 'admin', 'active', NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param('ssss', $admin_username, $admin_email, $hashed_password, $admin_name);
    
    if ($insert_stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong style='color: #155724;'>✓ Admin user created successfully!</strong><br>";
        echo "Username: <code>admin</code><br>";
        echo "Password: <code>admin123</code><br>";
        echo "Hash stored: " . htmlspecialchars(substr($hashed_password, 0, 50)) . "...<br>";
        echo "You can now <a href='login.php'><strong>login here</strong></a>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong style='color: #721c24;'>✗ Error creating admin user:</strong> " . htmlspecialchars($insert_stmt->error);
        echo "</div>";
    }
} else {
    // Update admin password
    $update_query = "UPDATE users SET password = ? WHERE username = ? AND role = 'admin'";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('ss', $hashed_password, $admin_username);
    
    if ($update_stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong style='color: #155724;'>✓ Admin password updated successfully!</strong><br>";
        echo "Username: <code>admin</code><br>";
        echo "Password: <code>admin123</code><br>";
        echo "Hash stored: " . htmlspecialchars(substr($hashed_password, 0, 50)) . "...<br>";
        echo "You can now <a href='login.php'><strong>login here</strong></a>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong style='color: #721c24;'>✗ Error updating admin password:</strong> " . htmlspecialchars($update_stmt->error);
        echo "</div>";
    }
}

$conn->close();
?>
