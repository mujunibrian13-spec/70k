<?php
/**
 * Fix Admin Login Redirect Loop
 * Ensures admin user has correct role and session setup
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

$message = '';
$error = '';
$admin_fixed = false;

// Check if we're processing a login test
if (isset($_GET['test_login']) && $_GET['test_login'] === '1') {
    // Start fresh session
    session_destroy();
    session_start();
    
    // Try to login as admin
    $admin_username = 'admin';
    $admin_password = 'admin123';
    
    // Query database for user
    $query = "SELECT id, username, password, email, full_name, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        echo "<h3>Debug Info:</h3>";
        echo "<ul>";
        echo "<li>Username found: " . htmlspecialchars($user['username']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "<li>Full Name: " . htmlspecialchars($user['full_name']) . "</li>";
        echo "<li>Role: <strong>" . htmlspecialchars($user['role']) . "</strong></li>";
        echo "<li>Password stored (first 50 chars): " . htmlspecialchars(substr($user['password'], 0, 50)) . "...</li>";
        echo "</ul>";
        
        // Verify password
        if (verifyPassword($admin_password, $user['password'])) {
            echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 4px;'>";
            echo "<strong style='color: #155724;'>✓ Password verification: SUCCESS</strong>";
            echo "</div>";
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            echo "<div style='background: #cfe2ff; padding: 15px; margin: 15px 0; border-radius: 4px;'>";
            echo "<strong>Session Variables Set:</strong><br>";
            echo "user_id: " . $_SESSION['user_id'] . "<br>";
            echo "user_name: " . $_SESSION['user_name'] . "<br>";
            echo "user_email: " . $_SESSION['user_email'] . "<br>";
            echo "user_role: " . $_SESSION['user_role'] . "<br>";
            echo "</div>";
            
            // Check role
            echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 4px;'>";
            echo "<strong>isAdmin() check:</strong><br>";
            if (isAdmin()) {
                echo "✓ isAdmin() returns TRUE<br>";
                echo "<a href='admin.php' style='color: blue; text-decoration: underline;'><strong>Click here to go to admin dashboard</strong></a>";
            } else {
                echo "✗ isAdmin() returns FALSE<br>";
                $role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET';
                echo "This means role is: " . $role . "<br>";
            }
            echo "</div>";
            
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 4px;'>";
            echo "<strong style='color: #721c24;'>✗ Password verification: FAILED</strong>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 4px;'>";
        echo "<strong style='color: #721c24;'>✗ Admin user not found in database</strong>";
        echo "</div>";
    }
}

// Fix the admin user if needed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_admin'])) {
    $admin_username = 'admin';
    $admin_password = 'admin123';
    $admin_email = 'admin@70k.local';
    $admin_name = 'Administrator';
    
    // Hash the password
    $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);
    
    // Check if admin exists
    $check_query = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('s', $admin_username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing admin user
        $update_query = "UPDATE users SET password = ?, email = ?, full_name = ?, role = 'admin', status = 'active' WHERE username = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('ssss', $hashed_password, $admin_email, $admin_name, $admin_username);
        
        if ($update_stmt->execute()) {
            $message = 'Admin user updated successfully!';
            $admin_fixed = true;
        } else {
            $error = 'Error updating admin user: ' . $update_stmt->error;
        }
    } else {
        // Create new admin user
        $insert_query = "INSERT INTO users (username, email, password, full_name, role, status, created_at) 
                        VALUES (?, ?, ?, ?, 'admin', 'active', NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('ssss', $admin_username, $admin_email, $hashed_password, $admin_name);
        
        if ($insert_stmt->execute()) {
            $message = 'Admin user created successfully!';
            $admin_fixed = true;
        } else {
            $error = 'Error creating admin user: ' . $insert_stmt->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Login - 70K Savings & Loans</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 700px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 30px;
            text-align: center;
        }
        
        .card-body {
            padding: 40px 30px;
        }
        
        .step-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .btn-large {
            padding: 12px 30px;
            font-size: 1.1rem;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-tools"></i> Fix Admin Login</h2>
                <p class="mb-0">Resolve redirect loop issues</p>
            </div>
            
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <h4 class="mb-3">Admin Login Fix Steps</h4>
                
                <div class="step-box">
                    <h5><i class="fas fa-circle-1"></i> Step 1: Fix Admin User</h5>
                    <p>This will create or update the admin user with correct credentials:</p>
                    <ul>
                        <li><strong>Username:</strong> admin</li>
                        <li><strong>Email:</strong> admin@70k.local</li>
                        <li><strong>Password:</strong> admin123</li>
                        <li><strong>Role:</strong> admin</li>
                    </ul>
                    
                    <form method="POST" style="margin-top: 15px;">
                        <button type="submit" name="fix_admin" class="btn btn-primary btn-large">
                            <i class="fas fa-wrench"></i> Fix Admin User Now
                        </button>
                    </form>
                </div>
                
                <div class="step-box">
                    <h5><i class="fas fa-circle-2"></i> Step 2: Test Login</h5>
                    <p>After fixing, test that the login works:</p>
                    
                    <a href="?test_login=1" class="btn btn-info btn-large">
                        <i class="fas fa-vial"></i> Test Admin Login
                    </a>
                </div>
                
                <div class="step-box">
                    <h5><i class="fas fa-circle-3"></i> Step 3: Go to Dashboard</h5>
                    <p>If test succeeds, go to admin dashboard:</p>
                    
                    <a href="admin.php" class="btn btn-success btn-large">
                        <i class="fas fa-tachometer-alt"></i> Go to Admin Dashboard
                    </a>
                </div>
                
                <hr>
                
                <h4 class="mt-4 mb-3">Manual Login</h4>
                <p>Or try logging in manually:</p>
                
                <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
                    <strong>Admin Credentials:</strong><br>
                    Email/Username: <code>admin</code><br>
                    Password: <code>admin123</code><br>
                    <a href="login.php" class="btn btn-primary btn-sm mt-3">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                </div>
                
                <hr>
                
                <h4 class="mt-4 mb-3">What This Fixes</h4>
                <ul>
                    <li>✓ Creates/updates admin user in database</li>
                    <li>✓ Ensures role is set to 'admin'</li>
                    <li>✓ Fixes redirect loop by setting correct role</li>
                    <li>✓ Ensures session variables are set properly</li>
                    <li>✓ Password hashing is correct</li>
                </ul>
                
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i>
                    <strong>Why This Happens:</strong> The redirect loop occurs when the admin user exists 
                    but doesn't have the 'admin' role set in the database. This causes <code>isAdmin()</code> 
                    to return false, redirecting back to login.php, creating an infinite loop.
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
