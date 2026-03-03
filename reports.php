<?php
/**
 * Reports Page
 * Displays detailed financial reports and analytics
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
$member_savings = getMemberSavings($conn, $member_id);

// Get summary statistics
$query = "SELECT COUNT(*) as savings_count, SUM(savings_amount) as total_savings FROM savings WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$savings_stats = $stmt->get_result()->fetch_assoc();

$query = "SELECT COUNT(*) as loan_count, SUM(loan_amount) as total_loan_amount FROM loans WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$loan_stats = $stmt->get_result()->fetch_assoc();

$query = "SELECT SUM(interest_earned) as total_interest FROM interest_distributions WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$interest_stats = $stmt->get_result()->fetch_assoc();

// Get transactions for the report
$query = "SELECT * FROM transactions WHERE member_id = ? ORDER BY transaction_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get interest distribution history
$query = "SELECT * FROM interest_distributions WHERE member_id = ? ORDER BY distribution_year DESC, distribution_month DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$interest_distributions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get all members' savings for report (excluding Ariganyira Alison)
$query = "SELECT m.id, m.full_name, m.savings_amount, 
          COUNT(s.id) as savings_count, SUM(s.savings_amount) as total_contributions
          FROM members m
          LEFT JOIN savings s ON m.id = s.member_id
          WHERE m.status = 'active' AND m.full_name != 'Ariganyira Alison'
          GROUP BY m.id, m.full_name, m.savings_amount
          ORDER BY m.savings_amount DESC";
$result = $conn->query($query);
$all_members_savings = $result->fetch_all(MYSQLI_ASSOC);

// Get weekly savings for current member (last 12 weeks)
$weekly_savings_query = "
    SELECT 
        WEEK(savings_date) as week_number,
        YEAR(savings_date) as year,
        SUM(savings_amount) as weekly_amount,
        DATE_FORMAT(MIN(savings_date), '%b %d') as week_start
    FROM savings 
    WHERE member_id = ? AND savings_date >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
    GROUP BY YEAR(savings_date), WEEK(savings_date)
    ORDER BY YEAR(savings_date), WEEK(savings_date)
";
$weekly_stmt = $conn->prepare($weekly_savings_query);
$weekly_stmt->bind_param('i', $member_id);
$weekly_stmt->execute();
$weekly_savings = $weekly_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Convert weekly data to JSON for chart
$weekly_labels = array();
$weekly_data = array();
$week_colors = array();
$colors = array('#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56');

foreach ($weekly_savings as $index => $week) {
    $weekly_labels[] = $week['week_start'] . ' (W' . $week['week_number'] . ')';
    $weekly_data[] = floatval($week['weekly_amount']);
    $week_colors[] = $colors[$index % count($colors)];
}

$weekly_labels_json = json_encode($weekly_labels);
$weekly_data_json = json_encode($weekly_data);
$week_colors_json = json_encode($week_colors);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - 70K Savings & Loans</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
                        <a class="nav-link active" href="reports.php">Reports</a>
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
                         <h1 class="mb-2">
                             <i class="fas fa-chart-bar"></i> Financial Reports
                         </h1>
                         <p class="mb-0">View your comprehensive financial statements and history</p>
                     </div>
                 </div>
             </div>
         </div>

        <div class="container">
            <!-- Summary Statistics Row -->
            <div class="row mb-4">
                <!-- Total Savings Contributed -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-card-label">Total Saved</div>
                        <div class="stat-card-value text-success">
                            <?php echo formatCurrency(isset($savings_stats['total_savings']) ? $savings_stats['total_savings'] : 0); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <?php echo isset($savings_stats['savings_count']) ? $savings_stats['savings_count'] : 0; ?> contributions
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Borrowed -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card danger">
                        <div class="stat-card-label">Total Borrowed</div>
                        <div class="stat-card-value text-danger">
                            <?php echo formatCurrency(isset($loan_stats['total_loan_amount']) ? $loan_stats['total_loan_amount'] : 0); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <?php echo isset($loan_stats['loan_count']) ? $loan_stats['loan_count'] : 0; ?> loans
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Interest Earned -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card info">
                        <div class="stat-card-label">Interest Earned</div>
                        <div class="stat-card-value text-info">
                            <?php echo formatCurrency(isset($interest_stats['total_interest']) ? $interest_stats['total_interest'] : 0); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            From group loans
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>

                <!-- Net Gain/Loss -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-card-label">Net Position</div>
                        <div class="stat-card-value">
                            <?php 
                            $net = (isset($savings_stats['total_savings']) ? $savings_stats['total_savings'] : 0) + (isset($interest_stats['total_interest']) ? $interest_stats['total_interest'] : 0) - (isset($loan_stats['total_loan_amount']) ? $loan_stats['total_loan_amount'] : 0);
                            echo formatCurrency($net);
                            ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Total position
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button" role="tab">
                        <i class="fas fa-exchange-alt"></i> All Transactions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab">
                        <i class="fas fa-users"></i> Member Savings
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
                <!-- Transactions Tab -->
                <div class="tab-pane fade show active" id="transactions" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-exchange-alt"></i> Transaction History</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="transactionsTable">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $running_balance = 0;
                                        if (count($transactions) > 0):
                                            foreach ($transactions as $transaction):
                                                $amount = $transaction['amount'];
                                                if (in_array($transaction['transaction_type'], array('savings', 'interest_earned', 'interest_distributed'))) {
                                                    $running_balance += $amount;
                                                    $sign = '+';
                                                    $color = 'text-success';
                                                } else {
                                                    $running_balance -= $amount;
                                                    $sign = '-';
                                                    $color = 'text-danger';
                                                }
                                                
                                                // Determine badge type based on transaction type
                                                $badge_class = 'badge-primary';
                                                $type_label = ucfirst(str_replace('_', ' ', $transaction['transaction_type']));
                                                if ($transaction['transaction_type'] === 'interest_earned') {
                                                    $badge_class = 'badge-success';
                                                    $type_label = 'Profit';
                                                } elseif ($transaction['transaction_type'] === 'savings') {
                                                    $badge_class = 'badge-info';
                                                } elseif ($transaction['transaction_type'] === 'loan') {
                                                    $badge_class = 'badge-danger';
                                                }
                                        ?>
                                        <tr>
                                            <td><?php echo formatDate($transaction['transaction_date']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $type_label; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                            <td class="<?php echo $color; ?>">
                                                <strong><?php echo $sign . formatCurrency($amount); ?></strong>
                                            </td>
                                            <td><?php echo formatCurrency($running_balance); ?></td>
                                        </tr>
                                        <?php 
                                            endforeach;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No transactions yet
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-secondary" onclick="printTable('transactionsTable')">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="exportTableToCSV('transactionsTable', 'transactions.csv')">
                                <i class="fas fa-download"></i> Export CSV
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Member Savings Tab -->
                <div class="tab-pane fade" id="members" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> All Members Savings</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="membersTable">
                                    <thead>
                                        <tr>
                                            <th>Member Name</th>
                                            <th class="text-center">Total Savings</th>
                                            <th class="text-center">Contributions</th>
                                            <th class="text-center">Avg per Contribution</th>
                                            <th class="text-center">% of Pool</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_pool = 0;
                                        foreach ($all_members_savings as $member) {
                                            $total_pool += $member['savings_amount'];
                                        }
                                        
                                        if (count($all_members_savings) > 0):
                                            foreach ($all_members_savings as $m):
                                                $pool_percentage = $total_pool > 0 ? ($m['savings_amount'] / $total_pool) * 100 : 0;
                                                $avg_contribution = $m['savings_count'] > 0 ? $m['total_contributions'] / $m['savings_count'] : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($m['full_name']); ?></strong>
                                            </td>
                                            <td class="text-center text-success">
                                                <strong><?php echo formatCurrency($m['savings_amount']); ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <?php echo $m['savings_count'] ? $m['savings_count'] : 0; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo formatCurrency($avg_contribution); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo number_format($pool_percentage, 2); ?>%
                                            </td>
                                        </tr>
                                        <?php 
                                            endforeach;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No member savings data
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>Total Pool</th>
                                            <th class="text-center text-success">
                                                <strong><?php echo formatCurrency($total_pool); ?></strong>
                                            </th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-secondary" onclick="printTable('membersTable')">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="exportTableToCSV('membersTable', 'member-savings.csv')">
                                <i class="fas fa-download"></i> Export CSV
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Interest Distribution Tab -->
                <div class="tab-pane fade" id="interest" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-percentage"></i> Interest Distribution History</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="interestTable">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Your Savings Ratio</th>
                                            <th>Total Pool Interest</th>
                                            <th>Your Share</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (count($interest_distributions) > 0):
                                            foreach ($interest_distributions as $distribution):
                                        ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?php echo date('F Y', mktime(0, 0, 0, $distribution['distribution_month'], 1, $distribution['distribution_year'])); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php echo number_format($distribution['savings_ratio'] * 100, 2); ?>%
                                            </td>
                                            <td>
                                                <?php echo formatCurrency($distribution['total_monthly_interest']); ?>
                                            </td>
                                            <td>
                                                <span class="text-success">
                                                    <strong><?php echo formatCurrency($distribution['interest_earned']); ?></strong>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $distribution['status'] === 'distributed' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($distribution['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php 
                                            endforeach;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No interest distributions yet
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-secondary" onclick="printTable('interestTable')">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="exportTableToCSV('interestTable', 'interest-distribution.csv')">
                                <i class="fas fa-download"></i> Export CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Savings Pie Chart -->
            <div class="row mt-4">
                <div class="col-lg-6 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-pie"></i> Weekly Savings Distribution (Last 12 Weeks)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($weekly_savings) > 0): ?>
                                <canvas id="weeklySavingsChart" style="max-height: 400px;"></canvas>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    No weekly savings data available for the last 12 weeks
                                </div>
                            <?php endif; ?>
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
                        <li><a href="reports.php">Reports</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Weekly Savings Chart Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (count($weekly_savings) > 0): ?>
            try {
                const weeklySavingsCtx = document.getElementById('weeklySavingsChart');
                if (weeklySavingsCtx) {
                    const weeklyLabels = <?php echo json_encode(array_column($weekly_savings, 'week_start')); ?>;
                    const weeklyData = <?php echo json_encode(array_map('floatval', array_column($weekly_savings, 'weekly_amount'))); ?>;
                    const weeklyNumbers = <?php echo json_encode(array_column($weekly_savings, 'week_number')); ?>;
                    
                    // Create labels with week numbers
                    const labels = weeklyLabels.map((label, index) => label + ' (W' + weeklyNumbers[index] + ')');
                    const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56'];
                    const backgroundColors = colors.slice(0, labels.length);
                    
                    new Chart(weeklySavingsCtx, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: weeklyData,
                                backgroundColor: backgroundColors,
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = parseInt(context.parsed).toLocaleString('en-US');
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            return label + ': UGX ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('Error rendering weekly savings chart:', e);
            }
            <?php endif; ?>
        });
    </script>
    
    <!-- Custom Script -->
    <script src="js/script.js"></script>
</body>
</html>
