<?php
/**
 * Reset Admin Password Script
 * Resets admin password to admin123
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

echo "<h2>Reset Admin Password</h2>";

$admin_username = 'admin';
$admin_password = 'admin123';

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);

echo "<p>Hashing password...</p>";
echo "<p><strong>Generated hash:</strong><br>" . htmlspecialchars(substr($hashed_password, 0, 100)) . "...</p>";

// Check if admin exists
$check_query = "SELECT id FROM users WHERE username = ? AND role = 'admin'";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param('s', $admin_username);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    // Admin doesn't exist, create it
    echo "<p>Admin user not found. Creating new admin user...</p>";
    
    $admin_email = 'admin@70k.local';
    $admin_name = 'Administrator';
    
    $insert_query = "INSERT INTO users (username, email, password, full_name, role, status, created_at) 
                     VALUES (?, ?, ?, ?, 'admin', 'active', NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param('ssss', $admin_username, $admin_email, $hashed_password, $admin_name);
    
    if ($insert_stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #c3e6cb;'>";
        echo "<strong style='color: #155724; font-size: 16px;'>✓ Admin user created successfully!</strong><br><br>";
        echo "<p><strong>Login Credentials:</strong></p>";
        echo "<p><code style='font-size: 14px;'>Username: admin</code></p>";
        echo "<p><code style='font-size: 14px;'>Password: admin123</code></p>";
        echo "<p><a href='login.php'><strong>Go to Login Page →</strong></a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #f5c6cb;'>";
        echo "<strong style='color: #721c24;'>✗ Error creating admin user:</strong><br>";
        echo htmlspecialchars($insert_stmt->error);
        echo "</div>";
    }
} else {
    // Admin exists, update password
    echo "<p>Admin user found. Updating password...</p>";
    
    $update_query = "UPDATE users SET password = ?, updated_at = NOW() WHERE username = ? AND role = 'admin'";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('ss', $hashed_password, $admin_username);
    
    if ($update_stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #c3e6cb;'>";
        echo "<strong style='color: #155724; font-size: 16px;'>✓ Admin password updated successfully!</strong><br><br>";
        echo "<p><strong>Login Credentials:</strong></p>";
        echo "<p><code style='font-size: 14px;'>Username: admin</code></p>";
        echo "<p><code style='font-size: 14px;'>Password: admin123</code></p>";
        echo "<p><a href='login.php'><strong>Go to Login Page →</strong></a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #f5c6cb;'>";
        echo "<strong style='color: #721c24;'>✗ Error updating admin password:</strong><br>";
        echo htmlspecialchars($update_stmt->error);
        echo "</div>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 40px;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        h2 { 
            color: #1e40af; 
            border-bottom: 3px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        p { 
            line-height: 1.8;
            color: #333;
            margin: 10px 0;
        }
        code { 
            background: #f5f5f5; 
            padding: 8px 12px; 
            border-radius: 4px; 
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #1e40af;
            display: inline-block;
            margin: 5px 0;
        }
        a {
            color: #1e40af;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
</div>
</body>
</html>
