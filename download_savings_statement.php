<?php
/**
 * Download Member Savings Statement as PDF
 * Generates and downloads a PDF statement for the logged-in member
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';
require_once 'config/pdf_generator.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(array('error' => 'Not authenticated'));
    exit();
}

// Get member ID - from session (for members) or from parameter (for admin)
$member_id = null;

if (isAdmin() && isset($_GET['member_id'])) {
    // Admin can download any member's statement
    $member_id = intval($_GET['member_id']);
} else if (isset($_SESSION['member_id'])) {
    // Regular member downloads their own statement
    $member_id = $_SESSION['member_id'];
} else {
    // Fallback: Try to find member from user_id
    $user_query = "SELECT id FROM members WHERE user_id = ? LIMIT 1";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param('i', $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $member_id = $user_row['id'];
    }
}

if (!$member_id) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(array('error' => 'Member not found'));
    exit();
}

// Generate PDF
$html = generateSavingsStatementPDF($conn, $member_id);

if (!$html) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(array('error' => 'Member not found or no data available'));
    exit();
}

// Get member name for filename
$member_query = "SELECT full_name FROM members WHERE id = ?";
$member_stmt = $conn->prepare($member_query);
$member_stmt->bind_param('i', $member_id);
$member_stmt->execute();
$member_result = $member_stmt->get_result();
$member = $member_result->fetch_assoc();

if (!$member) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(array('error' => 'Member not found'));
    exit();
}

// Create filename
$safe_name = preg_replace('/[^a-zA-Z0-9-]/', '_', $member['full_name']);
$filename = 'Savings_Statement_' . $safe_name . '_' . date('Y-m-d') . '.pdf';

// Output PDF
outputPDF($html, $filename);
?>
