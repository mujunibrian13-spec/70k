<?php
/**
 * Login Page
 * Handles user authentication for members and admins
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize(isset($_POST['username']) ? $_POST['username'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        // Query database for user
        $query = "SELECT id, username, password, email, full_name, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
             if (verifyPassword($password, $user['password'])) {
                 // Update last_login timestamp
                 $login_update = "UPDATE users SET last_login = NOW(), updated_at = NOW() WHERE id = ?";
                 $login_stmt = $conn->prepare($login_update);
                 if ($login_stmt) {
                     $login_stmt->bind_param('i', $user['id']);
                     $login_stmt->execute();
                 }
                 
                 // Set session variables
                 $_SESSION['user_id'] = $user['id'];
                 $_SESSION['user_name'] = $user['full_name'];
                 $_SESSION['user_email'] = $user['email'];
                 $_SESSION['user_role'] = $user['role'];
                 
                 // If member, get member_id
                 if ($user['role'] === 'member') {
                     $member_query = "SELECT id FROM members WHERE user_id = ?";
                     $member_stmt = $conn->prepare($member_query);
                     $member_stmt->bind_param('i', $user['id']);
                     $member_stmt->execute();
                     $member_result = $member_stmt->get_result();
                     if ($member_result->num_rows === 1) {
                         $member = $member_result->fetch_assoc();
                         $_SESSION['member_id'] = $member['id'];
                     }
                 }
                 
                 // Log successful login
                 logError("User '{$username}' logged in successfully", 'INFO');
                 
                 // Redirect to appropriate dashboard based on role
                 if ($user['role'] === 'admin') {
                     redirect('admin.php');
                 } else {
                     redirect('index.php');
                 }
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - 70K Savings & Loans</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
        }
        
        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control:focus {
            border-color: #1e40af;
            box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.15);
        }
    </style>
</head>
<body>
    <!-- Login Container -->
    <div class="login-container">
        <!-- Login Card -->
        <div class="login-card">
            <!-- Card Header -->
            <div class="login-header">
                <h1>
                    <i class="fas fa-piggy-bank"></i>
                </h1>
                <h1>70K Savings & Loans</h1>
                <p>Member Portal</p>
            </div>
            
            <!-- Card Body -->
            <div class="login-body">
                <!-- Error Alert -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Success Alert -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="POST" class="needs-validation" novalidate>
                    <!-- Username Field -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="username" 
                                name="username" 
                                placeholder="Enter your username"
                                required
                            >
                        </div>
                        <div class="invalid-feedback">
                            Please enter your username
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                            >
                        </div>
                        <div class="invalid-feedback">
                            Please enter your password
                        </div>
                    </div>
                    
                    <!-- Remember Me Checkbox -->
                    <div class="mb-3 form-check">
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            id="remember" 
                            name="remember"
                        >
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <!-- Additional Links -->
                <hr>
                <p class="text-center mb-0">
                    <small class="text-muted">
                        Don't have an account? <a href="register.php">Register here</a>
                    </small>
                </p>
            </div>
        </div>
        

    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Script -->
    <script src="js/script.js"></script>
</body>
</html>
