<?php
/**
 * Member Registration Page
 * Allows new members to register for the system
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize(isset($_POST['full_name']) ? $_POST['full_name'] : '');
    $email = sanitize(isset($_POST['email']) ? $_POST['email'] : '');
    $phone = sanitize(isset($_POST['phone']) ? $_POST['phone'] : '');
    $nin = sanitize(isset($_POST['nin']) ? $_POST['nin'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate input
    if (empty($full_name) || empty($email) || empty($phone) || empty($nin) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email address';
    } elseif (strlen($nin) !== 14 || !ctype_alnum($nin)) {
        $error = 'NIN must be exactly 14 alphanumeric characters (letters and numbers)';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param('s', $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Create user account
            $username = strtolower(str_replace(' ', '.', $full_name));
            $hashed_password = hashPassword($password);
            
            $user_query = "INSERT INTO users (username, email, password, plain_password, full_name, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'member', 'active', NOW(), NOW())";
            $user_stmt = $conn->prepare($user_query);
            $user_stmt->bind_param('sssss', $username, $email, $hashed_password, $password, $full_name);
            
            if ($user_stmt->execute()) {
                $user_id = $conn->insert_id;
                
                // Create member record
                $member_query = "INSERT INTO members (user_id, full_name, email, phone, nin, savings_amount, status, date_joined) VALUES (?, ?, ?, ?, ?, 0, 'active', CURDATE())";
                $member_stmt = $conn->prepare($member_query);
                $member_stmt->bind_param('issss', $user_id, $full_name, $email, $phone, $nin);
                
                if ($member_stmt->execute()) {
                    $success = 'Registration successful! Please log in with your credentials.';
                    // Clear form
                    $_POST = array();
                } else {
                    $error = 'Failed to complete registration. Please try again.';
                }
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - 70K Savings & Loans</title>
    
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
            padding: 2rem 0;
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
        }
        
        .register-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .register-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <!-- Registration Container -->
    <div class="register-container">
        <!-- Registration Card -->
        <div class="register-card">
            <!-- Card Header -->
            <div class="register-header">
                <h1>
                    <i class="fas fa-user-plus"></i>
                </h1>
                <h1>Register</h1>
                <p>Join the 70K Group</p>
            </div>
            
            <!-- Card Body -->
            <div class="register-body">
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
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?><br>
                        <a href="login.php" class="btn btn-sm btn-success mt-2">Login Now</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Registration Form -->
                <form method="POST" class="needs-validation" novalidate>
                    <!-- Full Name Field -->
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="full_name" 
                                name="full_name" 
                                placeholder="Enter your full name"
                                value="<?php echo htmlspecialchars(isset($_POST['full_name']) ? $_POST['full_name'] : ''); ?>"
                                required
                            >
                        </div>
                        <div class="invalid-feedback">
                            Please enter your full name
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''); ?>"
                                required
                            >
                        </div>
                        <div class="invalid-feedback">
                            Please enter a valid email
                        </div>
                    </div>
                    
                    <!-- Phone Field -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="phone" 
                                name="phone" 
                                placeholder="Enter your phone number"
                                value="<?php echo htmlspecialchars(isset($_POST['phone']) ? $_POST['phone'] : ''); ?>"
                                required
                            >
                        </div>
                        <div class="invalid-feedback">
                            Please enter your phone number
                        </div>
                    </div>
                    
                    <!-- NIN Field -->
                    <div class="mb-3">
                        <label for="nin" class="form-label">NIN (National ID Number)</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nin" 
                                name="nin" 
                                placeholder="Enter 14-character NIN (e.g., AB123456CD7890)"
                                maxlength="14"
                                pattern="[A-Za-z0-9]{14}"
                                value="<?php echo htmlspecialchars(isset($_POST['nin']) ? strtoupper($_POST['nin']) : ''); ?>"
                                required
                            >
                        </div>
                        <small class="text-muted">Must be exactly 14 characters (letters A-Z and numbers 0-9)</small>
                        <div class="invalid-feedback">
                            Please enter a valid 14-character NIN
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
                                placeholder="Enter password (min 6 characters)"
                                required
                            >
                        </div>
                        <div class="invalid-feedback">
                            Please enter a password
                        </div>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Confirm password"
                                required
                            >
                        </div>
                        <div class="invalid-feedback">
                            Please confirm your password
                        </div>
                    </div>
                    
                    <!-- Register Button -->
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
                
                <!-- Additional Links -->
                <hr>
                <p class="text-center mb-0">
                    <small class="text-muted">
                        Already have an account? <a href="login.php">Login here</a>
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
