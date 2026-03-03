<?php
/**
 * Admin Dashboard
 * Displays system statistics, member management, and loan approvals
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Get system statistics
$members_result = $conn->query("SELECT COUNT(*) as count FROM members WHERE status = 'active'");
$members_row = $members_result->fetch_assoc();
$total_members = $members_row['count'];

$savings_result = $conn->query("SELECT COALESCE(SUM(savings_amount), 0) as total FROM members");
$savings_row = $savings_result->fetch_assoc();
$total_savings = $savings_row['total'];

$loans_result = $conn->query("SELECT COALESCE(SUM(loan_amount), 0) as total FROM loans WHERE status IN ('active', 'approved')");
$loans_row = $loans_result->fetch_assoc();
$total_loans = $loans_row['total'];

$pending_result = $conn->query("SELECT COUNT(*) as count FROM loans WHERE status = 'pending'");
$pending_row = $pending_result->fetch_assoc();
$pending_loans = $pending_row['count'];

$pending_payments_result = $conn->query("SELECT COUNT(*) as count FROM loan_payments WHERE status = 'pending'");
$pending_payments_row = $pending_payments_result->fetch_assoc();
$pending_payments_count = $pending_payments_row['count'];

// Get all loan applications (pending, approved, rejected, active, completed)
$pending_loans_query = $conn->query("
    SELECT l.*, m.full_name, m.email, m.phone, m.savings_amount 
    FROM loans l
    JOIN members m ON l.member_id = m.id
    ORDER BY CASE 
        WHEN l.status = 'pending' THEN 1
        WHEN l.status = 'approved' THEN 2
        WHEN l.status = 'active' THEN 3
        WHEN l.status = 'completed' THEN 4
        WHEN l.status = 'rejected' THEN 5
    END, l.loan_date DESC
");

// Get all members
$all_members_query = $conn->query("
    SELECT id, full_name, email, phone, savings_amount, status, date_joined, profile_picture
    FROM members
    ORDER BY date_joined DESC
");

$error = '';
$success = '';

// Check for delete success message
if (isset($_SESSION['delete_success'])) {
    $success = $_SESSION['delete_success'];
    unset($_SESSION['delete_success']);
}

// Handle undo deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['undo_delete'])) {
    $deleted_member_id = intval($_POST['deleted_member_id']);
    $result = restoreDeletedMember($conn, $deleted_member_id, $_SESSION['user_id']);
    
    if ($result['success']) {
        $success = $result['message'] . ' You can now manage this member again.';
    } else {
        $error = $result['message'];
    }
}

// Get recently deleted members
$deleted_members = getDeletedMembers($conn);

// Get specific member data for Aliganyira Alson
$alison_member = null;
$alison_savings = 0;
$alison_loans = 0;
$alison_interest = 0;

$alison_query = "SELECT id, full_name, email, savings_amount FROM members WHERE full_name LIKE '%alison%' OR full_name LIKE '%alson%' LIMIT 1";
$alison_result = $conn->query($alison_query);
if ($alison_result && $alison_result->num_rows > 0) {
    $alison_member = $alison_result->fetch_assoc();
    $alison_savings = $alison_member['savings_amount'];
    
    // Get total interest distributed to this member (if table exists)
    $interest_query = "SELECT COALESCE(SUM(interest_amount), 0) as total FROM member_interest WHERE member_id = ?";
    $interest_stmt = $conn->prepare($interest_query);
    if ($interest_stmt) {
        $interest_stmt->bind_param('i', $alison_member['id']);
        $interest_stmt->execute();
        $interest_result = $interest_stmt->get_result();
        $interest_row = $interest_result->fetch_assoc();
        $alison_interest = $interest_row['total'];
    } else {
        $alison_interest = 0; // Table doesn't exist yet
    }
    
    // Get total loans taken by this member
    $loans_query = "SELECT COALESCE(SUM(loan_amount), 0) as total FROM loans WHERE member_id = ? AND status IN ('active', 'completed', 'approved')";
    $loans_stmt = $conn->prepare($loans_query);
    if ($loans_stmt) {
        $loans_stmt->bind_param('i', $alison_member['id']);
        $loans_stmt->execute();
        $loans_result = $loans_stmt->get_result();
        $loans_row = $loans_result->fetch_assoc();
        $alison_loans = $loans_row['total'];
    } else {
        $alison_loans = 0; // Query failed
    }
}

// Get total interest distributed to all members (if table exists)
$total_interest_distributed = 0;
$total_interest_query = "SELECT COALESCE(SUM(interest_amount), 0) as total FROM member_interest";
$total_interest_result = $conn->query($total_interest_query);
if ($total_interest_result) {
    $total_interest_row = $total_interest_result->fetch_assoc();
    $total_interest_distributed = $total_interest_row['total'];
}

// Handle loan approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_loan'])) {
    $loan_id = intval($_POST['loan_id']);
    $action = $_POST['approve_loan']; // This is either 'approve' or 'reject'
    
    // Check current loan status
    $check_query = "SELECT status FROM loans WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('i', $loan_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $error = "Loan not found.";
    } else {
        $loan = $check_result->fetch_assoc();
        $current_status = $loan['status'];
        
        // Check if trying to reject an already approved loan
        if ($action === 'reject' && $current_status !== 'pending') {
            $error = "Cannot reject a loan that is already " . $current_status . ". Only pending loans can be rejected.";
        } else if ($current_status !== 'pending') {
            $error = "This loan has already been processed (Status: " . ucfirst($current_status) . "). Cannot make changes.";
        } else {
            // Process the approval/rejection
            if ($action === 'approve') {
                $status = 'approved';
            } else {
                $status = 'rejected';
            }
            
            $query = "UPDATE loans SET status = ?, approved_by = ?, approval_date = CURDATE() WHERE id = ?";
            $stmt = $conn->prepare($query);
            $user_id = $_SESSION['user_id'];
            $stmt->bind_param('sii', $status, $user_id, $loan_id);
            
            if ($stmt->execute()) {
                $success = "Loan application " . $action . "ed successfully!";
            } else {
                $error = "Failed to process loan. Please try again.";
            }
        }
    }
}

// Handle monthly interest distribution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['distribute_interest'])) {
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    
    if (distributeInterestToMembers($conn, $month, $year)) {
        $success = "Interest distributed successfully for " . date('F Y', mktime(0, 0, 0, $month, 1, $year));
    } else {
        $error = "Failed to distribute interest. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - 70K Savings & Loans</title>
    
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
                        <a class="nav-link active" href="admin.php">Dashboard</a>
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
                    <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                </h1>
                <p class="mb-0">System overview, member management, and loan approvals</p>
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

            <!-- Undo Panel for Deleted Members -->
            <?php if (count($deleted_members) > 0): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h5 class="alert-heading mb-3">
                                <i class="fas fa-undo"></i> Recently Deleted Members
                            </h5>
                            <div class="row">
                                <?php foreach ($deleted_members as $deleted): ?>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="card border-warning">
                                        <div class="card-body p-2">
                                            <p class="mb-1"><strong><?php echo htmlspecialchars($deleted['full_name']); ?></strong></p>
                                            <p class="mb-1 text-muted small"><?php echo htmlspecialchars($deleted['email']); ?></p>
                                            <p class="mb-2 text-muted small">
                                                Deleted: <?php echo formatDate($deleted['deleted_at']); ?>
                                            </p>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="deleted_member_id" value="<?php echo $deleted['id']; ?>">
                                                <button type="submit" name="undo_delete" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-undo"></i> Restore
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Undo available for 24 hours after deletion
                            </small>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- System Statistics Row -->
            <div class="row mb-4">
                <!-- Total Members Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-card-label">Total Members</div>
                        <div class="stat-card-value text-success">
                            <?php echo $total_members; ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Active members
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <!-- Member Savings Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
                        <div class="stat-card-label">Member Savings</div>
                        <div class="stat-card-value" style="color: white;">
                            <?php 
                            $other_members_savings = $total_savings - $alison_savings;
                            echo formatCurrency($other_members_savings);
                            ?>
                        </div>
                        <small style="color: rgba(255,255,255,0.8);">
                            <i class="fas fa-info-circle"></i> Total from members
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Outstanding Loans Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card danger">
                        <div class="stat-card-label">Outstanding Loans</div>
                        <div class="stat-card-value text-danger">
                            <?php echo formatCurrency($total_loans); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Active & approved
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                </div>

                <!-- Pending Loans Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card warning">
                        <div class="stat-card-label">Pending Applications</div>
                        <div class="stat-card-value text-warning">
                            <?php echo $pending_loans; ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Loan applications
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>

                <!-- Pending Payments Card -->
                <div class="col-md-6 col-lg-3">
                    <a href="approve_payments.php" style="text-decoration: none; color: inherit;">
                        <div class="stat-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%); color: white;">
                            <div class="stat-card-label">Payment Approvals</div>
                            <div class="stat-card-value" style="color: white;">
                                <?php echo $pending_payments_count; ?>
                            </div>
                            <small style="color: rgba(255,255,255,0.8);">
                                <i class="fas fa-info-circle"></i> Pending approval
                            </small>
                            <div class="stat-card-icon">
                                <i class="fas fa-money-bill-check"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Member-Specific Cards Row -->
            <div class="row mb-4">
                <!-- Aliganyira Alson Savings Card -->
                <?php if ($alison_member): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100" style="border-left: 5px solid #667eea;">
                        <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                            <h5 class="mb-0" style="color: #667eea;">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($alison_member['full_name']); ?>
                            </h5>
                            <small class="text-muted"><?php echo htmlspecialchars($alison_member['email']); ?></small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <small class="text-muted">Savings Amount</small>
                                        <h4 class="text-success" style="margin: 5px 0;">
                                            <?php echo formatCurrency($alison_savings); ?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <small class="text-muted">Total Loans</small>
                                        <h4 class="text-danger" style="margin: 5px 0;">
                                            <?php echo formatCurrency($alison_loans); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <small class="text-muted">Interest Earned (from loans)</small>
                                <h4 class="text-info" style="margin: 5px 0;">
                                    <?php echo formatCurrency($alison_interest); ?>
                                </h4>
                            </div>
                            <a href="edit_member.php?member_id=<?php echo $alison_member['id']; ?>" class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Interest Distribution Card -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100" style="border-left: 5px solid #28a745;">
                        <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                            <h5 class="mb-0" style="color: #28a745;">
                                <i class="fas fa-percentage"></i> Interest Distribution
                            </h5>
                            <small class="text-muted">Group interest sharing</small>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Total Interest Distributed</small>
                                <h4 class="text-success" style="margin: 5px 0;">
                                    <?php echo formatCurrency($total_interest_distributed); ?>
                                </h4>
                            </div>
                            <hr>
                            <div class="alert alert-info" role="alert" style="margin-bottom: 0; font-size: 0.9rem;">
                                <strong><i class="fas fa-info-circle"></i> How it Works:</strong>
                                <ul class="mb-0" style="margin-top: 10px; margin-left: 20px;">
                                    <li>Members earn interest based on loan payments</li>
                                    <li>Interest distributed based on savings ratio</li>
                                    <li>All active members share from group loan interest</li>
                                    <li>No minimum savings required for interest</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interest Sharing Details Card -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100" style="border-left: 5px solid #ffc107;">
                        <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                            <h5 class="mb-0" style="color: #ffc107;">
                                <i class="fas fa-share-nodes"></i> Sharing Details
                            </h5>
                            <small class="text-muted">Member participation</small>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Members with Interest</small>
                                <h4 style="margin: 5px 0;">
                                    <?php 
                                    $member_count_query = "SELECT COUNT(DISTINCT member_id) as count FROM member_interest WHERE interest_amount > 0";
                                    $member_count_result = $conn->query($member_count_query);
                                    if ($member_count_result) {
                                        $member_count_row = $member_count_result->fetch_assoc();
                                        echo $member_count_row['count'];
                                    } else {
                                        echo "0";
                                    }
                                    ?>
                                </h4>
                            </div>
                            <hr>
                            <div class="alert alert-warning" role="alert" style="margin-bottom: 0; font-size: 0.9rem;">
                                <strong><i class="fas fa-star"></i> Key Point:</strong>
                                <p style="margin-top: 8px; margin-bottom: 0;">
                                    Members earn interest automatically when loans are taken and paid by any member, 
                                    regardless of whether they have savings. Interest is shared based on membership.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Group Savings Card (LAST CARD) -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100" style="border-left: 5px solid #28a745;">
                        <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                            <h5 class="mb-0" style="color: #28a745;">
                                <i class="fas fa-piggy-bank"></i> Group Savings
                            </h5>
                            <small class="text-muted">Total savings of all members</small>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Total Group Savings</small>
                                <h2 class="text-success" style="margin: 10px 0;">
                                    <?php echo formatCurrency($total_savings); ?>
                                </h2>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">Breakdown:</small>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Alison</small>
                                        <h5 style="margin: 5px 0; color: #667eea;">
                                            <?php echo formatCurrency($alison_savings); ?>
                                        </h5>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Others</small>
                                        <h5 style="margin: 5px 0; color: #17a2b8;">
                                            <?php echo formatCurrency($total_savings - $alison_savings); ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-success" role="alert" style="margin-bottom: 0; font-size: 0.9rem;">
                                <strong><i class="fas fa-check-circle"></i> Group Fund Health:</strong>
                                <p style="margin-top: 8px; margin-bottom: 0;">
                                    This is the total pool of savings available for group lending. 
                                    The larger this amount, the more loans can be approved.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Section -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Pending Loan Applications (<?php echo $pending_loans; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab">
                        <i class="fas fa-users"></i> All Members
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="interest-tab" data-bs-toggle="tab" data-bs-target="#interest" type="button" role="tab">
                        <i class="fas fa-percentage"></i> Interest Distribution
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Pending Loans Tab -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-file-alt"></i> Pending Loan Applications</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                         <tr>
                                             <th>Member</th>
                                             <th>Phone</th>
                                             <th>Amount</th>
                                             <th>Purpose</th>
                                             <th>Savings</th>
                                             <th>Max Allowed</th>
                                             <th>Status</th>
                                             <th>Action</th>
                                         </tr>
                                     </thead>
                                    <tbody>
                                         <?php if ($pending_loans_query->num_rows > 0): ?>
                                             <?php while ($loan = $pending_loans_query->fetch_assoc()): ?>
                                                 <tr>
                                                     <td>
                                                         <strong><?php echo htmlspecialchars($loan['full_name']); ?></strong><br>
                                                         <small class="text-muted"><?php echo htmlspecialchars($loan['email']); ?></small>
                                                     </td>
                                                     <td><?php echo htmlspecialchars($loan['phone']); ?></td>
                                                     <td>
                                                         <strong class="text-danger">
                                                             <?php echo formatCurrency($loan['loan_amount']); ?>
                                                         </strong>
                                                     </td>
                                                     <td><?php echo htmlspecialchars(substr($loan['purpose'], 0, 30)); ?></td>
                                                     <td>
                                                         <span class="text-success">
                                                             <?php echo formatCurrency($loan['savings_amount']); ?>
                                                         </span>
                                                     </td>
                                                     <td>
                                                         <?php echo formatCurrency($loan['savings_amount']); ?>
                                                     </td>
                                                     <td>
                                                         <span class="badge 
                                                             <?php 
                                                             switch($loan['status']) {
                                                                 case 'pending': echo 'badge-warning'; break;
                                                                 case 'approved': echo 'badge-info'; break;
                                                                 case 'active': echo 'badge-success'; break;
                                                                 case 'completed': echo 'badge-primary'; break;
                                                                 case 'rejected': echo 'badge-danger'; break;
                                                             }
                                                             ?>
                                                         ">
                                                             <?php echo ucfirst($loan['status']); ?>
                                                         </span>
                                                     </td>
                                                     <td>
                                                         <?php if ($loan['status'] === 'pending'): ?>
                                                             <form method="POST" style="display: inline;">
                                                                 <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                                 <button type="submit" name="approve_loan" value="approve" class="btn btn-sm btn-success" onclick="return confirm('Approve this loan?')">
                                                                     <i class="fas fa-check"></i> Approve
                                                                 </button>
                                                             </form>
                                                             <form method="POST" style="display: inline;">
                                                                 <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                                 <button type="submit" name="approve_loan" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this loan?')">
                                                                     <i class="fas fa-times"></i> Reject
                                                                 </button>
                                                             </form>
                                                         <?php else: ?>
                                                             <button class="btn btn-sm btn-secondary" disabled title="This loan has already been processed">
                                                                 <i class="fas fa-lock"></i> Locked
                                                             </button>
                                                         <?php endif; ?>
                                                     </td>
                                                 </tr>
                                             <?php endwhile; ?>
                                         <?php else: ?>
                                             <tr>
                                                 <td colspan="8" class="text-center text-muted py-4">
                                                     No loan applications
                                                 </td>
                                             </tr>
                                         <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members Tab -->
                <div class="tab-pane fade" id="members" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> All Members</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                         <tr>
                                             <th style="width: 50px;">Picture</th>
                                             <th>Name</th>
                                             <th>Email</th>
                                             <th>Phone</th>
                                             <th>Savings</th>
                                             <th>Status</th>
                                             <th>Joined</th>
                                             <th>Actions</th>
                                         </tr>
                                      </thead>
                                      <tbody>
                                          <?php if ($all_members_query->num_rows > 0): ?>
                                              <?php while ($member = $all_members_query->fetch_assoc()): ?>
                                                  <tr>
                                                      <td>
                                                          <?php if (isset($member['profile_picture']) && !empty($member['profile_picture']) && file_exists($member['profile_picture'])): ?>
                                                              <img src="<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                                          <?php else: ?>
                                                              <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #999;">
                                                                  <i class="fas fa-user"></i>
                                                              </div>
                                                          <?php endif; ?>
                                                      </td>
                                                      <td><strong><?php echo htmlspecialchars($member['full_name']); ?></strong></td>
                                                      <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                      <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                                      <td>
                                                          <span class="text-success">
                                                              <?php echo formatCurrency($member['savings_amount']); ?>
                                                          </span>
                                                      </td>
                                                      <td>
                                                          <span class="badge badge-success">
                                                              <?php echo ucfirst($member['status']); ?>
                                                          </span>
                                                      </td>
                                                      <td><?php echo formatDate($member['date_joined']); ?></td>
                                                      <td>
                                                          <div class="btn-group btn-group-sm" role="group">
                                                              <a href="edit_member.php?member_id=<?php echo $member['id']; ?>" class="btn btn-primary" title="Edit member profile">
                                                                  <i class="fas fa-edit"></i> Edit
                                                              </a>
                                                              <a href="edit_member.php?member_id=<?php echo $member['id']; ?>#deleteModal" class="btn btn-danger" title="Delete member" onclick="openDeleteModal(<?php echo $member['id']; ?>); return false;">
                                                                  <i class="fas fa-trash"></i> Delete
                                                              </a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                              <?php endwhile; ?>
                                          <?php else: ?>
                                              <tr>
                                                  <td colspan="8" class="text-center text-muted py-4">
                                                      No members found
                                                  </td>
                                              </tr>
                                          <?php endif; ?>
                                      </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interest Distribution Tab -->
                <div class="tab-pane fade" id="interest" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-percentage"></i> Monthly Interest Distribution</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <div class="col-md-4">
                                    <label for="month" class="form-label">Month</label>
                                    <select class="form-select" id="month" name="month" required>
                                        <option value="">Select month</option>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($i == date('n') ? 'selected' : ''); ?>>
                                                <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="">Select year</option>
                                        <?php for ($y = 2025; $y <= date('Y'); $y++): ?>
                                            <option value="<?php echo $y; ?>" <?php echo ($y == date('Y') ? 'selected' : ''); ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" name="distribute_interest" class="btn btn-primary w-100">
                                        <i class="fas fa-check"></i> Distribute Interest
                                    </button>
                                </div>
                            </form>
                            
                            <div class="alert alert-info mt-3" role="alert">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> This will calculate 2% monthly interest on all outstanding loans 
                                and distribute it to members based on their savings ratio.
                            </div>
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
</body>
</html>
