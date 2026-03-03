<?php
/**
 * Update Admin Password Script
 * Resets admin password to 'admin123'
 * Uses the system's password hashing function
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Admin Password - 70K Savings & Loans</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            width: 100%;
        }
        h1 {
            color: #1e40af;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 5px solid;
            font-size: 15px;
            line-height: 1.6;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }
        .code-block {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #333;
            overflow-x: auto;
        }
        .credentials {
            background: #e8f4f8;
            border: 2px solid #17a2b8;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials h3 {
            margin-top: 0;
            color: #0c5460;
            font-size: 16px;
        }
        .credentials p {
            margin: 8px 0;
            font-size: 15px;
        }
        .credentials strong {
            color: #17a2b8;
            font-family: 'Courier New', monospace;
        }
        .button {
            display: inline-block;
            background: #1e40af;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px 5px 10px 0;
            border: none;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #1e3a8a;
        }
        .button-secondary {
            background: #6c757d;
        }
        .button-secondary:hover {
            background: #5a6268;
        }
        .admin-info {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .admin-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .admin-info th {
            text-align: left;
            padding: 8px;
            background: #f0f0f0;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            color: #333;
        }
        .admin-info td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 30px 0;
        }
        .footer {
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔐 Update Admin Password</h1>
    
    <?php
    // Check if admin user exists
    $query = "SELECT id, username, email, full_name FROM users WHERE role = 'admin' LIMIT 1";
    $result = $conn->query($query);
    
    if (!$result) {
        echo '<div class="alert alert-error">';
        echo '<strong>Database Error:</strong><br>';
        echo htmlspecialchars($conn->error);
        echo '</div>';
    } elseif ($result->num_rows === 0) {
        echo '<div class="alert alert-error">';
        echo '<strong>No Admin Account Found</strong><br>';
        echo 'Please run the database.sql file first to create the admin account.';
        echo '</div>';
    } else {
        $admin = $result->fetch_assoc();
        $password = 'admin123';
        
        // Generate the hash using the system's password_hash function
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        echo '<div class="alert alert-info">';
        echo '<strong>Admin Account Found:</strong><br>';
        echo 'Username: <strong>' . htmlspecialchars($admin['username']) . '</strong><br>';
        echo 'Email: ' . htmlspecialchars($admin['email']) . '<br>';
        echo 'Name: ' . htmlspecialchars($admin['full_name']);
        echo '</div>';
        
        // Update the password in database
        $update_query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        
        if (!$stmt) {
            echo '<div class="alert alert-error">';
            echo '<strong>Database Error:</strong><br>';
            echo htmlspecialchars($conn->error);
            echo '</div>';
        } else {
            $stmt->bind_param('si', $hashed_password, $admin['id']);
            
            if (!$stmt->execute()) {
                echo '<div class="alert alert-error">';
                echo '<strong>Error updating password:</strong><br>';
                echo htmlspecialchars($stmt->error);
                echo '</div>';
            } else {
                echo '<div class="alert alert-success">';
                echo '<strong>✅ Password Updated Successfully!</strong><br>';
                echo 'The admin password has been reset and is ready to use.';
                echo '</div>';
                
                echo '<div class="credentials">';
                echo '<h3>Login Credentials</h3>';
                echo '<p><strong>Username:</strong> admin</p>';
                echo '<p><strong>Password:</strong> admin123</p>';
                echo '</div>';
                
                // Verify password works
                echo '<div class="alert alert-info">';
                echo '<strong>Verification:</strong><br>';
                if (verifyPassword($password, $hashed_password)) {
                    echo '✅ Password verification: <strong style="color: green;">SUCCESS</strong><br>';
                    echo 'The password will work for login.';
                } else {
                    echo '⚠️ Password verification: <strong style="color: red;">FAILED</strong><br>';
                    echo 'There may be an issue with password verification.';
                }
                echo '</div>';
            }
        }
        
        echo '<hr>';
        echo '<h3>Next Steps:</h3>';
        echo '<ol>';
        echo '<li>Click the button below to go to the login page</li>';
        echo '<li>Enter username: <strong>admin</strong></li>';
        echo '<li>Enter password: <strong>admin123</strong></li>';
        echo '<li>You should see the admin dashboard</li>';
        echo '<li>Change your password to a secure one in your admin profile</li>';
        echo '<li>Delete this file (update_admin_password.php) for security</li>';
        echo '</ol>';
        
        echo '<div style="margin: 30px 0;">';
        echo '<a href="login.php" class="button">→ Go to Login Page</a>';
        echo '<a href="index.php" class="button button-secondary">→ Go to Home</a>';
        echo '</div>';
        
        // Show hash details for debugging
        echo '<hr>';
        echo '<details>';
        echo '<summary>Hash Details (for debugging)</summary>';
        echo '<div class="code-block">';
        echo '<strong>Password:</strong> ' . htmlspecialchars($password) . '<br>';
        echo '<strong>Generated Hash:</strong><br>';
        echo '<code>' . htmlspecialchars($hashed_password) . '</code>';
        echo '</div>';
        echo '</details>';
    }
    
    echo '<div class="footer">';
    echo '<strong>⚠️ Security Notice:</strong><br>';
    echo 'After confirming that login works, please delete this file (update_admin_password.php) from your server for security reasons. ';
    echo 'This file can reset the admin password and should not be publicly accessible.';
    echo '</div>';
    ?>
</div>
</body>
</html>

<?php
$conn->close();
?>
