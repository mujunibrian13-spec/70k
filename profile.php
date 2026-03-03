<?php
/**
 * Member Profile Page
 * Allows members to view and update their profile information
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if user is admin
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    redirect('admin.php');
}

// Check if member_id exists in session
if (!isset($_SESSION['member_id']) || empty($_SESSION['member_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$member_id = $_SESSION['member_id'];
$member = getMemberDetails($conn, $member_id);
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize(isset($_POST['full_name']) ? $_POST['full_name'] : '');
    $email = sanitize(isset($_POST['email']) ? $_POST['email'] : '');
    $phone = sanitize(isset($_POST['phone']) ? $_POST['phone'] : '');
    $nin = sanitize(isset($_POST['nin']) ? $_POST['nin'] : '');
    $identification_number = sanitize(isset($_POST['identification_number']) ? $_POST['identification_number'] : '');
    $address = sanitize(isset($_POST['address']) ? $_POST['address'] : '');
    $occupation = sanitize(isset($_POST['occupation']) ? $_POST['occupation'] : '');
    
    // Validate input
    if (empty($full_name) || empty($email) || empty($phone)) {
        $error = 'Full name, email, and phone are required';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email address';
    } elseif (!empty($nin) && strlen($nin) !== 14) {
        $error = 'NIN must be exactly 14 alphanumeric characters';
    } else {
        // Handle profile picture upload
        $profile_picture = isset($member['profile_picture']) ? $member['profile_picture'] : '';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_picture']['tmp_name'];
            $file_name = $_FILES['profile_picture']['name'];
            $file_size = $_FILES['profile_picture']['size'];
            
            // Get file type
            $file_type = '';
            if (function_exists('finfo_file')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $file_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);
            } elseif (function_exists('getimagesize')) {
                $image_info = @getimagesize($file_tmp);
                if ($image_info !== false) {
                    $file_type = $image_info['mime'];
                }
            } else {
                // Fallback: check file extension
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $ext_types = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif');
                $file_type = isset($ext_types[$ext]) ? $ext_types[$ext] : 'unknown';
            }
            
            // Validate file type and size
            $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Only JPEG, PNG, and GIF files are allowed';
            } elseif ($file_size > $max_size) {
                $error = 'File size must not exceed 5MB';
            } else {
                // Create uploads directory if it doesn't exist
                if (!is_dir('uploads/profiles')) {
                    mkdir('uploads/profiles', 0755, true);
                }
                
                // Generate unique filename
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $profile_picture = 'uploads/profiles/member_' . $member_id . '_' . time() . '.' . $ext;
                
                if (!move_uploaded_file($file_tmp, $profile_picture)) {
                    $error = 'Failed to upload profile picture';
                    $profile_picture = isset($member['profile_picture']) ? $member['profile_picture'] : '';
                }
            }
        }
        
        if (empty($error)) {
            // Update member profile
            $query = "UPDATE members SET full_name = ?, email = ?, phone = ?, nin = ?, identification_number = ?, address = ?, occupation = ?, profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssssssssi', $full_name, $email, $phone, $nin, $identification_number, $address, $occupation, $profile_picture, $member_id);
            
            if ($stmt->execute()) {
                // Update session
                $_SESSION['user_name'] = $full_name;
                $success = 'Profile updated successfully!';
                $member = getMemberDetails($conn, $member_id);
            } else {
                $error = 'Failed to update profile. Please try again.';
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
    <title>My Profile - 70K Savings & Loans</title>
    
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
                    <li class="nav-item">
                        <a class="nav-link" href="savings.php">Savings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loans.php">Loans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item active" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
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
                    <i class="fas fa-user-circle"></i> My Profile
                </h1>
                <p class="mb-0">Manage your account information</p>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alert-container"></div>

        <div class="container">
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

            <div class="row">
                <!-- Profile Information Column -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-user"></i> Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                                <!-- Full Name Field -->
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="full_name" 
                                        name="full_name" 
                                        value="<?php echo htmlspecialchars($member['full_name']); ?>"
                                        required
                                    >
                                </div>

                                <!-- Email Field -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="email" 
                                        name="email" 
                                        value="<?php echo htmlspecialchars($member['email']); ?>"
                                        required
                                    >
                                </div>

                                <!-- Phone Field -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input 
                                        type="tel" 
                                        class="form-control" 
                                        id="phone" 
                                        name="phone" 
                                        value="<?php echo htmlspecialchars($member['phone']); ?>"
                                        required
                                    >
                                </div>

                                <!-- NIN Field -->
                                <div class="mb-3">
                                    <label for="nin" class="form-label">National ID Number (NIN)</label>
                                    <small class="form-text text-muted d-block mb-2">14 alphanumeric characters</small>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="nin" 
                                        name="nin" 
                                        value="<?php echo htmlspecialchars(isset($member['nin']) ? $member['nin'] : ''); ?>"
                                        maxlength="14"
                                        placeholder="e.g., CM12A123456789"
                                    >
                                </div>

                                <!-- Identification Number Field -->
                                <div class="mb-3">
                                    <label for="identification_number" class="form-label">Identification Number</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="identification_number" 
                                        name="identification_number" 
                                        value="<?php echo htmlspecialchars(isset($member['identification_number']) ? $member['identification_number'] : ''); ?>"
                                    >
                                </div>

                                <!-- Address Field -->
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea 
                                        class="form-control" 
                                        id="address" 
                                        name="address" 
                                        rows="2"
                                    ><?php echo htmlspecialchars(isset($member['address']) ? $member['address'] : ''); ?></textarea>
                                </div>

                                <!-- Occupation Field -->
                                <div class="mb-3">
                                    <label for="occupation" class="form-label">Occupation</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="occupation" 
                                        name="occupation" 
                                        value="<?php echo htmlspecialchars(isset($member['occupation']) ? $member['occupation'] : ''); ?>"
                                    >
                                </div>

                                <!-- Profile Picture Field -->
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <div class="mb-2">
                                        <?php if (isset($member['profile_picture']) && !empty($member['profile_picture']) && file_exists($member['profile_picture'])): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="Profile Picture" style="max-width: 150px; max-height: 150px; border-radius: 5px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input 
                                        type="file" 
                                        class="form-control" 
                                        id="profile_picture" 
                                        name="profile_picture" 
                                        accept="image/jpeg,image/png,image/gif"
                                    >
                                    <small class="form-text text-muted">Accepted formats: JPEG, PNG, GIF. Maximum size: 5MB</small>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="btn-group" role="group">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Account Details Column -->
                <div class="col-lg-4">
                    <!-- Account Information Card -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Account Details</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-6">Member ID:</dt>
                                <dd class="col-sm-6">
                                    <code><?php echo $member['id']; ?></code>
                                </dd>

                                <dt class="col-sm-6">Member Since:</dt>
                                <dd class="col-sm-6">
                                    <?php echo formatDate($member['date_joined']); ?>
                                </dd>

                                <dt class="col-sm-6">Status:</dt>
                                <dd class="col-sm-6">
                                    <span class="badge badge-success">
                                        <?php echo ucfirst($member['status']); ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Financial Summary Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-wallet"></i> Financial Summary</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-6">Total Savings:</dt>
                                <dd class="col-sm-6">
                                    <strong class="text-success">
                                        <?php echo formatCurrency($member['savings_amount']); ?>
                                    </strong>
                                </dd>

                                <dt class="col-sm-6">Total Borrowed:</dt>
                                <dd class="col-sm-6">
                                    <strong class="text-danger">
                                        <?php echo formatCurrency(isset($member['total_borrowed']) ? $member['total_borrowed'] : 0); ?>
                                    </strong>
                                </dd>
                            </dl>
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
                        <li><a href="reports.php">Reports</a></li>
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
