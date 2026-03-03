<?php
/**
 * Setup Missing Tables
 * Creates deleted_members, deletion_log, and other required tables
 */

require_once 'config/db_config.php';

$error = '';
$success = '';

// Create deleted_members table
$deleted_members_sql = "CREATE TABLE IF NOT EXISTS deleted_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    national_id VARCHAR(50),
    address TEXT,
    savings_amount DECIMAL(15, 2),
    status VARCHAR(50),
    date_joined DATE,
    nin VARCHAR(14),
    identification_number VARCHAR(50),
    profile_picture VARCHAR(255),
    occupation VARCHAR(100),
    deleted_by INT,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    member_data LONGTEXT,
    can_restore TINYINT DEFAULT 1,
    INDEX idx_member_id (member_id),
    INDEX idx_deleted_at (deleted_at)
)";

if ($conn->query($deleted_members_sql)) {
    $success .= "✓ deleted_members table created successfully<br>";
} else {
    $error .= "✗ Error creating deleted_members table: " . $conn->error . "<br>";
}

// Create deletion_log table
$deletion_log_sql = "CREATE TABLE IF NOT EXISTS deletion_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    member_email VARCHAR(100),
    member_name VARCHAR(100),
    deleted_by INT,
    reason VARCHAR(255),
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    restored TINYINT DEFAULT 0,
    restored_at DATETIME,
    restored_by INT,
    INDEX idx_member_id (member_id),
    INDEX idx_deleted_at (deleted_at)
)";

if ($conn->query($deletion_log_sql)) {
    $success .= "✓ deletion_log table created successfully<br>";
} else {
    $error .= "✗ Error creating deletion_log table: " . $conn->error . "<br>";
}

// Create interest_distributions table
$interest_dist_sql = "CREATE TABLE IF NOT EXISTS interest_distributions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    distribution_month INT NOT NULL,
    distribution_year INT NOT NULL,
    savings_ratio DECIMAL(10, 6),
    total_monthly_interest DECIMAL(15, 2),
    interest_earned DECIMAL(15, 2),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_member_id (member_id),
    INDEX idx_month_year (distribution_month, distribution_year)
)";

if ($conn->query($interest_dist_sql)) {
    $success .= "✓ interest_distributions table created successfully<br>";
} else {
    $error .= "✗ Error creating interest_distributions table: " . $conn->error . "<br>";
}

// Create member_interest table
$member_interest_sql = "CREATE TABLE IF NOT EXISTS member_interest (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    interest_amount DECIMAL(15, 2) DEFAULT 0,
    distribution_date DATE,
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_member_id (member_id)
)";

if ($conn->query($member_interest_sql)) {
    $success .= "✓ member_interest table created successfully<br>";
} else {
    $error .= "✗ Error creating member_interest table: " . $conn->error . "<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Missing Tables - 70K Savings & Loans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-database"></i> Database Setup</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-4">Missing Tables Setup</h5>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <strong>Success!</strong><br>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <strong>Error!</strong><br>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$error): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle"></i> All required tables have been created successfully!
                            </div>
                            <p>You can now proceed with member deletion and other features.</p>
                            <a href="admin.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Back to Admin Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
