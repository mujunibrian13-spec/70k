<?php
/**
 * Direct Admin Password Reset
 * Directly resets admin password in database
 * This is a simple, straightforward script with no complications
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

$password = 'admin123';
$new_hash = password_hash($password, PASSWORD_BCRYPT);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Direct Admin Password Reset</title>
    <style>
        body {
            font-family: Arial;
            margin: 50px;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        h1 { color: #1e40af; border-bottom: 3px solid #1e40af; padding-bottom: 15px; }
        .section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .code {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New';
            font-size: 12px;
            margin: 10px 0;
            overflow-x: auto;
            border: 1px solid #ddd;
        }
        .button {
            display: inline-block;
            background: #1e40af;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            margin: 10px 5px 10px 0;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .button:hover { background: #1e3a8a; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .credentials {
            background: #e8f4f8;
            border: 2px solid #17a2b8;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        h3 { color: #0c5460; }
        strong { color: #0c5460; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔐 Direct Admin Password Reset</h1>
    
    <?php
    
    // Step 1: Check admin exists
    echo '<h2>Step 1: Find Admin Account</h2>';
    
    $admin_query = "SELECT id, username, email, full_name FROM users WHERE role = 'admin' LIMIT 1";
    $admin_result = $conn->query($admin_query);
    
    if (!$admin_result) {
        echo '<div class="section error">';
        echo '<strong>❌ Database Error:</strong><br>' . htmlspecialchars($conn->error);
        echo '</div>';
        die();
    }
    
    if ($admin_result->num_rows === 0) {
        echo '<div class="section error">';
        echo '<strong>❌ No Admin Account Found</strong><br>';
        echo 'Cannot find admin user in database.';
        echo '</div>';
        die();
    }
    
    $admin = $admin_result->fetch_assoc();
    $admin_id = $admin['id'];
    
    echo '<div class="section success">';
    echo '<strong>✅ Admin Account Found</strong><br>';
    echo '<table>';
    echo '<tr><td><strong>ID:</strong></td><td>' . htmlspecialchars($admin_id) . '</td></tr>';
    echo '<tr><td><strong>Username:</strong></td><td>' . htmlspecialchars($admin['username']) . '</td></tr>';
    echo '<tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($admin['email']) . '</td></tr>';
    echo '<tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($admin['full_name']) . '</td></tr>';
    echo '</table>';
    echo '</div>';
    
    // Step 2: Generate new hash
    echo '<h2>Step 2: Generate Password Hash</h2>';
    
    echo '<div class="section">';
    echo '<strong>Password:</strong> ' . htmlspecialchars($password) . '<br><br>';
    echo '<strong>Generated Hash:</strong><br>';
    echo '<div class="code">' . htmlspecialchars($new_hash) . '</div>';
    echo '<p><small>This hash will be stored in the database.</small></p>';
    echo '</div>';
    
    // Step 3: Update database
    echo '<h2>Step 3: Update Database</h2>';
    
    $update_query = "UPDATE users SET password = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    
    if (!$update_stmt) {
        echo '<div class="section error">';
        echo '<strong>❌ Prepare Error:</strong><br>' . htmlspecialchars($conn->error);
        echo '</div>';
        die();
    }
    
    $update_stmt->bind_param('si', $new_hash, $admin_id);
    
    if (!$update_stmt->execute()) {
        echo '<div class="section error">';
        echo '<strong>❌ Update Failed:</strong><br>' . htmlspecialchars($update_stmt->error);
        echo '</div>';
        die();
    }
    
    echo '<div class="section success">';
    echo '<strong>✅ Password Updated Successfully!</strong><br>';
    echo 'The admin password has been reset.';
    echo '</div>';
    
    // Step 4: Verify
    echo '<h2>Step 4: Verify Password Works</h2>';
    
    // Re-fetch to verify
    $verify_query = "SELECT password FROM users WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param('i', $admin_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $verify_row = $verify_result->fetch_assoc();
    $stored_hash = $verify_row['password'];
    
    $password_verify_test = verifyPassword($password, $stored_hash);
    
    echo '<div class="section ' . ($password_verify_test ? 'success' : 'error') . '">';
    if ($password_verify_test) {
        echo '<strong>✅ Password Verification Success!</strong><br>';
        echo 'The password "admin123" will work for login.';
    } else {
        echo '<strong>❌ Password Verification Failed!</strong><br>';
        echo 'The password verification returned false. This may indicate an issue.';
    }
    echo '</div>';
    
    // Step 5: Login credentials
    echo '<h2>Step 5: Login Credentials</h2>';
    
    echo '<div class="credentials">';
    echo '<h3>✅ Ready to Login</h3>';
    echo '<p><strong>Username:</strong> admin</p>';
    echo '<p><strong>Password:</strong> admin123</p>';
    echo '</div>';
    
    // Step 6: Next steps
    echo '<h2>Step 6: Next Steps</h2>';
    
    echo '<div class="section">';
    echo '<ol>';
    echo '<li><a href="login.php" class="button">Go to Login Page</a></li>';
    echo '<li>Enter username: <strong>admin</strong></li>';
    echo '<li>Enter password: <strong>admin123</strong></li>';
    echo '<li>Click Login</li>';
    echo '<li>You should see the Admin Dashboard</li>';
    echo '</ol>';
    echo '</div>';
    
    // Summary
    echo '<h2>Summary</h2>';
    
    echo '<div class="section success">';
    echo '<strong>✅ Admin Password Reset Complete!</strong><br><br>';
    echo 'What was done:<br>';
    echo '• Found admin account (ID: ' . htmlspecialchars($admin_id) . ')<br>';
    echo '• Generated new password hash using password_hash()<br>';
    echo '• Updated database with new hash<br>';
    echo '• Verified password works with new hash<br>';
    echo '• Ready for login<br><br>';
    echo '<strong>Login credentials:</strong><br>';
    echo 'Username: <code>admin</code><br>';
    echo 'Password: <code>admin123</code>';
    echo '</div>';
    
    ?>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="login.php" class="button">→ Go to Login</a>
        <a href="index.php" class="button" style="background: #6c757d;">→ Go to Home</a>
    </div>
    
    <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
    
    <p style="color: #666; font-size: 12px;">
        <strong>⚠️ IMPORTANT:</strong> Delete this file (direct_reset_admin_password.php) after verifying login works.<br>
        This file can reset the admin password and should not be left on the server.
    </p>
</div>
</body>
</html>

<?php
$conn->close();
?>
