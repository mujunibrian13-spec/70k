<?php
/**
 * Loan Payment Page
 * Allows members to pay their active loans
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

$member_id = $_SESSION['member_id'];
$member = getMemberDetails($conn, $member_id);
$error = '';
$success = '';

// Get active loans for this member
$loans_query = "SELECT id, loan_amount, interest_rate, total_payable, status, loan_date, due_date, remaining_balance 
                FROM loans 
                WHERE member_id = ? AND status IN ('active', 'approved')
                ORDER BY loan_date DESC";
$loans_stmt = $conn->prepare($loans_query);
$loans_stmt->bind_param('i', $member_id);
$loans_stmt->execute();
$active_loans = $loans_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_loan'])) {
    $loan_id = intval($_POST['loan_id']);
    $payment_amount = floatval(isset($_POST['payment_amount']) ? $_POST['payment_amount'] : 0);
    $payment_method = sanitize(isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cash');
    $receipt_number = sanitize(isset($_POST['receipt_number']) ? $_POST['receipt_number'] : '');
    $notes = sanitize(isset($_POST['notes']) ? $_POST['notes'] : '');
    
    // Validate input
    if ($payment_amount <= 0) {
        $error = 'Payment amount must be greater than zero';
    } elseif (empty($payment_method)) {
        $error = 'Please select a payment method';
    } else {
        // Process payment
        $result = recordLoanPayment($conn, $loan_id, $payment_amount, $payment_method, $receipt_number, $notes);
        
        if ($result['success']) {
            $success = $result['message'] . "<br><small class='text-muted'><i class='fas fa-info-circle'></i> " . 
                      "Once approved by the administrator, interest will be distributed to all members.</small>";
            // Refresh loans
            $loans_stmt->execute();
            $active_loans = $loans_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    <title>Pay Loan - 70K Savings & Loans</title>
    
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
                        <a class="nav-link" href="loans.php">Loans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="pay_loan.php">Pay Loan</a>
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
                    <i class="fas fa-money-bill-wave"></i> Pay Loan
                </h1>
                <p class="mb-0">Make payments towards your loans to clear them</p>
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

            <!-- Pending Loan Payments -->
            <?php 
            $pending_query = "SELECT lp.*, l.loan_amount, l.remaining_balance FROM loan_payments lp 
                            JOIN loans l ON lp.loan_id = l.id 
                            WHERE lp.member_id = ? AND lp.status = 'pending'
                            ORDER BY lp.payment_date DESC";
            $pending_stmt = $conn->prepare($pending_query);
            $pending_stmt->bind_param('i', $member_id);
            $pending_stmt->execute();
            $pending_payments = $pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>
            
            <?php if (count($pending_payments) > 0): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">
                        <i class="fas fa-hourglass-half"></i> Pending Payment Approvals
                    </h5>
                    <p>You have <?php echo count($pending_payments); ?> payment(s) awaiting admin approval:</p>
                    <ul class="mb-0">
                        <?php foreach ($pending_payments as $pending): ?>
                            <li>
                                <strong><?php echo formatCurrency($pending['payment_amount']); ?></strong> 
                                submitted on <?php echo formatDate($pending['payment_date']); ?> 
                                (Status: <span class="badge badge-warning">Pending</span>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <hr>
                    <p class="mb-0">
                        <small><i class="fas fa-info-circle"></i> The administrator will review and approve your payment shortly. 
                        Once approved, your loan balance will be updated.</small>
                    </p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Active Loans -->
            <?php if (count($active_loans) > 0): ?>
            <div class="row mb-4">
                <?php foreach ($active_loans as $loan): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="fas fa-file-invoice-dollar"></i> Loan Payment</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label"><strong>Loan Status:</strong></label>
                                <span class="badge 
                                    <?php 
                                    echo ($loan['status'] === 'active') ? 'badge-warning' : 'badge-info';
                                    ?>
                                ">
                                    <?php echo ucfirst($loan['status']); ?>
                                </span>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Loan Amount</label>
                                    <p class="h5 text-danger"><?php echo formatCurrency($loan['loan_amount']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Interest Rate</label>
                                    <p class="h5"><?php echo ($loan['interest_rate'] * 100); ?>% monthly</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Total Payable</label>
                                    <p class="h5"><?php echo formatCurrency($loan['total_payable']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Remaining Balance</label>
                                    <p class="h5 text-success"><?php echo formatCurrency($loan['remaining_balance']); ?></p>
                                </div>
                            </div>

                            <div class="progress mb-4">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo ((($loan['total_payable'] - $loan['remaining_balance']) / $loan['total_payable']) * 100); ?>%">
                                </div>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">

                                <!-- Payment Amount -->
                                <div class="mb-3">
                                    <label for="payment_amount_<?php echo $loan['id']; ?>" class="form-label">Payment Amount</label>
                                    <input 
                                        type="number" 
                                        class="form-control" 
                                        id="payment_amount_<?php echo $loan['id']; ?>" 
                                        name="payment_amount" 
                                        min="0.01" 
                                        step="0.01"
                                        max="<?php echo $loan['remaining_balance']; ?>"
                                        placeholder="Enter payment amount"
                                        required
                                    >
                                    <small class="text-muted">Max: <?php echo formatCurrency($loan['remaining_balance']); ?></small>
                                </div>

                                <!-- Payment Method -->
                                <div class="mb-3">
                                    <label for="payment_method_<?php echo $loan['id']; ?>" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method_<?php echo $loan['id']; ?>" name="payment_method" required>
                                        <option value="">Select payment method</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="mobile_money">Mobile Money</option>
                                    </select>
                                </div>

                                <!-- Receipt Number -->
                                <div class="mb-3">
                                    <label for="receipt_<?php echo $loan['id']; ?>" class="form-label">Receipt/Reference Number</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="receipt_<?php echo $loan['id']; ?>" 
                                        name="receipt_number" 
                                        placeholder="(Optional)"
                                    >
                                </div>

                                <!-- Notes -->
                                <div class="mb-3">
                                    <label for="notes_<?php echo $loan['id']; ?>" class="form-label">Notes</label>
                                    <textarea 
                                        class="form-control" 
                                        id="notes_<?php echo $loan['id']; ?>" 
                                        name="notes" 
                                        rows="2" 
                                        placeholder="(Optional)"
                                    ></textarea>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" name="pay_loan" class="btn btn-primary w-100">
                                    <i class="fas fa-check"></i> Record Payment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
                    <h4 class="mt-3">No Active Loans</h4>
                    <p class="text-muted">You don't have any active loans to pay.</p>
                    <a href="loans.php" class="btn btn-primary mt-2">
                        <i class="fas fa-credit-card"></i> Apply for a Loan
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Information Alert -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle"></i> How Loan Payments Work
                        </h5>
                        <ul class="mb-0">
                            <li><strong>Submit Payment:</strong> Enter the amount you want to pay towards your loan and submit for approval</li>
                            <li><strong>Admin Approval:</strong> The administrator will review and approve your payment</li>
                            <li><strong>Balance Updated:</strong> Once approved, your loan balance will be updated and interest distributed</li>
                            <li><strong>Clear Your Loan:</strong> Once remaining balance reaches zero, your loan status changes to "CLEARED"</li>
                            <li><strong>Borrow Again:</strong> After clearing a loan, you can apply for another loan</li>
                            <li><strong>Payment Methods:</strong> Cash, Bank Transfer, or Mobile Money</li>
                            <li><strong>Interest Included:</strong> Make sure to pay the total payable amount (principal + interest)</li>
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
                        <li><a href="loans.php">Loans</a></li>
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
