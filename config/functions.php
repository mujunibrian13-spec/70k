<?php
/**
 * Core Functions File
 * Contains reusable functions for the entire application
 */

require_once 'db_config.php';

// PHP 5.5+ Compatibility: password_hash and password_verify functions
if (!function_exists('password_hash')) {
    define('PASSWORD_BCRYPT', 1);
    define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
    
    function password_hash($password, $algo) {
        // Use SHA256 with random salt for compatibility
        $salt = '';
        for ($i = 0; $i < 16; $i++) {
            $salt .= chr(mt_rand(0, 255));
        }
        $salt = bin2hex($salt);
        $hash = 'sha256$' . $salt . '$' . hash('sha256', $salt . $password);
        return $hash;
    }
}

if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        // Handle SHA256 format
        if (strpos($hash, 'sha256$') === 0) {
            $parts = explode('$', $hash);
            if (count($parts) === 3) {
                $salt = $parts[1];
                $stored_hash = $parts[2];
                $computed_hash = hash('sha256', $salt . $password);
                return $computed_hash === $stored_hash;
            }
        }
        // Handle MD5 format (fallback)
        if (strpos($hash, 'md5$') === 0) {
            $stored_hash = substr($hash, 4);
            return md5($password) === $stored_hash;
        }
        // Direct MD5 comparison (legacy)
        return md5($password) === $hash;
    }
}

// Custom function to handle additional hash formats (including bcrypt)
function verifyPasswordHashExtended($password, $hash) {
    // Handle SHA256 format (custom format)
    if (strpos($hash, 'sha256$') === 0) {
        $parts = explode('$', $hash);
        if (count($parts) === 3) {
            $salt = $parts[1];
            $stored_hash = $parts[2];
            $computed_hash = hash('sha256', $salt . $password);
            return $computed_hash === $stored_hash;
        }
    }
    
    // Handle native bcrypt format ($2y$, $2a$, $2b$) using PHP's native function
    if (strpos($hash, '$2') === 0) {
        // Use PHP's native password_verify function (built-in)
        // Call the language construct directly, not our wrapper
        if (PHP_VERSION_ID >= 50500) {
            // PHP 5.5+ has native password_verify
            return \password_verify($password, $hash);
        }
        // For older PHP, bcrypt won't work, return false
        return false;
    }
    
    // Handle MD5 format (fallback)
    if (strpos($hash, 'md5$') === 0) {
        $stored_hash = substr($hash, 4);
        return md5($password) === $stored_hash;
    }
    
    // Direct MD5 comparison (legacy)
    return md5($password) === $hash;
}

/**
 * Sanitize user input to prevent XSS attacks
 * @param string $input User input string
 * @return string Sanitized string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 * @param string $email Email address to validate
 * @return boolean True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password using bcrypt
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password against hash
 * Handles SHA256, bcrypt, and MD5 formats
 * @param string $password Plain text password
 * @param string $hash Password hash
 * @return boolean True if password matches hash
 */
function verifyPassword($password, $hash) {
    return verifyPasswordHashExtended($password, $hash);
}

/**
 * Get total savings of all members
 * @param object $conn Database connection
 * @return float Total savings amount
 */
function getTotalSavings($conn) {
    $query = "SELECT COALESCE(SUM(savings_amount), 0) as total FROM members";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Get total loans outstanding
 * @param object $conn Database connection
 * @return float Total loan amount
 */
function getTotalLoans($conn) {
    $query = "SELECT COALESCE(SUM(loan_amount), 0) as total FROM loans WHERE status = 'active'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Get member's current savings
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return float Member's savings amount
 */
function getMemberSavings($conn, $member_id) {
    $query = "SELECT savings_amount FROM members WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return isset($row['savings_amount']) ? $row['savings_amount'] : 0;
}

/**
 * Calculate savings ratio for a member
 * Used for interest distribution calculation
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return float Member's savings ratio (0 to 1)
 */
function getMemberSavingsRatio($conn, $member_id) {
    $total_savings = getTotalSavings($conn);
    
    if ($total_savings == 0) {
        return 0;
    }
    
    $member_savings = getMemberSavings($conn, $member_id);
    return $member_savings / $total_savings;
}

/**
 * Calculate monthly interest on a loan
 * @param float $loan_amount Principal loan amount
 * @param float $interest_rate Monthly interest rate (default 2% = 0.02)
 * @return float Calculated interest amount
 */
function calculateMonthlyInterest($loan_amount, $interest_rate = LOAN_INTEREST_RATE) {
    return $loan_amount * $interest_rate;
}

/**
 * Get total active loan amount
 * @param object $conn Database connection
 * @return float Total outstanding loans
 */
function getTotalActiveLoanAmount($conn) {
    $query = "SELECT COALESCE(SUM(loan_amount), 0) as total FROM loans WHERE status = 'active'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Calculate total monthly interest from all loans
 * @param object $conn Database connection
 * @return float Total interest amount to be distributed
 */
function calculateTotalMonthlyInterest($conn) {
    $total_loan_amount = getTotalActiveLoanAmount($conn);
    return calculateMonthlyInterest($total_loan_amount);
}

/**
 * Distribute interest to all members based on savings ratio
 * Called automatically at end of each month
 * @param object $conn Database connection
 * @param int $month Month number (1-12)
 * @param int $year Year
 * @return boolean True if distribution successful
 */
function distributeInterestToMembers($conn, $month, $year) {
    $total_interest = calculateTotalMonthlyInterest($conn);
    
    // If no interest to distribute, return true
    if ($total_interest <= 0) {
        return true;
    }
    
    // Get all active members
    $query = "SELECT id FROM members WHERE status = 'active'";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $member_id = $row['id'];
        $ratio = getMemberSavingsRatio($conn, $member_id);
        $interest_share = $total_interest * $ratio;
        
        // Add interest to member's savings
        $update_query = "UPDATE members SET savings_amount = savings_amount + ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('di', $interest_share, $member_id);
        $stmt->execute();
        
        // Log transaction
        logTransaction($conn, $member_id, 'interest_earned', $interest_share, 'Monthly interest distribution', $month, $year);
    }
    
    return true;
}

/**
 * Log transaction for audit trail
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @param string $transaction_type Type of transaction (savings, loan, interest, etc.)
 * @param float $amount Transaction amount
 * @param string $description Transaction description
 * @param int $month Month of transaction
 * @param int $year Year of transaction
 * @return boolean True if logged successfully
 */
function logTransaction($conn, $member_id, $transaction_type, $amount, $description = '', $month = null, $year = null) {
    // Use current month/year if not specified
    if ($month === null) {
        $month = date('n');
        $year = date('Y');
    }
    
    // Enhance description for interest transactions
    if ($transaction_type === 'interest_earned' && strpos($description, 'Monthly interest') === 0) {
        $month_name = date('F', mktime(0, 0, 0, $month, 1));
        $description = "Profit - Monthly interest distribution for {$month_name} {$year}";
    }
    
    $query = "INSERT INTO transactions (member_id, transaction_type, amount, description, transaction_month, transaction_year, transaction_date) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('isdsii', $member_id, $transaction_type, $amount, $description, $month, $year);
    
    return $stmt->execute();
}

/**
 * Get member details by ID
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return array Member details or null if not found
 */
function getMemberDetails($conn, $member_id) {
    $query = "SELECT id, full_name, email, phone, nin, identification_number, address, occupation, profile_picture, savings_amount, status, date_joined FROM members WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $member_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Check if member has met mandatory savings requirement
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return boolean True if member has saved at least MANDATORY_SAVINGS
 */
function hasMandatorySavings($conn, $member_id) {
    $savings = getMemberSavings($conn, $member_id);
    return $savings >= MANDATORY_SAVINGS;
}

/**
 * Format currency for display
 * @param float $amount Amount to format
 * @param boolean $include_symbol Include currency symbol
 * @return string Formatted currency string
 */
function formatCurrency($amount, $include_symbol = true) {
    $formatted = number_format($amount, 2, '.', ',');
    if ($include_symbol) {
        return CURRENCY_SYMBOL . ' ' . $formatted;
    }
    return $formatted;
}

/**
 * Format date for display
 * @param string $date Date string (YYYY-MM-DD)
 * @return string Formatted date (e.g., Mar 02, 2026)
 */
function formatDate($date) {
    if (empty($date)) {
        return '-';
    }
    return date('M d, Y', strtotime($date));
}

/**
 * Log error to file
 * @param string $message Error message
 * @param string $level Error level (ERROR, WARNING, INFO)
 * @return void
 */
function logError($message, $level = 'ERROR') {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message\n";
    
    // Create logs directory if it doesn't exist
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

/**
 * Redirect to another page
 * @param string $page Page to redirect to
 * @return void
 */
function redirect($page) {
    header("Location: $page");
    exit();
}

/**
 * Check if user is logged in
 * @return boolean True if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return boolean True if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get all members
 * @param object $conn Database connection
 * @param string $status Filter by status (optional)
 * @return array Array of member records
 */
function getAllMembers($conn, $status = 'active') {
    $query = "SELECT id, full_name, email, phone, savings_amount, status, date_joined FROM members";
    
    if ($status) {
        $query .= " WHERE status = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

/**
 * Get member's loan history
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return array Array of loan records
 */
function getMemberLoanHistory($conn, $member_id) {
    $query = "SELECT id, loan_amount, interest_rate, status, loan_date, due_date FROM loans WHERE member_id = ? ORDER BY loan_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $member_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Calculate remaining balance on a loan with interest
 * @param float $principal Original loan amount
 * @param float $monthly_rate Monthly interest rate
 * @param int $months_passed Number of months since loan was issued
 * @return float Remaining balance
 */
function calculateLoanBalance($principal, $monthly_rate = LOAN_INTEREST_RATE, $months_passed = 1) {
    $balance = $principal;
    
    for ($i = 0; $i < $months_passed; $i++) {
        $interest = $balance * $monthly_rate;
        $balance += $interest;
    }
    
    return $balance;
}

/**
 * Calculate maximum amount member can borrow based on available group savings pool
 * Member can borrow from total group savings minus loans already approved/active
 * No limit based on individual savings - only limited by group pool availability
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return float Maximum borrowable amount
 */
function getMaxBorrowableAmount($conn, $member_id) {
    // Get total savings in the pool (all members)
    $total_savings = getTotalSavings($conn);
    
    // Get total amount already borrowed (active and approved loans)
    $query = "SELECT COALESCE(SUM(loan_amount), 0) as total_borrowed FROM loans WHERE status IN ('active', 'approved')";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $total_borrowed = $row['total_borrowed'];
    
    // Available pool = total group savings - already borrowed
    $available_pool = $total_savings - $total_borrowed;
    
    // Member can borrow up to the available pool
    // Ensure it's not negative
    return max(0, $available_pool);
}

/**
 * Record a loan payment and update loan status
 * @param object $conn Database connection
 * @param int $loan_id Loan ID
 * @param float $payment_amount Amount being paid
 * @param string $payment_method Payment method (cash, bank_transfer, mobile_money)
 * @param string $receipt_number Payment receipt/reference number
 * @param string $notes Payment notes
 * @return array Result array with 'success' and 'message' keys
 */
function recordLoanPayment($conn, $loan_id, $payment_amount, $payment_method, $receipt_number = '', $notes = '') {
    // Get current loan details
    $query = "SELECT member_id, loan_amount, interest_rate, total_payable, remaining_balance, status FROM loans WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $loan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return array('success' => false, 'message' => 'Loan not found');
    }
    
    $loan = $result->fetch_assoc();
    $member_id = $loan['member_id'];
    $remaining_balance = $loan['remaining_balance'];
    
    // Check if loan is already cleared
    if ($loan['status'] === 'cleared' || $loan['status'] === 'completed') {
        return array('success' => false, 'message' => 'This loan has already been cleared');
    }
    
    // Calculate new balance
    $new_balance = $remaining_balance - $payment_amount;
    
    if ($new_balance < 0) {
        return array('success' => false, 'message' => 'Payment amount exceeds remaining balance');
    }
    
    // Determine new status
    $new_status = ($new_balance <= 0) ? 'cleared' : 'active';
    $amount_paid = $loan['total_payable'] - $new_balance;
    
    // Insert payment record (status = pending, waiting for admin approval)
    $payment_query = "INSERT INTO loan_payments (loan_id, member_id, payment_amount, payment_method, receipt_number, notes, status, payment_date) 
                      VALUES (?, ?, ?, ?, ?, ?, 'pending', CURDATE())";
    $payment_stmt = $conn->prepare($payment_query);
    $payment_stmt->bind_param('iidsss', $loan_id, $member_id, $payment_amount, $payment_method, $receipt_number, $notes);
    
    if (!$payment_stmt->execute()) {
         return array('success' => false, 'message' => 'Failed to record payment');
     }
     
     return array(
         'success' => true, 
         'message' => 'Payment submitted successfully! Your payment is pending admin approval.',
         'status' => 'pending',
         'remaining_balance' => $remaining_balance
     );
    }

    /**
    * Approve a loan payment by admin
    * Updates loan balance, clears loan if fully paid, and distributes interest
    * @param object $conn Database connection
    * @param int $payment_id Payment ID
    * @param int $approved_by Admin user ID
    * @return array Result array with 'success' and 'message' keys
    */
    function approveLoanPayment($conn, $payment_id, $approved_by) {
     // Get payment details
     $query = "SELECT loan_id, member_id, payment_amount FROM loan_payments WHERE id = ?";
     $stmt = $conn->prepare($query);
     $stmt->bind_param('i', $payment_id);
     $stmt->execute();
     $result = $stmt->get_result();
     
     if ($result->num_rows === 0) {
         return array('success' => false, 'message' => 'Payment not found');
     }
     
     $payment = $result->fetch_assoc();
     $loan_id = $payment['loan_id'];
     $member_id = $payment['member_id'];
     $payment_amount = $payment['payment_amount'];
     
     // Get current loan details
     $loan_query = "SELECT loan_amount, interest_rate, total_payable, remaining_balance, status FROM loans WHERE id = ?";
     $loan_stmt = $conn->prepare($loan_query);
     $loan_stmt->bind_param('i', $loan_id);
     $loan_stmt->execute();
     $loan_result = $loan_stmt->get_result();
     
     if ($loan_result->num_rows === 0) {
         return array('success' => false, 'message' => 'Loan not found');
     }
     
     $loan = $loan_result->fetch_assoc();
     $remaining_balance = $loan['remaining_balance'];
     
     // Calculate new balance
     $new_balance = $remaining_balance - $payment_amount;
     if ($new_balance < 0) {
         $new_balance = 0;
     }
     
     // Determine new status
     $new_status = ($new_balance <= 0) ? 'cleared' : 'active';
     $amount_paid = $loan['total_payable'] - $new_balance;
     
     // Update loan status and balance
     $update_query = "UPDATE loans SET remaining_balance = ?, amount_paid = ?, status = ? WHERE id = ?";
     $update_stmt = $conn->prepare($update_query);
     $update_stmt->bind_param('ddsi', $new_balance, $amount_paid, $new_status, $loan_id);
     
     if (!$update_stmt->execute()) {
         return array('success' => false, 'message' => 'Failed to update loan');
     }
     
     // Approve the payment
     $approve_query = "UPDATE loan_payments SET status = 'approved', approved_by = ?, approval_date = NOW() WHERE id = ?";
     $approve_stmt = $conn->prepare($approve_query);
     $approve_stmt->bind_param('ii', $approved_by, $payment_id);
     
     if (!$approve_stmt->execute()) {
         return array('success' => false, 'message' => 'Failed to approve payment');
     }
     
     // Log transaction
     logTransaction($conn, $member_id, 'loan_payment', $payment_amount, "Loan payment - " . ($new_status === 'cleared' ? 'Loan cleared' : 'Partial payment'), 
                    date('n'), date('Y'));
     
     // Distribute interest from this payment immediately to all members
     $monthly_interest_from_payment = $payment_amount * LOAN_INTEREST_RATE;
     
     if ($monthly_interest_from_payment > 0) {
         $total_savings = getTotalSavings($conn);
         
         if ($total_savings > 0) {
             $members_query = "SELECT id FROM members WHERE status = 'active'";
             $members_result = $conn->query($members_query);
             
             if ($members_result) {
                 while ($m = $members_result->fetch_assoc()) {
                     $dist_member_id = $m['id'];
                     $dist_ratio = getMemberSavingsRatio($conn, $dist_member_id);
                     $interest_share = $monthly_interest_from_payment * $dist_ratio;
                     
                     if ($interest_share > 0) {
                         $add_interest_query = "UPDATE members SET savings_amount = savings_amount + ? WHERE id = ?";
                         $add_interest_stmt = $conn->prepare($add_interest_query);
                         $add_interest_stmt->bind_param('di', $interest_share, $dist_member_id);
                         $add_interest_stmt->execute();
                         
                         logTransaction($conn, $dist_member_id, 'interest_earned', $interest_share, "Interest from loan payment received", 
                                        date('n'), date('Y'));
                     }
                 }
             }
         }
     }
     
     return array(
         'success' => true, 
         'message' => $new_status === 'cleared' ? 'Payment approved! Loan cleared successfully!' : 'Payment approved successfully!',
         'status' => $new_status,
         'remaining_balance' => max(0, $new_balance)
     );
    }

/**
 * Generate unique reference number for savings transaction
 * Format: RCP-YYYYMMDD-XXXXX (RCP-20260302-12345)
 * @return string Generated reference number
 */
function generateReceiptNumber() {
    $date = date('Ymd');
    $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return 'RCP-' . $date . '-' . $random;
}

/**
 * Get current week's savings entry for a member
 * Returns existing entry if member already saved this week
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return array Savings record or null if not found
 */
function getCurrentWeekSavings($conn, $member_id) {
    // Get Monday of current week
    $monday = date('Y-m-d', strtotime('monday this week'));
    $sunday = date('Y-m-d', strtotime('sunday this week'));
    
    $query = "SELECT * FROM savings WHERE member_id = ? AND savings_date BETWEEN ? AND ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iss', $member_id, $monday, $sunday);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get next available savings date for a member
 * Shows when member can save again if they already saved this week
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return string Next available date or current date if available
 */
function getNextSavingsDate($conn, $member_id) {
    $current_savings = getCurrentWeekSavings($conn, $member_id);
    
    if (!$current_savings) {
        // Can save now
        return date('Y-m-d');
    }
    
    // Can save next week (Monday)
    return date('Y-m-d', strtotime('next monday'));
}

/**
 * Reset member password - Self service password reset
 * Member provides current password and new password
 * @param object $conn Database connection
 * @param int $user_id User ID
 * @param string $current_password Current password (for verification)
 * @param string $new_password New password to set
 * @return array Result array with 'success' and 'message' keys
 */
function resetMemberPassword($conn, $user_id, $current_password, $new_password) {
    // Validate input
    if (empty($current_password) || empty($new_password)) {
        return array(
            'success' => false,
            'message' => 'Password fields cannot be empty'
        );
    }
    
    // Check password length
    if (strlen($new_password) < 6) {
        return array(
            'success' => false,
            'message' => 'New password must be at least 6 characters long'
        );
    }
    
    // Get current password hash from database
    $query = "SELECT password, username FROM users WHERE id = ? AND role = 'member'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return array(
            'success' => false,
            'message' => 'User not found or does not have permission'
        );
    }
    
    $user = $result->fetch_assoc();
    $username = $user['username'];
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        return array(
            'success' => false,
            'message' => 'Current password is incorrect'
        );
    }
    
    // Prevent same password
    if ($current_password === $new_password) {
        return array(
            'success' => false,
            'message' => 'New password must be different from current password'
        );
    }
    
    // Hash new password
    $hashed_password = hashPassword($new_password);
    
    // Update password in database
    $update_query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('si', $hashed_password, $user_id);
    
    if (!$update_stmt->execute()) {
        return array(
            'success' => false,
            'message' => 'Failed to update password. Please try again.'
        );
    }
    
    // Log transaction for audit
    logTransaction($conn, getMemberIdByUserId($conn, $user_id), 'password_reset', 0, 
                   'Password reset by member');
    
    return array(
        'success' => true,
        'message' => 'Password changed successfully! Please login with your new password.'
    );
}

/**
 * Get member ID from user ID
 * @param object $conn Database connection
 * @param int $user_id User ID
 * @return int|null Member ID or null if not found
 */
function getMemberIdByUserId($conn, $user_id) {
    $query = "SELECT id FROM members WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    return null;
}

/**
 * Admin reset member password - Admin can reset without current password
 * @param object $conn Database connection
 * @param int $user_id User ID
 * @param string $new_password New password to set
 * @return array Result array with 'success' and 'message' keys
 */
function adminResetMemberPassword($conn, $user_id, $new_password) {
    // Validate input
    if (empty($new_password)) {
        return array(
            'success' => false,
            'message' => 'Password cannot be empty'
        );
    }
    
    // Check password length
    if (strlen($new_password) < 6) {
        return array(
            'success' => false,
            'message' => 'Password must be at least 6 characters long'
        );
    }
    
    // Verify user exists and is a member
    $check_query = "SELECT id, role, username FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('i', $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        return array(
            'success' => false,
            'message' => 'User not found'
        );
    }
    
    $user = $check_result->fetch_assoc();
    
    if ($user['role'] !== 'member') {
        return array(
            'success' => false,
            'message' => 'Can only reset member passwords'
        );
    }
    
    $username = $user['username'];
    
    // Hash new password
    $hashed_password = hashPassword($new_password);
    
    // Update password in database
    $update_query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('si', $hashed_password, $user_id);
    
    if (!$update_stmt->execute()) {
        return array(
            'success' => false,
            'message' => 'Failed to reset password. Please try again.'
        );
    }
    
    // Log transaction for audit
    $member_id = getMemberIdByUserId($conn, $user_id);
    if ($member_id) {
        logTransaction($conn, $member_id, 'password_reset', 0, 
                       'Password reset by administrator');
    }
    
    return array(
        'success' => true,
        'message' => "Password reset successfully for {$username}",
        'username' => $username
    );
    }

    /**
     * Get recently deleted members that can be restored
     * @param object $conn Database connection
     * @return array List of deleted members
     */
    function getDeletedMembers($conn) {
        // Check if table exists first
        $table_check = $conn->query("SHOW TABLES LIKE 'deleted_members'");
        if (!$table_check || $table_check->num_rows === 0) {
            return array(); // Table doesn't exist yet, return empty array
        }
        
        $query = "SELECT * FROM deleted_members WHERE can_restore = 1 AND DATE_ADD(deleted_at, INTERVAL 24 HOUR) > NOW() ORDER BY deleted_at DESC LIMIT 10";
        $result = $conn->query($query);
        
        if (!$result) {
            return array(); // Query failed, return empty array
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Restore a deleted member
     * @param object $conn Database connection
     * @param int $deleted_member_id ID from deleted_members table
     * @param int $restored_by Admin user ID
     * @return array Result status
     */
    function restoreDeletedMember($conn, $deleted_member_id, $restored_by) {
        // Check if table exists first
        $table_check = $conn->query("SHOW TABLES LIKE 'deleted_members'");
        if (!$table_check || $table_check->num_rows === 0) {
            return array('success' => false, 'message' => 'Backup system not configured. Please run database setup.');
        }
        
        // Get the backup data
        $query = "SELECT * FROM deleted_members WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $deleted_member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return array('success' => false, 'message' => 'Backup record not found');
        }
    
    $backup = $result->fetch_assoc();
    $member_id = $backup['member_id'];
    
    // Start transaction
    $conn->query("START TRANSACTION");
    
    try {
       // Check if member still exists
       $check_query = "SELECT id FROM members WHERE id = ?";
       $check_stmt = $conn->prepare($check_query);
       $check_stmt->bind_param('i', $member_id);
       $check_stmt->execute();
       $check_result = $check_stmt->get_result();
       
       if ($check_result->num_rows > 0) {
           $conn->query("ROLLBACK");
           return array('success' => false, 'message' => 'Member already exists in system');
       }
       
       // Restore member
       $restore_query = "INSERT INTO members (id, full_name, email, phone, national_id, address, savings_amount, status, date_joined) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
       $restore_stmt = $conn->prepare($restore_query);
       $restore_stmt->bind_param(
           'isssssdss',
           $member_id,
           $backup['full_name'],
           $backup['email'],
           $backup['phone'],
           $backup['national_id'],
           $backup['address'],
           $backup['savings_amount'],
           $backup['status'],
           $backup['date_joined']
       );
       
       if (!$restore_stmt->execute()) {
           throw new Exception('Failed to restore member');
       }
       
       // Update deletion log
       $log_query = "UPDATE deletion_log SET restored = 1, restored_at = NOW(), restored_by = ? WHERE member_id = ? AND restored = 0";
       $log_stmt = $conn->prepare($log_query);
       if ($log_stmt) {
           $log_stmt->bind_param('ii', $restored_by, $member_id);
           $log_stmt->execute();
       }
       
       // Mark backup as used
       $update_backup = "UPDATE deleted_members SET can_restore = 0 WHERE id = ?";
       $update_stmt = $conn->prepare($update_backup);
       if ($update_stmt) {
           $update_stmt->bind_param('i', $deleted_member_id);
           $update_stmt->execute();
       }
       
       // Commit transaction
       $conn->query("COMMIT");
       
       return array('success' => true, 'message' => "Member {$backup['full_name']} has been restored successfully");
       
       } catch (Exception $e) {
       $conn->query("ROLLBACK");
       return array('success' => false, 'message' => 'Restore failed: ' . $e->getMessage());
       }
    }

    ?>
