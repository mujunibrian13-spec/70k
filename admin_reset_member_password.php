<?php
/**
 * Admin Reset Member Password Page
 * Allows admin to reset member passwords
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$error = '';
$success = '';
$reset_member = null;

// Get all members
$members_query = "SELECT u.id, u.username, u.email, u.full_name, m.phone, m.status
                  FROM users u
                  LEFT JOIN members m ON u.id = m.user_id
                  WHERE u.role = 'member'
                  ORDER BY u.full_name ASC";
$members_result = $conn->query($members_query);

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = intval($_POST['user_id']);
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        // Call admin reset function
        $result = adminResetMemberPassword($conn, $user_id, $new_password);
        
        if ($result['success']) {
            $success = "Password reset successfully for {$result['username']}";
            $reset_member = $result['username'];
            // Refresh member list
            $members_result = $conn->query($members_query);
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
    <title>Reset Member Password - 70K Savings & Loans</title>
    
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
            <a class="navbar-brand" href="admin.php">
                <i class="fas fa-shield-alt"></i> Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_reset_member_password.php">Reset Member Password</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="approve_payments.php">Approve Payments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="savings.php">Add Savings</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
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
                    <i class="fas fa-key"></i> Reset Member Password
                </h1>
                <p class="mb-0">Reset a member's password when they cannot access their account</p>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-lg-8">
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

                    <!-- Password Reset Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="fas fa-lock"></i> Set New Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <!-- Member Selection -->
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Select Member</label>
                                    <select 
                                        class="form-select" 
                                        id="user_id" 
                                        name="user_id" 
                                        required
                                        onchange="showMemberDetails(this.value)"
                                    >
                                        <option value="">-- Choose a member --</option>
                                        <?php if ($members_result && $members_result->num_rows > 0): ?>
                                            <?php $members_result->data_seek(0); ?>
                                            <?php while ($member = $members_result->fetch_assoc()): ?>
                                                <option value="<?php echo $member['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($member['username']); ?>"
                                                        data-email="<?php echo htmlspecialchars($member['email']); ?>"
                                                        data-phone="<?php echo htmlspecialchars($member['phone']); ?>"
                                                        data-status="<?php echo htmlspecialchars($member['status']); ?>">
                                                    <?php echo htmlspecialchars($member['full_name'] . ' (' . $member['username'] . ')'); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Member Details -->
                                <div id="member-details" style="display: none; margin-bottom: 20px;">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Username:</strong> <span id="detail-username"></span></p>
                                                    <p><strong>Email:</strong> <span id="detail-email"></span></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Phone:</strong> <span id="detail-phone"></span></p>
                                                    <p><strong>Status:</strong> <span id="detail-status"></span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="confirm_password" 
                                        name="confirm_password" 
                                        placeholder="Confirm new password"
                                        required
                                    >
                                    <small class="text-muted">Must match the password above</small>
                                </div>

                                <!-- Confirmation Alert -->
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Warning:</strong> The selected member will need to use the new password to login. 
                                    Make sure to communicate the new password securely.
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" name="reset_password" class="btn btn-primary w-100" 
                                        onclick="return confirm('Are you sure you want to reset this member\\'s password?')">
                                    <i class="fas fa-check"></i> Reset Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Information Panel -->
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Important</h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Password must be at least <strong>6 characters</strong></li>
                                <li>Member will use new password to login</li>
                                <li>Communicate new password <strong>securely</strong></li>
                                <li>Consider a <strong>temporary password</strong> so member can change it later</li>
                                <li>Member can change password in their profile</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-shield-alt"></i> Security</h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>All password resets are <strong>logged</strong></li>
                                <li>Members should change password <strong>after reset</strong></li>
                                <li>Encourage <strong>strong passwords</strong></li>
                                <li>Do not share passwords via <strong>email</strong></li>
                                <li>Reset account access issues <strong>immediately</strong></li>
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
                    <h6>Admin Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="admin.php">Dashboard</a></li>
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

    <!-- Member Details Script -->
    <script>
    function showMemberDetails(userId) {
        const selectElement = document.getElementById('user_id');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const detailsDiv = document.getElementById('member-details');
        
        if (userId === '') {
            detailsDiv.style.display = 'none';
        } else {
            document.getElementById('detail-username').textContent = selectedOption.getAttribute('data-username');
            document.getElementById('detail-email').textContent = selectedOption.getAttribute('data-email');
            document.getElementById('detail-phone').textContent = selectedOption.getAttribute('data-phone');
            document.getElementById('detail-status').textContent = selectedOption.getAttribute('data-status');
            detailsDiv.style.display = 'block';
        }
    }
    </script>
</body>
</html>
