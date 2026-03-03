<?php
/**
 * Fix Login Issues Script
 * Resets admin and all member passwords to ensure login works
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

echo "<h2>Fix Login Issues</h2>";
echo "<p>This script will reset passwords for admin and all registered members.</p>";

$fixed_count = 0;
$error_count = 0;

// Fix admin
echo "<h3>Fixing Admin Account</h3>";
$admin_username = 'admin';
$admin_password = 'admin123';
$hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);

$check_query = "SELECT id FROM users WHERE username = ? AND role = 'admin'";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param('s', $admin_username);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    // Create admin
    $admin_email = 'admin@70k.local';
    $admin_name = 'Administrator';
    
    $insert_query = "INSERT INTO users (username, email, password, full_name, role, status, created_at) 
                     VALUES (?, ?, ?, ?, 'admin', 'active', NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param('ssss', $admin_username, $admin_email, $hashed_password, $admin_name);
    
    if ($insert_stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 12px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong style='color: #155724;'>✓ Admin user created</strong><br>";
        echo "Username: <code>admin</code><br>";
        echo "Password: <code>admin123</code>";
        echo "</div>";
        $fixed_count++;
    } else {
        echo "<div style='background: #f8d7da; padding: 12px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong style='color: #721c24;'>✗ Error creating admin: " . htmlspecialchars($insert_stmt->error) . "</strong>";
        echo "</div>";
        $error_count++;
    }
} else {
    // Update admin password
    $update_query = "UPDATE users SET password = ? WHERE username = ? AND role = 'admin'";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('ss', $hashed_password, $admin_username);
    
    if ($update_stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 12px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong style='color: #155724;'>✓ Admin password reset</strong><br>";
        echo "Username: <code>admin</code><br>";
        echo "Password: <code>admin123</code>";
        echo "</div>";
        $fixed_count++;
    } else {
        echo "<div style='background: #f8d7da; padding: 12px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong style='color: #721c24;'>✗ Error updating admin: " . htmlspecialchars($update_stmt->error) . "</strong>";
        echo "</div>";
        $error_count++;
    }
}

// Fix all members
echo "<h3>Fixing Member Accounts</h3>";

$members_query = "SELECT u.id, u.username, u.full_name, u.email FROM users u WHERE u.role = 'member'";
$members_result = $conn->query($members_query);

if ($members_result && $members_result->num_rows > 0) {
    $member_count = $members_result->num_rows;
    echo "<p>Found <strong>$member_count</strong> registered members. Resetting their passwords...</p>";
    
    while ($member = $members_result->fetch_assoc()) {
        $member_username = $member['username'];
        $member_password = 'member123'; // Default password for all members
        $member_hash = password_hash($member_password, PASSWORD_BCRYPT);
        $member_id = $member['id'];
        
        $update_member_query = "UPDATE users SET password = ? WHERE id = ?";
        $update_member_stmt = $conn->prepare($update_member_query);
        $update_member_stmt->bind_param('si', $member_hash, $member_id);
        
        if ($update_member_stmt->execute()) {
            echo "<p>✓ " . htmlspecialchars($member['full_name'] . ' (' . $member['username'] . ')') . "</p>";
            $fixed_count++;
        } else {
            echo "<p>✗ Error fixing " . htmlspecialchars($member['username']) . "</p>";
            $error_count++;
        }
    }
} else {
    echo "<p><em>No registered members found.</em></p>";
}

echo "<h2 style='border-top: 2px solid #ddd; padding-top: 20px; margin-top: 20px;'>Summary</h2>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 4px; border-left: 4px solid #2196F3;'>";
echo "<p><strong>Fixed:</strong> $fixed_count accounts</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count === 0 && $fixed_count > 0) {
    echo "<h3 style='color: #155724; margin-top: 20px;'>Login Credentials:</h3>";
    echo "<p><strong>Admin:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "</ul>";
    
    echo "<p><strong>Members:</strong></p>";
    echo "<ul>";
    echo "<li>Username: (your registered username)</li>";
    echo "<li>Password: <code>member123</code></li>";
    echo "</ul>";
    
    echo "<p style='margin-top: 20px;'><a href='login.php' style='background: #1e40af; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block;'><strong>Go to Login →</strong></a></p>";
}

echo "</div>";

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
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        h2 { 
            color: #1e40af; 
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        h3 {
            color: #555;
            margin-top: 20px;
        }
        p { 
            line-height: 1.8;
            color: #333;
            margin: 10px 0;
        }
        code { 
            background: #f5f5f5; 
            padding: 4px 8px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace;
            color: #1e40af;
        }
        ul {
            margin-left: 20px;
        }
        li {
            margin: 5px 0;
        }
        a {
            color: white;
        }
        a:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
<div class="container">
</div>
</body>
</html>
