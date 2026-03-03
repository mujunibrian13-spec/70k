<?php
/**
 * Debug Admin Login
 * Diagnoses why admin login is failing
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Debug Admin Login</title>
    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background: #f0f0f0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 1000px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        h3 { color: #333; margin-top: 25px; }
        .section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .code {
            background: #f5f5f5;
            padding: 12px;
            border-left: 4px solid #1e40af;
            font-family: 'Courier New';
            font-size: 12px;
            margin: 10px 0;
            overflow-x: auto;
        }
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
        .button {
            display: inline-block;
            background: #1e40af;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin: 5px 0;
            border: none;
            cursor: pointer;
        }
        .button:hover { background: #1e3a8a; }
        .button-danger {
            background: #dc3545;
        }
        .button-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Debug Admin Login</h1>
    
    <?php
    
    echo '<h3>Step 1: Check Admin User Exists</h3>';
    
    $admin_query = "SELECT id, username, email, password, full_name, role FROM users WHERE role = 'admin' LIMIT 1";
    $admin_result = $conn->query($admin_query);
    
    if (!$admin_result) {
        echo '<div class="section error">';
        echo '<strong>Database Error:</strong><br>';
        echo htmlspecialchars($conn->error);
        echo '</div>';
        die();
    }
    
    if ($admin_result->num_rows === 0) {
        echo '<div class="section error">';
        echo '<strong>NO ADMIN USER FOUND</strong><br>';
        echo 'No admin account exists in the database.';
        echo '</div>';
    } else {
        $admin = $admin_result->fetch_assoc();
        
        echo '<div class="section success">';
        echo '<strong>✅ Admin user found!</strong><br>';
        echo '<table>';
        echo '<tr><th>Field</th><th>Value</th></tr>';
        echo '<tr><td>ID</td><td>' . htmlspecialchars($admin['id']) . '</td></tr>';
        echo '<tr><td>Username</td><td>' . htmlspecialchars($admin['username']) . '</td></tr>';
        echo '<tr><td>Email</td><td>' . htmlspecialchars($admin['email']) . '</td></tr>';
        echo '<tr><td>Full Name</td><td>' . htmlspecialchars($admin['full_name']) . '</td></tr>';
        echo '<tr><td>Role</td><td>' . htmlspecialchars($admin['role']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        echo '<h3>Step 2: Analyze Current Password Hash</h3>';
        
        $current_hash = $admin['password'];
        $hash_length = strlen($current_hash);
        $hash_prefix = substr($current_hash, 0, 20);
        
        echo '<div class="section info">';
        echo '<strong>Current Hash Analysis:</strong><br>';
        echo 'Length: ' . $hash_length . ' characters<br>';
        echo 'Prefix: ' . htmlspecialchars($hash_prefix) . '...<br>';
        echo '<strong>Full Hash:</strong><br>';
        echo '<div class="code">' . htmlspecialchars($current_hash) . '</div>';
        
        // Detect hash format
        echo '<strong>Hash Format Detection:</strong><br>';
        if (strpos($current_hash, 'sha256$') === 0) {
            echo '✅ Detected: SHA256 Custom Format (sha256$...)<br>';
        } elseif (strpos($current_hash, '$2') === 0) {
            echo '✅ Detected: Bcrypt Format ($2y$, $2a$, or $2b$)<br>';
        } elseif (strpos($current_hash, 'md5$') === 0) {
            echo '✅ Detected: MD5 Format (md5$...)<br>';
        } elseif (strlen($current_hash) === 32 && ctype_xdigit($current_hash)) {
            echo '✅ Detected: Direct MD5 (32-character hex)<br>';
        } else {
            echo '❌ Unknown format<br>';
        }
        
        echo '</div>';
        
        echo '<h3>Step 3: Test Password Verification</h3>';
        
        $test_password = 'admin123';
        
        echo '<div class="section">';
        echo '<strong>Testing password:</strong> ' . htmlspecialchars($test_password) . '<br>';
        
        // Test with current hash
        $verify_result = verifyPassword($test_password, $current_hash);
        
        echo '<strong>Verification Result:</strong><br>';
        if ($verify_result) {
            echo '<div class="code success">✅ PASSWORD VERIFIED - Login should work!</div>';
        } else {
            echo '<div class="code error">❌ PASSWORD NOT VERIFIED - Login will fail</div>';
            echo '<strong>Reason:</strong> The password "admin123" does not match the hash in the database.<br>';
        }
        
        echo '</div>';
        
        echo '<h3>Step 4: Test With New Generated Hash</h3>';
        
        $new_hash = password_hash($test_password, PASSWORD_BCRYPT);
        $new_verify = verifyPassword($test_password, $new_hash);
        
        echo '<div class="section">';
        echo '<strong>Generating new hash for password:</strong> ' . htmlspecialchars($test_password) . '<br>';
        echo '<strong>New generated hash:</strong><br>';
        echo '<div class="code">' . htmlspecialchars($new_hash) . '</div>';
        echo '<strong>Verification with new hash:</strong><br>';
        if ($new_verify) {
            echo '<div class="code success">✅ NEW HASH VERIFIED - This hash works!</div>';
        } else {
            echo '<div class="code error">❌ NEW HASH NOT VERIFIED - Hash generation failed</div>';
        }
        echo '</div>';
        
        echo '<h3>Step 5: Summary & Recommendation</h3>';
        
        echo '<div class="section">';
        
        if ($verify_result) {
            echo '<div class="code success"><strong>✅ EVERYTHING LOOKS GOOD!</strong></div>';
            echo '<p>The admin password "admin123" should work for login.</p>';
            echo '<p>Try logging in at: <a href="login.php">login.php</a></p>';
        } else {
            echo '<div class="code error"><strong>⚠️ PASSWORD HASH MISMATCH</strong></div>';
            echo '<p>The password "admin123" does not match the hash in the database.</p>';
            echo '<p>The stored hash is for a different password, or was corrupted.</p>';
            echo '<p><strong>Solution:</strong> Update the password hash in the database.</p>';
            
            // Offer to update
            echo '<form method="POST" style="margin-top: 20px;">';
            echo '<input type="hidden" name="action" value="reset_password">';
            echo '<input type="hidden" name="new_hash" value="' . htmlspecialchars($new_hash) . '">';
            echo '<input type="hidden" name="admin_id" value="' . htmlspecialchars($admin['id']) . '">';
            echo '<button type="submit" class="button" onclick="return confirm(\'Reset admin password to admin123?\');">';
            echo 'Reset Password to admin123';
            echo '</button>';
            echo '</form>';
        }
        
        echo '</div>';
    }
    
    // Handle password reset
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $new_hash = isset($_POST['new_hash']) ? $_POST['new_hash'] : '';
        $admin_id = intval($_POST['admin_id']);
        
        if (!empty($new_hash) && $admin_id > 0) {
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('si', $new_hash, $admin_id);
            
            if ($update_stmt->execute()) {
                echo '<div class="section success">';
                echo '<h3>✅ Password Updated Successfully!</h3>';
                echo '<p>Admin password has been reset to: <strong>admin123</strong></p>';
                echo '<p><a href="login.php" class="button">Go to Login Page →</a></p>';
                echo '</div>';
            } else {
                echo '<div class="section error">';
                echo '<strong>Error updating password:</strong><br>';
                echo htmlspecialchars($update_stmt->error);
                echo '</div>';
            }
        }
    }
    
    ?>
    
    <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
    
    <p style="color: #666; font-size: 12px;">
        <strong>Debug Information:</strong><br>
        PHP Version: <?php echo PHP_VERSION; ?><br>
        PHP Version ID: <?php echo PHP_VERSION_ID; ?><br>
        MySQL: <?php echo $conn->server_info; ?><br>
        Database: <?php echo DB_NAME; ?>
    </p>
</div>
</body>
</html>

<?php
$conn->close();
?>
