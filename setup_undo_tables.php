<?php
/**
 * Setup Undo/Restore Tables
 * Creates the necessary database tables for delete & restore functionality
 */

require_once 'config/db_config.php';

$message = '';
$success = false;
$tables_exist = false;

// Check if tables already exist
$check_query = "SHOW TABLES LIKE 'deleted_members'";
$check_result = $conn->query($check_query);
if ($check_result && $check_result->num_rows > 0) {
    $tables_exist = true;
    $message = 'Tables already exist. Undo/Restore feature is ready!';
    $success = true;
} else {
    // Create the tables
    try {
        // Create deleted_members table
        $sql1 = "CREATE TABLE IF NOT EXISTS deleted_members (
            id INT PRIMARY KEY AUTO_INCREMENT,
            member_id INT NOT NULL COMMENT 'Original member ID',
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            national_id VARCHAR(50),
            address TEXT,
            savings_amount DECIMAL(15, 2) DEFAULT 0.00,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            date_joined DATE,
            deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_by INT COMMENT 'Admin user ID who deleted',
            member_data JSON COMMENT 'Full member data in JSON format',
            can_restore TINYINT(1) DEFAULT 1,
            INDEX idx_member_id (member_id),
            INDEX idx_deleted_at (deleted_at),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$conn->query($sql1)) {
            throw new Exception('Failed to create deleted_members table: ' . $conn->error);
        }
        
        // Create deletion_log table
        $sql2 = "CREATE TABLE IF NOT EXISTS deletion_log (
            id INT PRIMARY KEY AUTO_INCREMENT,
            member_id INT NOT NULL,
            member_email VARCHAR(100),
            member_name VARCHAR(100),
            deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_by INT COMMENT 'Admin user ID',
            reason TEXT,
            restored TINYINT(1) DEFAULT 0,
            restored_at DATETIME,
            restored_by INT,
            INDEX idx_member_id (member_id),
            INDEX idx_deleted_at (deleted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$conn->query($sql2)) {
            throw new Exception('Failed to create deletion_log table: ' . $conn->error);
        }
        
        $message = 'Success! Both tables created. Undo/Restore feature is now ready!';
        $success = true;
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $success = false;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Undo Tables - 70K Savings & Loans</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 30px;
            text-align: center;
        }
        
        .card-body {
            padding: 40px 30px;
        }
        
        .success-icon {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .error-icon {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .message-box {
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .btn-group-vertical {
            width: 100%;
        }
        
        .btn-group-vertical .btn {
            margin-bottom: 10px;
        }
        
        .table-list {
            list-style: none;
            padding: 0;
        }
        
        .table-list li {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
        }
        
        .table-list li:last-child {
            border-bottom: none;
        }
        
        .table-list i {
            margin-right: 10px;
            color: #28a745;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-database"></i> Setup Undo Tables</h2>
                <p class="mb-0">Database setup for Delete & Restore feature</p>
            </div>
            
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="text-center">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="text-success">Setup Complete!</h3>
                    </div>
                    
                    <div class="message-box success-message">
                        <i class="fas fa-thumbs-up"></i> <?php echo $message; ?>
                    </div>
                    
                    <?php if (!$tables_exist): ?>
                    <div class="info-box">
                        <h5><i class="fas fa-info-circle"></i> Tables Created</h5>
                        <ul class="table-list">
                            <li>
                                <i class="fas fa-check"></i>
                                <span><strong>deleted_members</strong> - Stores backup of deleted members</span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><strong>deletion_log</strong> - Audit trail of all deletions</span>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb"></i> What's Next?</h5>
                        <ol class="mb-0">
                            <li>Visit <strong>demo-start.php</strong> to create a demo member</li>
                            <li>Try deleting and restoring the demo member</li>
                            <li>Check the 24-hour undo window feature</li>
                            <li>View the deletion audit log</li>
                        </ol>
                    </div>
                    
                    <div class="btn-group-vertical">
                        <a href="demo-start.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right"></i> Go to Demo Start
                        </a>
                        <a href="admin.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-shield-alt"></i> Go to Admin Dashboard
                        </a>
                    </div>
                
                <?php else: ?>
                    <div class="text-center">
                        <div class="error-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h3 class="text-danger">Setup Failed</h3>
                    </div>
                    
                    <div class="message-box error-message">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $message; ?>
                    </div>
                    
                    <div class="info-box">
                        <h5><i class="fas fa-tools"></i> Troubleshooting</h5>
                        <ol class="mb-0">
                            <li>Make sure your database is running</li>
                            <li>Check database user has CREATE TABLE permissions</li>
                            <li>Try again or use PHPMyAdmin to run the SQL manually</li>
                        </ol>
                    </div>
                    
                    <div class="btn-group-vertical">
                        <a href="setup_undo_tables.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-redo"></i> Try Again
                        </a>
                        <a href="admin.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Back to Admin
                        </a>
                    </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
