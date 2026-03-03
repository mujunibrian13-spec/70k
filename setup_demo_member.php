<?php
/**
 * Demo Member Setup Script
 * Quickly adds a demo member for testing and demonstration
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

// Check if already have demo member
$check_query = "SELECT * FROM members WHERE email = 'demo@70k.local'";
$check_result = $conn->query($check_query);

if ($check_result->num_rows > 0) {
    $demo_member = $check_result->fetch_assoc();
    $message = 'Demo member already exists!';
    $action = 'exists';
} else {
    // Create demo user first
    $demo_username = 'demo';
    $demo_password = password_hash('demo123', PASSWORD_DEFAULT);
    $demo_email = 'demo@70k.local';
    $demo_name = 'Demo User';
    
    // Check if demo user exists
    $user_check = $conn->query("SELECT id FROM users WHERE username = 'demo'");
    
    if ($user_check->num_rows === 0) {
        // Create user
        $user_query = "INSERT INTO users (username, email, password, full_name, role, status, created_at) 
                      VALUES (?, ?, ?, ?, 'member', 'active', NOW())";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param('ssss', $demo_username, $demo_email, $demo_password, $demo_name);
        $user_stmt->execute();
        $user_id = $conn->insert_id;
    } else {
        $user_row = $user_check->fetch_assoc();
        $user_id = $user_row['id'];
    }
    
    // Create demo member
    $member_query = "INSERT INTO members (
        user_id, 
        full_name, 
        email, 
        phone, 
        nin, 
        address, 
        savings_amount, 
        status, 
        date_joined,
        last_savings_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())";
    
    $member_stmt = $conn->prepare($member_query);
    $full_name = 'John Demo Member';
    $email = 'demo@70k.local';
    $phone = '+256701234567';
    $nin = '12345678901234';
    $address = '123 Demo Street, Kampala, Uganda';
    $savings_amount = 500000; // 500k UGX initial savings
    $status = 'active';
    
    $member_stmt->bind_param(
        'issssds',
        $user_id,
        $full_name,
        $email,
        $phone,
        $nin,
        $address,
        $savings_amount,
        $status
    );
    
    if ($member_stmt->execute()) {
        $demo_member = array(
            'id' => $conn->insert_id,
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'nin' => $nin,
            'address' => $address,
            'savings_amount' => $savings_amount,
            'status' => $status,
            'user_id' => $user_id
        );
        $message = 'Demo member created successfully!';
        $action = 'created';
    } else {
        $message = 'Error creating demo member: ' . $conn->error;
        $action = 'error';
        $demo_member = null;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Member Setup - 70K Savings & Loans</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            margin: 20px;
        }
        
        .setup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        
        .setup-body {
            padding: 40px 30px;
        }
        
        .demo-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .demo-credentials {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #b3d9ff;
        }
        
        .credential-item:last-child {
            border-bottom: none;
        }
        
        .credential-label {
            font-weight: bold;
            color: #333;
        }
        
        .credential-value {
            font-family: monospace;
            color: #0066cc;
            font-weight: bold;
        }
        
        .copy-btn {
            cursor: pointer;
            margin-left: 10px;
            color: #0066cc;
        }
        
        .member-details {
            margin-top: 30px;
        }
        
        .member-details h5 {
            color: #667eea;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        
        .detail-value {
            color: #333;
        }
        
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .error-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .action-buttons a,
        .action-buttons button {
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1><i class="fas fa-user-plus"></i> Demo Member Setup</h1>
            <p class="mb-0">Quick setup for testing and demonstration</p>
        </div>
        
        <div class="setup-body">
            <?php if ($action === 'created'): ?>
            
            <div class="text-center">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-success">Demo Member Created Successfully!</h3>
                <p class="text-muted">Your test member is ready for demonstration</p>
            </div>
            
            <div class="demo-credentials">
                <h5 class="mb-3"><i class="fas fa-key"></i> Demo Credentials</h5>
                
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">demo@70k.local</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">demo123</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">NIN:</span>
                    <span class="credential-value">12345678901234</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Phone:</span>
                    <span class="credential-value">+256701234567</span>
                </div>
            </div>
            
            <div class="member-details">
                <h5><i class="fas fa-user-circle"></i> Member Information</h5>
                
                <div class="detail-row">
                    <span class="detail-label">Member ID:</span>
                    <span class="detail-value"><strong><?php echo $demo_member['id']; ?></strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value"><?php echo $demo_member['full_name']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo $demo_member['email']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo $demo_member['phone']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">NIN:</span>
                    <span class="detail-value"><?php echo $demo_member['nin']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value"><?php echo $demo_member['address']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Initial Savings:</span>
                    <span class="detail-value text-success"><strong><?php echo formatCurrency($demo_member['savings_amount']); ?></strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="badge bg-success"><?php echo ucfirst($demo_member['status']); ?></span>
                    </span>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <h5><i class="fas fa-lightbulb"></i> How to Use This Demo Member</h5>
                <ol class="mb-0">
                    <li>Log in with the credentials above as a member</li>
                    <li>Or log in as admin to manage this member</li>
                    <li>Use this member to test:
                        <ul>
                            <li>Adding and updating savings</li>
                            <li>Applying for loans</li>
                            <li>Making loan payments</li>
                            <li>Viewing transaction history</li>
                            <li>Testing edit member profile</li>
                            <li>Testing delete and restore member</li>
                        </ul>
                    </li>
                </ol>
            </div>
            
            <?php elseif ($action === 'exists'): ?>
            
            <div class="text-center">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-info">Demo Member Already Exists</h3>
                <p class="text-muted">The demo member is ready for use</p>
            </div>
            
            <div class="demo-credentials">
                <h5 class="mb-3"><i class="fas fa-key"></i> Demo Credentials</h5>
                
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">demo@70k.local</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">demo123</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Member ID:</span>
                    <span class="credential-value"><?php echo $demo_member['id']; ?></span>
                </div>
            </div>
            
            <div class="member-details">
                <h5><i class="fas fa-user-circle"></i> Current Member Details</h5>
                
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value"><?php echo $demo_member['full_name']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Current Savings:</span>
                    <span class="detail-value text-success"><strong><?php echo formatCurrency($demo_member['savings_amount']); ?></strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="badge bg-success"><?php echo ucfirst($demo_member['status']); ?></span>
                    </span>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <strong><i class="fas fa-info-circle"></i> Ready to Demo!</strong>
                Use the credentials above to test the system.
            </div>
            
            <?php else: ?>
            
            <div class="text-center">
                <div class="error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3 class="text-danger">Error</h3>
                <p class="text-muted"><?php echo $message; ?></p>
            </div>
            
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go to Dashboard
                </a>
                <a href="admin.php" class="btn btn-info">
                    <i class="fas fa-shield-alt"></i> Admin Dashboard
                </a>
                <a href="login.php" class="btn btn-success">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
