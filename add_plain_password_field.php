<?php
/**
 * Add Plain Password Field
 * Adds plain_password column to users table for admin reference
 */

require_once 'config/db_config.php';

$error = '';
$success = '';

// Add plain_password column if it doesn't exist
$alter_users_sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS plain_password VARCHAR(255) COMMENT 'Plain text password for admin reference'";
if ($conn->query($alter_users_sql)) {
    $success .= "✓ Added plain_password column to users table<br>";
} else {
    $error .= "✗ Error adding plain_password column: " . $conn->error . "<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Plain Password Field - 70K Savings & Loans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-database"></i> Add Plain Password Field</h4>
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
                                <i class="fas fa-check-circle"></i> Plain password field has been successfully added!
                            </div>
                            <p>The admin can now view member usernames and plain passwords in the members table.</p>
                            <p><strong>Note:</strong> Plain passwords are stored for admin reference when users register or when passwords are reset.</p>
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
