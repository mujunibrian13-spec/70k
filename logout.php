<?php
/**
 * Logout Page
 * Destroys user session and logs out
 */

require_once 'config/db_config.php';

session_start();

// Record logout time before destroying session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Ensure last_logout column exists
    $alter_users = "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_logout DATETIME";
    $conn->query($alter_users);
    
    // Update last_logout timestamp
    $logout_query = "UPDATE users SET last_logout = NOW(), updated_at = NOW() WHERE id = ?";
    $logout_stmt = $conn->prepare($logout_query);
    if ($logout_stmt) {
        $logout_stmt->bind_param('i', $user_id);
        $logout_stmt->execute();
    }
    
    // Ensure login_logout_history table exists
    $create_history = "CREATE TABLE IF NOT EXISTS login_logout_history (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        action VARCHAR(20) NOT NULL,
        action_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        INDEX idx_user_id (user_id),
        INDEX idx_action_time (action_time)
    )";
    $conn->query($create_history);
    
    // Log the logout action
    $log_query = "INSERT INTO login_logout_history (user_id, action, action_time) VALUES (?, 'logout', NOW())";
    $log_stmt = $conn->prepare($log_query);
    if ($log_stmt) {
        $log_stmt->bind_param('is', $user_id, 'logout');
        $log_stmt->execute();
    }
}

// Destroy session
session_destroy();

// Redirect to login
header("Location: login.php?logout=true");
exit();
?>
