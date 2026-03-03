<?php
/**
 * Fix Admin Password Script
 * Resets the admin password to admin123
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<style>";
echo "body { font-family: Arial; margin: 50px; background: #f0f0f0; }";
echo ".container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h2 { color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }";
echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0; border: 1px solid #c3e6cb; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; border: 1px solid #f5c6cb; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0; border: 1px solid #bee5eb; }";
echo ".code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: 'Courier New'; margin: 10px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h2>🔐 Admin Password Reset</h2>";

// Get the current admin user
$query = "SELECT id, username, email, full_name FROM users WHERE role = 'admin' LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    // Generate the correct hash for 'admin123'
    $password = 'admin123';
    
    // First, let's see what hash our password_hash function generates
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    echo "<div class='info'>";
    echo "<strong>Admin Account Found:</strong><br>";
    echo "Username: <strong>" . htmlspecialchars($admin['username']) . "</strong><br>";
    echo "Email: " . htmlspecialchars($admin['email']) . "<br>";
    echo "Full Name: " . htmlspecialchars($admin['full_name']) . "<br>";
    echo "</div>";
    
    echo "<p>Attempting to reset password for admin account...</p>";
    echo "<div class='code'>Password: <strong>admin123</strong></div>";
    
    // Update the password
    $update_query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        echo "<div class='error'>";
        echo "<strong>❌ Database Error:</strong><br>";
        echo htmlspecialchars($conn->error);
        echo "</div>";
    } else {
        $stmt->bind_param('si', $hashed_password, $admin['id']);
        
        if ($stmt->execute()) {
            echo "<div class='success'>";
            echo "<strong>✅ Success!</strong><br>";
            echo "Admin password has been reset successfully.<br><br>";
            echo "<strong>Login credentials:</strong><br>";
            echo "Username: <strong>admin</strong><br>";
            echo "Password: <strong>admin123</strong><br><br>";
            echo "<a href='login.php' style='color: white; background: #1e40af; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>→ Go to Login Page</a>";
            echo "</div>";
            
            // Test the password
            echo "<div class='info'>";
            echo "<strong>Testing password verification...</strong><br>";
            if (password_verify($password, $hashed_password)) {
                echo "✅ Password verification: <strong>SUCCESS</strong>";
            } else {
                echo "❌ Password verification: <strong>FAILED</strong>";
            }
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<strong>❌ Error updating password:</strong><br>";
            echo htmlspecialchars($stmt->error);
            echo "</div>";
        }
    }
} else {
    echo "<div class='error'>";
    echo "<strong>❌ Error:</strong> No admin account found in database.<br>";
    echo "Please run the database.sql file first to create the admin account.";
    echo "</div>";
}

echo "<hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>";
echo "<p style='color: #666; font-size: 12px;'>";
echo "<strong>Next Steps:</strong><br>";
echo "1. Visit the <a href='login.php'>Login Page</a><br>";
echo "2. Enter username: <strong>admin</strong><br>";
echo "3. Enter password: <strong>admin123</strong><br>";
echo "4. If successful, you will see the admin dashboard<br>";
echo "5. Go to Admin Profile and change to a secure password<br>";
echo "<br>";
echo "After confirming login works, delete this file (fix_admin_password.php) for security.";
echo "</p>";

echo "</div>";
echo "</body>";
echo "</html>";

$conn->close();
?>
