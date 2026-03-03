<?php
/**
 * Loans Management Page
 * Allows members to apply for loans and view loan history
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if user is a member (only members can access this page)
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    // Admins should not access member loan page
    redirect('admin.php');
}

// Check if member_id exists in session
if (!isset($_SESSION['member_id']) || empty($_SESSION['member_id'])) {
    // If member_id is not set, redirect to login
    redirect('login.php');
}

$member_id = $_SESSION['member_id'];
$member = getMemberDetails($conn, $member_id);
$member_savings = getMemberSavings($conn, $member_id);
$error = '';
$success = '';

// Check if member has existing active or approved loans
$existing_loan_query = "SELECT id, loan_amount, total_payable, remaining_balance, status FROM loans 
                        WHERE member_id = ? AND status IN ('approved', 'active')
                        LIMIT 1";
$existing_stmt = $conn->prepare($existing_loan_query);
$existing_stmt->bind_param('i', $member_id);
$existing_stmt->execute();
$existing_loan_result = $existing_stmt->get_result();
$has_existing_loan = $existing_loan_result->num_rows > 0;
$existing_loan = $has_existing_loan ? $existing_loan_result->fetch_assoc() : null;

// Check if member can borrow
$can_borrow = hasMandatorySavings($conn, $member_id) && !$has_existing_loan;
$max_borrowable = getMaxBorrowableAmount($conn, $member_id);

// Handle new loan application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_loan'])) {
    if ($has_existing_loan) {
        $error = 'You cannot apply for a new loan while you have an existing ' . $existing_loan['status'] . ' loan. Please clear your current loan first by paying it off.';
    } elseif (!$can_borrow) {
        $error = 'You must meet the mandatory savings requirement to borrow';
    } else {
        $loan_amount = floatval(isset($_POST['loan_amount']) ? $_POST['loan_amount'] : 0);
        $purpose = sanitize(isset($_POST['purpose']) ? $_POST['purpose'] : '');
        $months = intval(isset($_POST['months']) ? $_POST['months'] : 1);
        
        // Validate input
        if ($loan_amount <= 0) {
            $error = 'Loan amount must be greater than zero';
        } elseif ($loan_amount > $max_borrowable) {
            $error = 'Loan amount cannot exceed ' . formatCurrency($max_borrowable) . ' (available group savings pool)';
        } elseif (empty($purpose)) {
            $error = 'Please provide a purpose for the loan';
        } else {
            // Calculate due date
            $loan_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime($loan_date . ' +' . $months . ' months'));
            
            // Calculate total payable (with 2% monthly interest)
            $total_payable = $loan_amount;
            for ($i = 0; $i < $months; $i++) {
                $total_payable += $total_payable * LOAN_INTEREST_RATE;
            }
            
            // Insert loan application
            $query = "INSERT INTO loans (member_id, loan_amount, interest_rate, total_payable, status, purpose, loan_date, due_date, remaining_balance) 
                      VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?)";
            
            $interest_rate = LOAN_INTEREST_RATE;
            $remaining_balance = $total_payable;
            $stmt = $conn->prepare($query);
            $stmt->bind_param('idddsssd', $member_id, $loan_amount, $interest_rate, $total_payable, $purpose, $loan_date, $due_date, $remaining_balance);
            
            if ($stmt->execute()) {
                // Log transaction
                logTransaction($conn, $member_id, 'loan', $loan_amount, "Loan application for {$purpose}");
                
                $success = "Loan application submitted successfully! Your request is pending admin approval.";
            } else {
                $error = 'Failed to submit loan application. Please try again.';
            }
        }
    }
}

// Get member's loan history
$query = "SELECT id, loan_amount, interest_rate, status, loan_date, due_date, remaining_balance FROM loans WHERE member_id = ? ORDER BY loan_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$loan_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate loan statistics
$active_loans = 0;
$total_borrowed = 0;
foreach ($loan_history as $loan) {
    if ($loan['status'] === 'active' || $loan['status'] === 'approved') {
        $active_loans++;
        $total_borrowed += $loan['loan_amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loans Management - 70K Savings & Loans</title>
    
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
                        <a class="nav-link active" href="loans.php">Loans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
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
                    <i class="fas fa-credit-card"></i> Loans Management
                </h1>
                <p class="mb-0">Apply for loans and track your borrowing history</p>
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

            <!-- Existing Loan Alert -->
            <?php if ($has_existing_loan): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Existing Loan Pending</strong><br>
                    You have an existing <?php echo ucfirst($existing_loan['status']); ?> loan with a remaining balance of <?php echo formatCurrency($existing_loan['remaining_balance']); ?>. 
                    You cannot apply for a new loan until this loan is cleared.
                    <a href="pay_loan.php" class="btn btn-sm btn-danger ms-2">
                        <i class="fas fa-money-bill-wave"></i> Pay Loan
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Eligibility Alert -->
            <?php if (!$can_borrow && !$has_existing_loan): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Not Eligible to Borrow</strong><br>
                    You must save at least <?php echo formatCurrency(MANDATORY_SAVINGS); ?> to be eligible for loans. 
                    Current savings: <?php echo formatCurrency($member_savings); ?>
                    <a href="savings.php" class="btn btn-sm btn-warning ms-2">
                        <i class="fas fa-plus"></i> Add Savings
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Row -->
            <div class="row mb-4">
                <!-- Total Borrowed Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card danger">
                        <div class="stat-card-label">Total Borrowed</div>
                        <div class="stat-card-value text-danger">
                            <?php echo formatCurrency($total_borrowed); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Active loans
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                </div>

                <!-- Active Loans Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card info">
                        <div class="stat-card-label">Active Loans</div>
                        <div class="stat-card-value">
                            <?php echo $active_loans; ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Currently active
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                </div>

                <!-- Monthly Interest Rate Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-card-label">Interest Rate</div>
                        <div class="stat-card-value">
                            <?php echo (LOAN_INTEREST_RATE * 100); ?>%
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Per month
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>

                <!-- Borrowing Limit Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-card-label">Max Borrowing</div>
                        <div class="stat-card-value text-success">
                            <?php echo formatCurrency($max_borrowable); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Group savings available
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Row -->
            <div class="row">
                <!-- Apply for Loan Form Column -->
                <div class="col-lg-5 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-hand-holding-dollar"></i> Apply for Loan</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate <?php echo !$can_borrow ? 'disabled' : ''; ?>>
                                <!-- Loan Amount Field -->
                                <div class="mb-3">
                                    <label for="loan_amount" class="form-label">Loan Amount (UGX)</label>
                                    <input 
                                        type="number" 
                                        class="form-control" 
                                        id="loan_amount" 
                                        name="loan_amount" 
                                        min="10000" 
                                        step="1000" 
                                        max="<?php echo $max_borrowable; ?>"
                                        placeholder="Enter loan amount"
                                        <?php echo !$can_borrow ? 'disabled' : ''; ?>
                                        required
                                    >
                                    <small class="text-muted">
                                        Max: <?php echo formatCurrency($max_borrowable); ?>
                                    </small>
                                </div>

                                <!-- Loan Duration Field -->
                                <div class="mb-3">
                                    <label for="months" class="form-label">Loan Duration (Months)</label>
                                    <input 
                                        type="number" 
                                        class="form-control" 
                                        id="months" 
                                        name="months" 
                                        min="1" 
                                        max="24" 
                                        value="12"
                                        <?php echo !$can_borrow ? 'disabled' : ''; ?>
                                        required
                                    >
                                    <small class="text-muted">1 to 24 months</small>
                                </div>

                                <!-- Loan Purpose Field -->
                                <div class="mb-3">
                                    <label for="purpose" class="form-label">Purpose of Loan</label>
                                    <textarea 
                                        class="form-control" 
                                        id="purpose" 
                                        name="purpose" 
                                        rows="3" 
                                        placeholder="Explain why you need this loan"
                                        <?php echo !$can_borrow ? 'disabled' : ''; ?>
                                        required
                                    ></textarea>
                                </div>

                                <!-- Info Alert -->
                                <div class="alert alert-info alert-sm mb-3">
                                    <small>
                                        <i class="fas fa-info-circle"></i> 
                                        Your interest on this loan helps fund monthly distributions to all members
                                    </small>
                                </div>

                                <!-- Submit Button -->
                                <button 
                                    type="submit" 
                                    name="apply_loan" 
                                    class="btn btn-primary w-100"
                                    <?php echo !$can_borrow ? 'disabled' : ''; ?>
                                    title="<?php echo $has_existing_loan ? 'You must clear your existing loan first' : (!hasMandatorySavings($conn, $member_id) ? 'You must meet mandatory savings requirement' : ''); ?>"
                                >
                                    <i class="fas fa-paper-plane"></i> Submit Application
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Loan History Column -->
                <div class="col-lg-7 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Loan History</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($loan_history) > 0): ?>
                                            <?php foreach ($loan_history as $loan): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo formatDate($loan['loan_date']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="text-danger">
                                                            -<?php echo formatCurrency($loan['loan_amount']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php echo formatDate($loan['due_date']); ?>
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
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    No loans yet
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Alert -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle"></i> How Loans Work
                        </h5>
                        <ul class="mb-0">
                            <li>You must save at least <?php echo formatCurrency(MANDATORY_SAVINGS); ?> to be eligible for loans</li>
                            <li>You can only have ONE active/approved loan at a time - clear your current loan before applying for a new one</li>
                            <li>Maximum loan amount: Limited by group savings pool (all members' savings - total loans already disbursed)</li>
                            <li>You can borrow more than your individual savings, as long as group savings are available</li>
                            <li>Interest rate: <?php echo (LOAN_INTEREST_RATE * 100); ?>% per month</li>
                            <li>Once you clear a loan (pay principal + interest), you can immediately borrow again</li>
                            <li>Interest from loans is distributed to all members based on their savings ratio</li>
                            <li>All loan applications require admin approval</li>
                            <li>You can choose loan duration from 1 to 24 months</li>
                        </ul>
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
                        <li><a href="savings.php">Savings</a></li>
                        <li><a href="loans.php">Loans</a></li>
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
