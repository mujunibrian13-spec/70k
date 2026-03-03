<?php
/**
 * Savings Management Page
 * Allows members to view and add savings contributions
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if user is admin - only admins can add savings
if (!isAdmin()) {
    redirect('index.php');
}

$error = '';
$success = '';
$warning = '';

// Get all members for dropdown
$members_query = "SELECT id, full_name, email FROM members WHERE status = 'active' ORDER BY full_name";
$members_result = $conn->query($members_query);
$members_list = $members_result->fetch_all(MYSQLI_ASSOC);

// Get member_id from GET parameter
$requested_member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;

// Default to requested member or first member
if ($requested_member_id > 0) {
    $member_id = $requested_member_id;
} else if (count($members_list) > 0) {
    $member_id = $members_list[0]['id'];
} else {
    $member_id = 0;
}

// Initialize variables with defaults
$member = null;
$current_savings = 0;
$week_savings = null;
$can_save = true;
$next_save_date = date('Y-m-d');

if ($member_id === 0) {
    $error = 'No members found. Please register members first.';
} else {
    $member = getMemberDetails($conn, $member_id);
    $current_savings = getMemberSavings($conn, $member_id);

    // Check if member already saved this week
    $week_savings = getCurrentWeekSavings($conn, $member_id);
    $can_save = !$week_savings;
    $next_save_date = getNextSavingsDate($conn, $member_id);
}

if (!$can_save && $week_savings) {
    $warning = 'You have already saved this week (' . formatDate($week_savings['savings_date']) . '). You can update your savings below or save again next week on ' . formatDate($next_save_date) . '.';
}

// Handle new savings submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_savings'])) {
    $savings_amount = floatval(isset($_POST['savings_amount']) ? $_POST['savings_amount'] : 0);
    $payment_method = sanitize(isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cash');
    $notes = sanitize(isset($_POST['notes']) ? $_POST['notes'] : '');
    
    // Validate input
    if ($savings_amount <= 0) {
        $error = 'Savings amount must be greater than zero';
    } else {
        if ($week_savings) {
            // Update existing week's savings
            $old_amount = $week_savings['savings_amount'];
            $amount_diff = $savings_amount - $old_amount;
            $receipt_number = $week_savings['receipt_number']; // Keep original receipt number
            
            $update_query = "UPDATE savings SET savings_amount = ?, payment_method = ?, notes = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('dssi', $savings_amount, $payment_method, $notes, $week_savings['id']);
            
            if ($stmt->execute()) {
                // Update member's total savings with difference
                $update_member = "UPDATE members SET savings_amount = savings_amount + ?, last_savings_date = CURDATE() WHERE id = ?";
                $update_stmt = $conn->prepare($update_member);
                $update_stmt->bind_param('di', $amount_diff, $member_id);
                $update_stmt->execute();
                
                // Log transaction for the difference
                if ($amount_diff != 0) {
                    logTransaction($conn, $member_id, 'savings', $amount_diff, "Savings updated via {$payment_method}");
                }
                
                $success = "Savings updated to " . formatCurrency($savings_amount) . " successfully!";
                $current_savings += $amount_diff;
                $week_savings = getCurrentWeekSavings($conn, $member_id); // Refresh
            } else {
                $error = 'Failed to update savings. Please try again.';
            }
        } else {
            // Insert new savings record
            $receipt_number = generateReceiptNumber();
            $query = "INSERT INTO savings (member_id, savings_amount, savings_type, payment_method, receipt_number, notes, savings_date) 
                      VALUES (?, ?, 'voluntary', ?, ?, ?, CURDATE())";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param('idsss', $member_id, $savings_amount, $payment_method, $receipt_number, $notes);
            
            if ($stmt->execute()) {
                // Update member's total savings
                $update_query = "UPDATE members SET savings_amount = savings_amount + ?, last_savings_date = CURDATE() WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('di', $savings_amount, $member_id);
                $update_stmt->execute();
                
                // Log transaction
                logTransaction($conn, $member_id, 'savings', $savings_amount, "Savings contribution via {$payment_method}");
                
                $success = "Savings of " . formatCurrency($savings_amount) . " added successfully!";
                $current_savings += $savings_amount;
                $week_savings = getCurrentWeekSavings($conn, $member_id); // Refresh
                $can_save = false; // Can't save again this week
            } else {
                $error = 'Failed to add savings. Please try again.';
            }
        }
    }
}

// Get member's savings history
$query = "SELECT * FROM savings WHERE member_id = ? ORDER BY savings_date DESC LIMIT 20";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$savings_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate savings statistics
$mandatory_remaining = MANDATORY_SAVINGS - $current_savings;
$mandatory_met = $mandatory_remaining <= 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Management - 70K Savings & Loans</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Select2 CSS for searchable dropdown -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
                        <a class="nav-link active" href="savings.php">Savings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loans.php">Loans</a>
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
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">
                            <i class="fas fa-save"></i> Add Member Savings
                        </h1>
                        <p class="mb-0">Admin: Add and manage member savings contributions</p>
                    </div>
                    <?php if ($member_id): ?>
                    <a href="download_savings_statement.php?member_id=<?php echo $member_id; ?>" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download Statement
                    </a>
                    <?php endif; ?>
                </div>
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

             <?php if ($warning): ?>
                 <div class="alert alert-info alert-dismissible fade show" role="alert">
                     <i class="fas fa-info-circle"></i> <?php echo $warning; ?>
                     <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                 </div>
             <?php endif; ?>

            <!-- Member Selection -->
             <?php if (count($members_list) > 0): ?>
             <div class="row mb-4">
                 <div class="col-md-8">
                     <div class="card">
                         <div class="card-header">
                             <h5><i class="fas fa-users"></i> Select Member</h5>
                         </div>
                         <div class="card-body">
                             <form method="GET" id="member-selection-form">
                                 <label for="member_id" class="form-label">Search and select a member by name or email:</label>
                                 <select id="member_id" name="member_id" class="form-select member-search" style="width: 100%;">
                                     <option value="">-- Type to search members --</option>
                                     <?php foreach ($members_list as $m): ?>
                                     <option value="<?php echo $m['id']; ?>" <?php echo ($m['id'] === $member_id) ? 'selected' : ''; ?>>
                                         <?php echo htmlspecialchars($m['full_name']) . ' - ' . htmlspecialchars($m['email']); ?>
                                     </option>
                                     <?php endforeach; ?>
                                 </select>
                             </form>
                             <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle"></i> Start typing to search by member name or email</small>
                         </div>
                     </div>
                 </div>
             </div>
             <?php endif; ?>

            <!-- Statistics Row -->
            <?php if ($member): ?>
            <div class="row mb-4">
                <!-- Current Savings Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-card-label">Current Savings</div>
                        <div class="stat-card-value text-success">
                            <?php echo formatCurrency($current_savings); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Total accumulated
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                    </div>
                </div>

                <!-- Mandatory Savings Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card <?php echo $mandatory_met ? 'success' : 'warning'; ?>">
                        <div class="stat-card-label">Mandatory Savings</div>
                        <div class="stat-card-value">
                            <?php echo formatCurrency(MANDATORY_SAVINGS); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <?php 
                            if ($mandatory_met) {
                                echo 'Target met ✓';
                            } else {
                                echo 'Remaining: ' . formatCurrency($mandatory_remaining);
                            }
                            ?>
                        </small>
                        <div class="stat-card-icon">
                            <i class="fas fa-target"></i>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="col-md-6 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-card-label">Savings Progress</div>
                        <div class="progress" style="height: 25px;">
                            <div 
                                class="progress-bar <?php echo $mandatory_met ? 'bg-success' : 'bg-warning'; ?>" 
                                role="progressbar" 
                                style="width: <?php echo min(100, ($current_savings / MANDATORY_SAVINGS) * 100); ?>%"
                                aria-valuenow="<?php echo $current_savings; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="<?php echo MANDATORY_SAVINGS; ?>"
                            >
                                <?php echo round(($current_savings / MANDATORY_SAVINGS) * 100); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main Row -->
            <?php if ($member): ?>
            <div class="row">
                <!-- Add Savings Form Column -->
                <div class="col-lg-5 mb-4">
                    <div class="card">
                         <div class="card-header">
                             <h5><i class="fas fa-plus-circle"></i> <?php echo $week_savings ? 'Update Member Savings' : 'Add Member Savings'; ?></h5>
                         </div>
                         <div class="card-body">
                             <form method="POST" class="needs-validation" novalidate>
                                 <!-- Savings Amount Field -->
                                 <div class="mb-3">
                                     <label for="savings_amount" class="form-label">Savings Amount (UGX)</label>
                                     <input 
                                         type="number" 
                                         class="form-control" 
                                         id="savings_amount" 
                                         name="savings_amount" 
                                         min="5000" 
                                         step="1000" 
                                         placeholder="Enter amount"
                                         value="<?php echo $week_savings ? $week_savings['savings_amount'] : ''; ?>"
                                         required
                                     >
                                     <small class="text-muted">Minimum: <?php echo formatCurrency(5000); ?></small>
                                 </div>

                                <!-- Payment Method Field -->
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Select payment method</option>
                                        <option value="cash" <?php echo $week_savings && $week_savings['payment_method'] === 'cash' ? 'selected' : ''; ?>>Cash</option>
                                        <option value="bank_transfer" <?php echo $week_savings && $week_savings['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="mobile_money" <?php echo $week_savings && $week_savings['payment_method'] === 'mobile_money' ? 'selected' : ''; ?>>Mobile Money</option>
                                    </select>
                                </div>

                                <!-- Receipt Number Field -->
                                <div class="mb-3">
                                    <label for="receipt_number" class="form-label">Receipt/Reference Number</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="receipt_number" 
                                        value="<?php echo $week_savings ? $week_savings['receipt_number'] : generateReceiptNumber(); ?>"
                                        readonly
                                    >
                                    <small class="text-muted"><?php echo $week_savings ? 'Original receipt number' : 'Auto-generated reference'; ?></small>
                                </div>

                                <!-- Notes Field -->
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea 
                                        class="form-control" 
                                        id="notes" 
                                        name="notes" 
                                        rows="2" 
                                        placeholder="Optional notes about this contribution"
                                    ><?php echo $week_savings ? htmlspecialchars($week_savings['notes']) : ''; ?></textarea>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" name="add_savings" class="btn btn-success w-100">
                                    <i class="fas fa-check"></i> <?php echo $week_savings ? 'Update Savings' : 'Record Savings'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Savings History Column -->
                <div class="col-lg-7 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Savings History</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($savings_history) > 0): ?>
                                            <?php foreach ($savings_history as $saving): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo formatDate($saving['savings_date']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="text-success">
                                                            +<?php echo formatCurrency($saving['savings_amount']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary">
                                                            <?php echo ucfirst(str_replace('_', ' ', $saving['payment_method'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($saving['receipt_number']); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    No savings recorded yet
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
                            <i class="fas fa-info-circle"></i> How Savings Work
                        </h5>
                        <ul class="mb-0">
                            <li>All members must contribute a minimum of <?php echo formatCurrency(MANDATORY_SAVINGS); ?> in mandatory savings</li>
                            <li>You can contribute additional savings at any time</li>
                            <li>Your savings share determines your interest distribution percentage</li>
                            <li>Higher savings = Higher interest income from group loans</li>
                            <li>Interest is automatically distributed on the 1st of each month</li>
                        </ul>
                    </div>
                </div>
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
    
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Select2 JS for searchable dropdown -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Custom Script -->
    <script src="js/script.js"></script>
    
    <!-- Initialize Select2 for member search -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 with custom options
            $('#member_id').select2({
                placeholder: 'Search members by name or email...',
                allowClear: false,
                width: '100%',
                language: {
                    noResults: function() {
                        return 'No members found. Try a different search.';
                    }
                },
                matcher: function(params, data) {
                    // Custom matcher for better search
                    if ($.trim(params.term) === '') {
                        return data;
                    }
                    
                    var term = params.term.toLowerCase();
                    var text = data.text.toLowerCase();
                    
                    if (text.indexOf(term) > -1) {
                        return data;
                    }
                    
                    return null;
                }
            });
            
            // Auto-submit form when member is selected (using Select2 event)
            $('#member_id').on('select2:select', function() {
                var memberValue = $(this).val();
                if (memberValue && memberValue !== '') {
                    // Navigate to the page with the selected member
                    window.location.href = 'savings.php?member_id=' + encodeURIComponent(memberValue);
                }
            });
        });
    </script>
    </body>
    </html>
