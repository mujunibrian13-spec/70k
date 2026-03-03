<?php
/**
 * Loan Payment Approval Page
 * Allows admin to review and approve/reject member loan payments
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$admin_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle payment approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $payment_id = intval($_POST['payment_id']);
    $action = sanitize($_POST['action']);
    
    if ($action === 'approve') {
        $result = approveLoanPayment($conn, $payment_id, $admin_id);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'reject') {
        // Get payment details
        $check_query = "SELECT status FROM loan_payments WHERE id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param('i', $payment_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $error = "Payment not found.";
        } else {
            $payment = $check_result->fetch_assoc();
            
            if ($payment['status'] !== 'pending') {
                $error = "Only pending payments can be rejected.";
            } else {
                // Mark payment as rejected (change status to 'rejected')
                $reject_query = "UPDATE loan_payments SET status = 'rejected', approved_by = ?, approval_date = NOW() WHERE id = ?";
                $reject_stmt = $conn->prepare($reject_query);
                $reject_stmt->bind_param('ii', $admin_id, $payment_id);
                
                if ($reject_stmt->execute()) {
                    $success = "Payment rejected successfully!";
                } else {
                    $error = "Failed to reject payment. Please try again.";
                }
            }
        }
    }
}

// Get pending loan payments
$pending_payments_query = "
    SELECT 
        lp.id,
        lp.loan_id,
        lp.member_id,
        lp.payment_amount,
        lp.payment_method,
        lp.receipt_number,
        lp.notes,
        lp.payment_date,
        lp.status,
        m.full_name,
        m.email,
        m.phone,
        l.loan_amount,
        l.total_payable,
        l.remaining_balance,
        l.status as loan_status
    FROM loan_payments lp
    JOIN members m ON lp.member_id = m.id
    JOIN loans l ON lp.loan_id = l.id
    ORDER BY CASE 
        WHEN lp.status = 'pending' THEN 1
        WHEN lp.status = 'approved' THEN 2
        WHEN lp.status = 'rejected' THEN 3
    END, lp.payment_date DESC
";

$payments_result = $conn->query($pending_payments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Loan Payments - 70K Savings & Loans</title>
    
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
                        <a class="nav-link active" href="approve_payments.php">Approve Payments</a>
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
                    <i class="fas fa-check-circle"></i> Approve Loan Payments
                </h1>
                <p class="mb-0">Review and approve member loan payments</p>
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

            <!-- Payments Table -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-list"></i> Loan Payment Requests</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Member</th>
                                    <th>Loan Amount</th>
                                    <th>Payment Amount</th>
                                    <th>Remaining Balance</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($payments_result->num_rows > 0): ?>
                                    <?php while ($payment = $payments_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong><br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($payment['email']); ?><br>
                                                    <?php echo htmlspecialchars($payment['phone']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong class="text-danger">
                                                    <?php echo formatCurrency($payment['loan_amount']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <strong class="text-warning">
                                                    <?php echo formatCurrency($payment['payment_amount']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="text-success">
                                                    <?php echo formatCurrency($payment['remaining_balance']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                    switch($payment['status']) {
                                                        case 'pending': echo 'badge-warning'; break;
                                                        case 'approved': echo 'badge-success'; break;
                                                        case 'rejected': echo 'badge-danger'; break;
                                                    }
                                                    ?>
                                                ">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($payment['status'] === 'pending'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" 
                                                                onclick="return confirm('Approve this payment of <?php echo formatCurrency($payment['payment_amount']); ?>?')">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Reject this payment?')">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled title="Payment already processed">
                                                        <i class="fas fa-lock"></i> Locked
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox"></i> No payment requests at this time
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Information Alert -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle"></i> Loan Payment Approval Process
                        </h5>
                        <ul class="mb-0">
                            <li><strong>Pending Payments:</strong> Members submit payment requests that require admin approval</li>
                            <li><strong>Approve Payment:</strong> Click approve to accept the payment and update the loan balance</li>
                            <li><strong>Reject Payment:</strong> Click reject if the payment cannot be processed</li>
                            <li><strong>Interest Distribution:</strong> Interest is automatically distributed to all members when a payment is approved</li>
                            <li><strong>Loan Status:</strong> When remaining balance reaches zero, the loan is automatically marked as cleared</li>
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
                    <h6>Admin Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="admin.php">Dashboard</a></li>
                        <li><a href="approve_payments.php">Approve Payments</a></li>
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
