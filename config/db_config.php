<?php
/**
 * Database Configuration File
 * Contains database connection settings and constants
 */

// Database connection parameters
define('DB_HOST', 'localhost');        // Database server address
define('DB_USER', 'root');             // Database username
define('DB_PASS', '');                 // Database password
define('DB_NAME', 'savings_loans_db'); // Database name

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection status
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Define application constants
define('MANDATORY_SAVINGS', 5000);           // Minimum mandatory savings in UGX
define('LOAN_INTEREST_RATE', 0.02);         // 2% monthly interest rate
define('CURRENCY', 'UGX');                   // Currency code
define('CURRENCY_SYMBOL', 'Ush');           // Ugandan Shilling symbol
define('APP_NAME', '70K Savings & Loans');   // Application name

// Session configuration
if (!isset($_SESSION)) {
    session_start();
}

// Enable error reporting (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

?>
