<?php
/**
 * Edit Member Profile Page
 * Allows admins to update member information
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$error = '';
$success = '';
$member = null;

// Get member_id from URL parameter
$member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;

if ($member_id === 0) {
    $error = 'No member selected. Please select a member to edit.';
} else {
    // Fetch member details
    $query = "SELECT * FROM members WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error = 'Member not found.';
    } else {
        $member = $result->fetch_assoc();
    }
}

// Handle member deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_member'])) {
    $delete_id = intval($_POST['delete_id']);
    
    // Additional safety check
    if ($delete_id !== $member_id) {
        $error = 'Invalid member selection for deletion.';
    } else {
        // Start transaction for safe deletion
        $conn->begin_transaction();
        
        try {
            $deleted_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            
            // First, backup member data to deleted_members table
            $member_data = json_encode($member);
            $backup_query = "INSERT INTO deleted_members (member_id, full_name, email, phone, national_id, address, savings_amount, status, date_joined, deleted_by, member_data) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $backup_stmt = $conn->prepare($backup_query);
            $nin_value = isset($member['nin']) ? $member['nin'] : '';
            $backup_stmt->bind_param(
                'isssssdssii', 
                $delete_id,
                $member['full_name'],
                $member['email'],
                $member['phone'],
                $nin_value,
                $member['address'],
                $member['savings_amount'],
                $member['status'],
                $member['date_joined'],
                $deleted_by,
                $member_data
            );
            $backup_stmt->execute();
            
            // Log deletion
            $log_query = "INSERT INTO deletion_log (member_id, member_email, member_name, deleted_by, reason) 
                         VALUES (?, ?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_query);
            $reason = 'Admin deletion';
            $log_stmt->bind_param('issss', $delete_id, $member['email'], $member['full_name'], $deleted_by, $reason);
            $log_stmt->execute();
            
            // Delete member's savings records
            $delete_savings = "DELETE FROM savings WHERE member_id = ?";
            $stmt = $conn->prepare($delete_savings);
            $stmt->bind_param('i', $delete_id);
            $stmt->execute();
            
            // Delete member's loan records
            $delete_loans = "DELETE FROM loans WHERE member_id = ?";
            $stmt = $conn->prepare($delete_loans);
            $stmt->bind_param('i', $delete_id);
            $stmt->execute();
            
            // Delete member's transaction records
            $delete_transactions = "DELETE FROM transactions WHERE member_id = ?";
            $stmt = $conn->prepare($delete_transactions);
            $stmt->bind_param('i', $delete_id);
            $stmt->execute();
            
            // Delete member's interest records
            $delete_interest = "DELETE FROM member_interest WHERE member_id = ?";
            $stmt = $conn->prepare($delete_interest);
            $stmt->bind_param('i', $delete_id);
            $stmt->execute();
            
            // Finally delete the member
            $delete_member = "DELETE FROM members WHERE id = ?";
            $stmt = $conn->prepare($delete_member);
            $stmt->bind_param('i', $delete_id);
            $stmt->execute();
            
            // Commit the transaction
            $conn->commit();
            
            // Redirect to admin dashboard with success message
            $_SESSION['delete_success'] = 'Member deleted successfully. You can undo this action for the next 24 hours.';
            header('Location: admin.php');
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error = 'Failed to delete member. Error: ' . $e->getMessage();
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member'])) {
    $full_name = sanitize(isset($_POST['full_name']) ? $_POST['full_name'] : '');
    $email = sanitize(isset($_POST['email']) ? $_POST['email'] : '');
    $phone = sanitize(isset($_POST['phone']) ? $_POST['phone'] : '');
    $nin = sanitize(isset($_POST['nin']) ? $_POST['nin'] : '');
    $address = sanitize(isset($_POST['address']) ? $_POST['address'] : '');
    $status = sanitize(isset($_POST['status']) ? $_POST['status'] : 'active');
    
    // Validation
    if (empty($full_name)) {
        $error = 'Full name is required.';
    } else if (empty($email) || !validateEmail($email)) {
        $error = 'Valid email address is required.';
    } else if (empty($phone)) {
        $error = 'Phone number is required.';
    } else {
        // Check if email is already taken by another member
        $check_query = "SELECT id FROM members WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param('si', $email, $member_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Email already registered to another member.';
        } else {
            // Update member
            $update_query = "UPDATE members SET full_name = ?, email = ?, phone = ?, nin = ?, address = ?, status = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('ssssssi', $full_name, $email, $phone, $nin, $address, $status, $member_id);
            
            if ($update_stmt->execute()) {
                $success = 'Member profile updated successfully!';
                $member['full_name'] = $full_name;
                $member['email'] = $email;
                $member['phone'] = $phone;
                $member['nin'] = $nin;
                $member['address'] = $address;
                $member['status'] = $status;
            } else {
                $error = 'Failed to update member profile. Please try again.';
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
    <title>Edit Member Profile - 70K Savings & Loans</title>
    
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
                        <a class="nav-link" href="approve_payments.php">Approve Payments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_reset_member_password.php">Reset Password</a>
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
                    <i class="fas fa-edit"></i> Edit Member Profile
                </h1>
                <p class="mb-0">Update member information and account details</p>
            </div>
        </div>

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

            <?php if ($member): ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-user"></i> Member Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate>
                                <!-- Full Name -->
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

                                <!-- Email -->
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

                                <!-- Phone -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="phone" 
                                        name="phone" 
                                        value="<?php echo htmlspecialchars($member['phone']); ?>"
                                        required
                                    >
                                </div>

                                <!-- NIN -->
                                <div class="mb-3">
                                    <label for="nin" class="form-label">NIN (National ID Number)</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="nin" 
                                        name="nin" 
                                        value="<?php echo htmlspecialchars(isset($member['nin']) ? $member['nin'] : ''); ?>"
                                        maxlength="14"
                                    >
                                    <small class="text-muted">14 alphanumeric characters</small>
                                </div>

                                <!-- Address -->
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea 
                                        class="form-control" 
                                        id="address" 
                                        name="address" 
                                        rows="3"
                                    ><?php echo htmlspecialchars(isset($member['address']) ? $member['address'] : ''); ?></textarea>
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?php echo $member['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $member['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="suspended" <?php echo $member['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="mb-3">
                                    <button type="submit" name="update_member" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                    <a href="admin.php" class="btn btn-secondary btn-lg ms-2">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                    <button type="button" class="btn btn-danger btn-lg ms-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Delete Member
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Member Statistics -->
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Member Details</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-6">Member ID:</dt>
                                <dd class="col-sm-6"><strong><?php echo $member['id']; ?></strong></dd>
                                
                                <dt class="col-sm-6">Joined:</dt>
                                <dd class="col-sm-6"><?php echo formatDate($member['date_joined']); ?></dd>
                                
                                <dt class="col-sm-6">Total Savings:</dt>
                                <dd class="col-sm-6 text-success">
                                    <strong><?php echo formatCurrency($member['savings_amount']); ?></strong>
                                </dd>
                                
                                <dt class="col-sm-6">Status:</dt>
                                <dd class="col-sm-6">
                                    <span class="badge badge-<?php echo $member['status'] === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($member['status']); ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-key"></i> Other Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="admin_reset_member_password.php?member_id=<?php echo $member['id']; ?>" class="btn btn-warning btn-sm w-100 mb-2">
                                <i class="fas fa-key"></i> Reset Password
                            </a>
                            <a href="savings.php?member_id=<?php echo $member['id']; ?>" class="btn btn-info btn-sm w-100">
                                <i class="fas fa-piggy-bank"></i> Add/Update Savings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Please go to the <a href="admin.php">admin dashboard</a> and select a member to edit.
            </div>
            <?php endif; ?>
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
                        <li><a href="savings.php">Add Savings</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Delete Member
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($member): ?>
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-circle"></i> 
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>
                    <p>You are about to permanently delete the following member:</p>
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="mb-1"><strong><?php echo htmlspecialchars($member['full_name']); ?></strong></p>
                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($member['email']); ?></p>
                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($member['phone']); ?></p>
                        </div>
                    </div>
                    <p class="text-danger">
                        <i class="fas fa-info-circle"></i>
                        This will delete:
                    </p>
                    <ul class="text-danger">
                        <li>Member profile and account</li>
                        <li>All savings records</li>
                        <li>All loan records</li>
                        <li>All transaction history</li>
                        <li>All interest calculations</li>
                    </ul>
                    <p class="mt-3">
                        <strong>Are you absolutely sure?</strong> Type the member's email to confirm deletion:
                    </p>
                    <div class="alert alert-info mb-3">
                        <small><i class="fas fa-info-circle"></i> Type: <code><?php echo htmlspecialchars($member['email']); ?></code></small>
                    </div>
                    <input 
                        type="text" 
                        class="form-control form-control-lg" 
                        id="confirmEmail" 
                        placeholder="Type the email address here..."
                        autocomplete="off"
                    >
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled onclick="submitDeleteForm();">
                        <i class="fas fa-trash"></i> Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_member" value="1">
        <input type="hidden" id="deleteId" name="delete_id" value="">
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Script -->
    <script src="js/script.js"></script>
    
    <!-- Delete Confirmation Script -->
    <script>
        // Function to submit the delete form
        function submitDeleteForm() {
            const deleteForm = document.getElementById('deleteForm');
            const deleteId = document.getElementById('deleteId');
            const memberId = '<?php echo isset($member['id']) ? $member['id'] : '0'; ?>';
            
            if (deleteForm && deleteId && memberId !== '0') {
                deleteId.value = memberId;
                console.log('Submitting delete form with member ID:', memberId);
                deleteForm.submit();
            } else {
                alert('Error: Unable to delete member. Please refresh the page and try again.');
                console.error('Delete form error - Form:', deleteForm, 'DeleteId:', deleteId, 'MemberId:', memberId);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const confirmEmailInput = document.getElementById('confirmEmail');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const expectedEmail = '<?php echo isset($member['email']) ? $member['email'] : ''; ?>'; // No htmlspecialchars here
            
            console.log('Expected Email:', expectedEmail); // Debug
            console.log('Member ID:', '<?php echo isset($member['id']) ? $member['id'] : '0'; ?>');
            
            if (confirmEmailInput && confirmDeleteBtn) {
                confirmEmailInput.addEventListener('input', function() {
                    const inputValue = this.value.trim();
                    console.log('Input Value:', inputValue); // Debug
                    console.log('Match:', inputValue === expectedEmail); // Debug
                    
                    if (inputValue === expectedEmail) {
                        confirmDeleteBtn.disabled = false;
                        confirmDeleteBtn.classList.remove('disabled');
                        confirmDeleteBtn.style.opacity = '1';
                        confirmDeleteBtn.style.cursor = 'pointer';
                        console.log('Button enabled'); // Debug
                    } else {
                        confirmDeleteBtn.disabled = true;
                        confirmDeleteBtn.classList.add('disabled');
                        confirmDeleteBtn.style.opacity = '0.6';
                        confirmDeleteBtn.style.cursor = 'not-allowed';
                    }
                });
                
                // Also check on modal open
                const deleteModal = document.getElementById('deleteModal');
                if (deleteModal) {
                    deleteModal.addEventListener('shown.bs.modal', function() {
                        confirmEmailInput.focus();
                    });
                }
            } else {
                console.error('Delete form elements not found!');
            }
        });
    </script>
</body>
</html>
