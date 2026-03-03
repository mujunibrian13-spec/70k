<?php
/**
 * Change Password Page - Self Service
 * Allows members to change their own password
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$member = getMemberDetails($conn, getMemberIdByUserId($conn, $user_id));
$error = '';
$success = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        // Call password reset function
        $result = resetMemberPassword($conn, $user_id, $current_password, $new_password);
        
        if ($result['success']) {
            $success = $result['message'];
            // Clear the form
            $_POST = array();
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - 70K Savings & Loans</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-piggy-bank"></i> 70K Savings & Loans
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item active" href="change_password.php">Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4 mb-5">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="container">
                <h1 class="mb-2">
                    <i class="fas fa-lock"></i> Change Password
                </h1>
                <p class="mb-0">Update your account password for better security</p>
            </div>
        </div>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <!-- Error and Success Messages -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Change Password Form -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="fas fa-key"></i> Update Your Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <!-- Current Password -->
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="current_password" 
                                        name="current_password" 
                                        placeholder="Enter your current password"
                                        required
                                    >
                                    <small class="text-muted">We need your current password to verify your identity</small>
                                </div>

                                <!-- New Password -->
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="new_password" 
                                        name="new_password" 
                                        placeholder="Enter new password"
                                        required
                                    >
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="confirm_password" 
                                        name="confirm_password" 
                                        placeholder="Confirm new password"
                                        required
                                    >
                                    <small class="text-muted">Must match the new password above</small>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" name="change_password" class="btn btn-primary w-100">
                                    <i class="fas fa-check"></i> Change Password
                                </button>
                            </form>

                            <hr class="my-4">

                            <!-- Password Requirements -->
                            <div class="alert alert-info" role="alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle"></i> Password Requirements
                                </h6>
                                <ul class="mb-0">
                                    <li>Minimum <strong>6 characters</strong> long</li>
                                    <li>Must be <strong>different</strong> from current password</li>
                                    <li>Both new password fields must <strong>match</strong></li>
                                    <li>Passwords are <strong>case-sensitive</strong></li>
                                </ul>
                            </div>

                            <!-- Back to Profile -->
                            <div class="text-center mt-3">
                                <a href="profile.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Profile
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tips -->
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-shield-alt"></i> Security Tips</h6>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li>Choose a <strong>strong password</strong> that's hard to guess</li>
                                <li><strong>Don't share</strong> your password with anyone</li>
                                <li><strong>Change regularly</strong> for better security</li>
                                <li><strong>Use unique passwords</strong> for different accounts</li>
                                <li><strong>Log out</strong> when using shared computers</li>
                                <li><strong>Report</strong> suspicious activity immediately</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6>70K Savings & Loans Management System</h6>
                    <p>&copy; 2026 70K Group. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Script -->
    <script src="js/script.js"></script>
</body>
</html>
