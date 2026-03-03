<?php
/**
 * Add Logout Tracking
 * Adds last_logout column to users table and creates login_logout_history table
 */

require_once 'config/db_config.php';

$error = '';
$success = '';

// Add last_logout column if it doesn't exist
$alter_users_sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_logout DATETIME";
if ($conn->query($alter_users_sql)) {
    $success .= "✓ Added last_logout column to users table<br>";
} else {
    $error .= "✗ Error adding last_logout column: " . $conn->error . "<br>";
}

// Add indexes
$add_index_login = "ALTER TABLE users ADD INDEX IF NOT EXISTS idx_last_login (last_login)";
if ($conn->query($add_index_login)) {
    $success .= "✓ Added index for last_login<br>";
} else {
    $error .= "✗ Error adding last_login index: " . $conn->error . "<br>";
}

$add_index_logout = "ALTER TABLE users ADD INDEX IF NOT EXISTS idx_last_logout (last_logout)";
if ($conn->query($add_index_logout)) {
    $success .= "✓ Added index for last_logout<br>";
} else {
    $error .= "✗ Error adding last_logout index: " . $conn->error . "<br>";
}

// Create login_logout_history table
$login_logout_table_sql = "CREATE TABLE IF NOT EXISTS login_logout_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(20) NOT NULL COMMENT 'login or logout',
    action_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_time (action_time),
    INDEX idx_action (action)
)";

if ($conn->query($login_logout_table_sql)) {
    $success .= "✓ Created login_logout_history table<br>";
} else {
    $error .= "✗ Error creating login_logout_history table: " . $conn->error . "<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Logout Tracking - 70K Savings & Loans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-database"></i> Logout Tracking Setup</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-4">Database Migration</h5>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <strong>Success!</strong><br>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <strong>Errors Encountered:</strong><br>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$error): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle"></i> Logout tracking has been successfully configured!
                            </div>
                            <p>The system will now track:</p>
                            <ul>
                                <li>When users login (<strong>last_login</strong>)</li>
                                <li>When users logout (<strong>last_logout</strong>)</li>
                                <li>Complete login/logout history (<strong>login_logout_history</strong> table)</li>
                            </ul>
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
