<?php
/**
 * Reset Member Passwords Script
 * Helps reset passwords for members who cannot login
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

echo "<h2>Reset Member Passwords</h2>";

// Get all members
$query = "SELECT u.id, u.username, u.email, u.full_name, m.id as member_id, m.phone 
          FROM users u
          LEFT JOIN members m ON u.id = m.user_id
          WHERE u.role = 'member'
          ORDER BY u.username";

$result = $conn->query($query);

echo "<p>This tool helps reset passwords for members. Choose a member below and set a new password.</p>";

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = intval($_POST['user_id']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #f5c6cb;'>";
        echo "<strong style='color: #721c24;'>✗ Error: Password fields cannot be empty</strong>";
        echo "</div>";
    } elseif ($new_password !== $confirm_password) {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #f5c6cb;'>";
        echo "<strong style='color: #721c24;'>✗ Error: Passwords do not match</strong>";
        echo "</div>";
    } elseif (strlen($new_password) < 6) {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #f5c6cb;'>";
        echo "<strong style='color: #721c24;'>✗ Error: Password must be at least 6 characters</strong>";
        echo "</div>";
    } else {
        // Hash and update password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $update_query = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('si', $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            // Get user info
            $info_query = "SELECT username, email, full_name FROM users WHERE id = ?";
            $info_stmt = $conn->prepare($info_query);
            $info_stmt->bind_param('i', $user_id);
            $info_stmt->execute();
            $info_result = $info_stmt->get_result();
            $user_info = $info_result->fetch_assoc();
            
            echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #c3e6cb;'>";
            echo "<strong style='color: #155724; font-size: 16px;'>✓ Password reset successfully!</strong><br><br>";
            echo "<p><strong>Member:</strong> " . htmlspecialchars($user_info['full_name']) . "</p>";
            echo "<p><strong>Username:</strong> " . htmlspecialchars($user_info['username']) . "</p>";
            echo "<p><strong>New Password:</strong> <code style='font-size: 14px;'>" . htmlspecialchars($new_password) . "</code></p>";
            echo "<p style='margin-top: 15px;'><a href='login.php'><strong>← Back to Login</strong></a></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #f5c6cb;'>";
            echo "<strong style='color: #721c24;'>✗ Error: Failed to reset password</strong>";
            echo "</div>";
        }
    }
}

if ($result && $result->num_rows > 0) {
    echo "<form method='POST' style='margin-top: 20px;'>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label for='user_id' style='display: block; margin-bottom: 5px; font-weight: bold;'>Select Member:</label>";
    echo "<select name='user_id' id='user_id' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "<option value=''>Choose a member...</option>";
    
    $result->data_seek(0);
    while ($user = $result->fetch_assoc()) {
        echo "<option value='" . $user['id'] . "'>";
        echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')');
        echo "</option>";
    }
    
    echo "</select>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label for='new_password' style='display: block; margin-bottom: 5px; font-weight: bold;'>New Password:</label>";
    echo "<input type='password' name='new_password' id='new_password' placeholder='Enter new password' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "<small style='color: #666;'>Minimum 6 characters</small>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label for='confirm_password' style='display: block; margin-bottom: 5px; font-weight: bold;'>Confirm Password:</label>";
    echo "<input type='password' name='confirm_password' id='confirm_password' placeholder='Confirm password' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "</div>";
    
    echo "<button type='submit' style='background: #1e40af; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;'>Reset Password</button>";
    echo "</form>";
} else {
    echo "<p style='color: red;'><strong>✗ No registered members found</strong></p>";
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
            color: #1e40af;
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
