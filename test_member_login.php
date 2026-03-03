<?php
/**
 * Test Member Login Script
 * Debugs member password issues
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

echo "<h2>Member Login Debug</h2>";

// Get all members with user accounts
$query = "SELECT u.id, u.username, u.email, u.password, u.full_name, m.id as member_id, m.phone 
          FROM users u
          LEFT JOIN members m ON u.id = m.user_id
          WHERE u.role = 'member'
          LIMIT 5";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h3>Found " . $result->num_rows . " registered members:</h3>";
    
    while ($user = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 4px;'>";
        echo "<p><strong>Username:</strong> " . htmlspecialchars($user['username']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
        echo "<p><strong>Full Name:</strong> " . htmlspecialchars($user['full_name']) . "</p>";
        echo "<p><strong>Phone:</strong> " . htmlspecialchars($user['phone']) . "</p>";
        echo "<p><strong>Password Hash:</strong><br><code style='font-size: 11px;'>" . htmlspecialchars(substr($user['password'], 0, 80)) . "...</code></p>";
        echo "<p><strong>Member ID:</strong> " . ($user['member_id'] ? '<span style="color: green;">✓ ' . $user['member_id'] . '</span>' : '<span style="color: red;">✗ Not linked</span>') . "</p>";
        
        // Test password verification
        echo "<p><strong>Test Login:</strong></p>";
        echo "<form method='POST' style='display: inline;'>";
        echo "<input type='hidden' name='test_user_id' value='" . $user['id'] . "'>";
        echo "<input type='hidden' name='test_username' value='" . htmlspecialchars($user['username']) . "'>";
        echo "<input type='password' name='test_password' placeholder='Enter password' style='padding: 5px; margin-right: 5px;'>";
        echo "<button type='submit' class='btn btn-sm btn-primary'>Test Password</button>";
        echo "</form>";
        
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'><strong>✗ No registered members found</strong></p>";
}

// Test specific password if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_password'])) {
    $test_user_id = intval($_POST['test_user_id']);
    $test_username = sanitize($_POST['test_username']);
    $test_password = $_POST['test_password'];
    
    // Get user
    $user_query = "SELECT password FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param('i', $test_user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $verify = password_verify($test_password, $user['password']);
        
        echo "<div style='background: " . ($verify ? '#d4edda' : '#f8d7da') . "; padding: 15px; margin: 20px 0; border-radius: 4px;'>";
        echo "<strong>" . ($verify ? '✓ Password CORRECT' : '✗ Password INCORRECT') . "</strong>";
        echo "</div>";
    }
}

echo "<h3>Troubleshooting:</h3>";
echo "<ol>";
echo "<li>Check the password hashes above - they should all start with 'sha256\$'</li>";
echo "<li>If a member's password hash looks wrong, delete and re-register that member</li>";
echo "<li>Try logging in with the test form above with the correct password</li>";
echo "<li>If password still doesn't work, run <a href='reset_member_passwords.php'><strong>reset_member_passwords.php</strong></a></li>";
echo "</ol>";

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5;
        }
        h2 { 
            color: #333; 
            border-bottom: 2px solid #1e40af; 
            padding-bottom: 10px; 
        }
        h3 { 
            color: #555; 
            margin-top: 20px;
        }
        p { 
            line-height: 1.6; 
            margin: 8px 0;
        }
        code { 
            background: #f0f0f0; 
            padding: 4px 8px; 
            border-radius: 3px; 
            font-family: monospace;
            word-break: break-all;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background: #1e40af;
            color: white;
        }
        .btn-primary:hover {
            background: #1e3a8a;
        }
        input {
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
</body>
</html>
