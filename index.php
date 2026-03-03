<?php
/**
 * Main Dashboard Page
 * Displays overview of member account, savings, loans, and interest
 * Accessible to logged-in members only
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user information
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// If user is admin, show different dashboard
if (isAdmin()) {
    redirect('admin.php');
}

// Check if member_id exists in session
if (!isset($_SESSION['member_id']) || empty($_SESSION['member_id'])) {
    redirect('login.php');
}

// Get member details
$member_id = $_SESSION['member_id'];
$member = getMemberDetails($conn, $member_id);

// Get member's financial data
$member_savings = getMemberSavings($conn, $member_id);
$member_loans = getMemberLoanHistory($conn, $member_id);
$total_borrowed = 0;
$active_loans = 0;

// Calculate total borrowed and active loans
foreach ($member_loans as $loan) {
    if ($loan['status'] === 'active' || $loan['status'] === 'approved') {
        $total_borrowed += $loan['loan_amount'];
        $active_loans++;
    }
}

// Get savings ratio
$savings_ratio = getMemberSavingsRatio($conn, $member_id);

// Get recent transactions
$query = "SELECT * FROM transactions WHERE member_id = ? ORDER BY transaction_date DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$recent_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total interest earned this month (profit)
$current_month = date('n');
$current_year = date('Y');
$query = "SELECT COALESCE(SUM(interest_earned), 0) as total_interest FROM interest_distributions 
          WHERE member_id = ? AND distribution_month = ? AND distribution_year = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $member_id, $current_month, $current_year);
$stmt->execute();
$interest_result = $stmt->get_result()->fetch_assoc();
$monthly_interest = isset($interest_result['total_interest']) ? (float)$interest_result['total_interest'] : 0;

// Get total profit earned (all interest transactions)
$query = "SELECT COALESCE(SUM(amount), 0) as total_profit FROM transactions 
          WHERE member_id = ? AND transaction_type = 'interest_earned'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$profit_result = $stmt->get_result()->fetch_assoc();
$total_profit = isset($profit_result['total_profit']) ? (float)$profit_result['total_profit'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags for Responsiveness and Character Encoding -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="70K Savings & Loans Management System - Member Dashboard">
    
    <!-- Page Title -->
    <title>Member Dashboard - 70K Savings & Loans</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-piggy-bank"></i> 70K Savings & Loans
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loans.php">
                            <i class="fas fa-credit-card"></i> Loans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pay_loan.php">
                            <i class="fas fa-money-bill-wave"></i> Pay Loan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" style="gap: 8px;">
                            <?php if (isset($member['profile_picture']) && !empty($member['profile_picture']) && file_exists($member['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid white;">
                            <?php else: ?>
                                <i class="fas fa-user-circle"></i>
                            <?php endif; ?>
                            <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
                <div class="d-flex align-items-center gap-3 mb-3">
                    <?php if (isset($member['profile_picture']) && !empty($member['profile_picture']) && file_exists($member['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #667eea;">
                    <?php else: ?>
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; border: 3px solid #ddd; font-size: 2.5rem; color: #999;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="mb-2">Welcome, <?php echo htmlspecialchars($member['full_name']); ?>!</h1>
                        <p class="mb-0">Here's your account overview and financial summary</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Container for Notifications -->
        <div id="alert-container"></div>

        <!-- Dashboard Grid -->
        <div class="container">
            <!-- Financial Stats Row -->
            <div class="row mb-4">
                <!-- Total Savings Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-card-label">Total Savings</div>
                        <div class="stat-card-value text-success">
                            <?php echo formatCurrency($member_savings); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <?php 
                            if ($member_savings >= MANDATORY_SAVINGS) {
                                echo 'Mandatory savings met ✓';
                            } else {
                                echo 'Short by ' . formatCurrency(MANDATORY_SAVINGS - $member_savings);
                            }
                            ?>
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Profit Earned Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-card-label">Total Profit Earned</div>
                        <div class="stat-card-value text-success">
                            <?php echo formatCurrency($total_profit); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            All interest earned
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Borrowed Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card danger">
                        <div class="stat-card-label">Total Borrowed</div>
                        <div class="stat-card-value text-danger">
                            <?php echo formatCurrency($total_borrowed); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <?php echo $active_loans; ?> active loan(s)
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                </div>

                <!-- Savings Ratio Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card info">
                        <div class="stat-card-label">Your Savings Ratio</div>
                        <div class="stat-card-value">
                            <?php echo number_format($savings_ratio * 100, 2); ?>%
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Of total group savings
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>

                <!-- Monthly Profit Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-card-label">This Month Profit</div>
                        <div class="stat-card-value text-success">
                            <?php echo formatCurrency($monthly_interest); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Interest earned
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Recent Transactions Column -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Recent Transactions</h5>
                        </div>
                        <div class="card-body p-0">
                            <!-- Responsive Table -->
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($recent_transactions) > 0): ?>
                                            <?php foreach ($recent_transactions as $transaction): ?>
                                                <tr>
                                                    <td><?php echo formatDate($transaction['transaction_date']); ?></td>
                                                    <td>
                                                        <!-- Transaction Type Badge -->
                                                        <span class="badge 
                                                            <?php 
                                                            $type = $transaction['transaction_type'];
                                                            echo ($type === 'savings' || $type === 'interest_earned') 
                                                                ? 'badge-success' 
                                                                : 'badge-danger';
                                                            ?>
                                                        ">
                                                            <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                                    <td class="text-right">
                                                        <?php 
                                                        $is_credit = in_array($transaction['transaction_type'], array('savings', 'interest_earned', 'interest_distributed'));
                                                        $css_class = $is_credit ? 'text-success' : 'text-danger';
                                                        $sign = $is_credit ? '+' : '-';
                                                        ?>
                                                        <strong class="<?php echo $css_class; ?>">
                                                            <?php echo $sign . formatCurrency($transaction['amount']); ?>
                                                        </strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    No transactions yet
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="reports.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View All Transactions
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Column -->
                <div class="col-lg-4 mb-4">
                    <!-- Add Savings Card -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fas fa-plus-circle"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                <i class="fas fa-info-circle"></i> Savings are managed by the administrator.
                            </p>
                            <a href="loans.php" class="btn btn-primary w-100">
                                <i class="fas fa-hand-holding-dollar"></i> Apply for Loan
                            </a>
                        </div>
                    </div>

                    <!-- Account Information Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-user-card"></i> Account Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">Member Since:</dt>
                                <dd class="col-sm-7">
                                    <strong><?php echo formatDate($member['date_joined']); ?></strong>
                                </dd>

                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">
                                    <strong><?php echo htmlspecialchars($member['email']); ?></strong>
                                </dd>

                                <dt class="col-sm-5">Phone:</dt>
                                <dd class="col-sm-7">
                                    <strong><?php echo htmlspecialchars($member['phone']); ?></strong>
                                </dd>

                                <dt class="col-sm-5">Status:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge badge-success">
                                        <?php echo ucfirst($member['status']); ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        <div class="card-footer">
                            <a href="profile.php" class="btn btn-sm btn-secondary w-100">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information Row -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-lightbulb"></i> How the System Works
                        </h5>
                        <p class="mb-0">
                            The 70K Savings & Loans system automatically distributes interest earned from loans to all members based on their savings contribution ratio. 
                            The higher your savings relative to the group total, the larger your share of monthly interest income. 
                            Interest is distributed on the 1st of each month.
                        </p>
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
                        <li><a href="reports.php">Reports</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/script.js"></script>
</body>
</html>
