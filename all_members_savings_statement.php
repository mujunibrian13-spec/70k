<?php
/**
 * All Members Savings Statement PDF
 * Generates a comprehensive PDF statement containing all member savings
 * excluding Ariganyira Alison
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';
require_once 'config/pdf_generator.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(array('error' => 'Unauthorized access'));
    exit();
}

// Get all members except Ariganyira Alison with their savings
$query = "
    SELECT 
        m.id,
        m.full_name,
        m.email,
        m.phone,
        m.savings_amount,
        m.date_joined,
        COUNT(s.id) as transaction_count
    FROM members m
    LEFT JOIN savings s ON m.id = s.member_id
    WHERE m.full_name != 'Ariganyira Alison'
    GROUP BY m.id
    ORDER BY m.full_name ASC
";

$result = $conn->query($query);

if (!$result) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(array('error' => 'Database error: ' . $conn->error));
    exit();
}

$members = $result->fetch_all(MYSQLI_ASSOC);

if (count($members) === 0) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(array('error' => 'No members found'));
    exit();
}

// Calculate totals
$total_savings = 0;
$total_members = count($members);
foreach ($members as $member) {
    $total_savings += $member['savings_amount'];
}

// Generate HTML content for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1a5490;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            justify-content: flex-start;
        }
        .summary-box {
            padding: 15px;
            background: #f0f7ff;
            border-left: 4px solid #17a2b8;
            flex: 0 0 auto;
            min-width: 200px;
        }
        .summary-box h4 {
            margin: 0 0 10px 0;
            color: #17a2b8;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-box .amount {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        table thead {
            background: #1a5490;
            color: white;
        }
        table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #1a5490;
        }
        table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }
        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        .amount-column {
            text-align: right;
            font-weight: bold;
            color: #28a745;
        }
        .name-column {
            font-weight: 500;
            color: #1a5490;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
        }
        .note {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>70K Savings & Loans Management System</h1>
        <p>Complete Members Savings Statement</p>
        <p>Generated on: ' . date('d F Y, H:i:s') . '</p>
    </div>

    <div class="note">
        <strong>Note:</strong> This statement includes all member savings excluding Ariganyira Alison.
    </div>

    <div class="summary">
        <div class="summary-box">
            <h4>Total Members</h4>
            <div class="amount">' . $total_members . '</div>
        </div>
        <div class="summary-box">
            <h4>Total Savings</h4>
            <div class="amount">UGX ' . number_format($total_savings, 0) . '</div>
        </div>
        <div class="summary-box">
            <h4>Average per Member</h4>
            <div class="amount">UGX ' . number_format($total_members > 0 ? $total_savings / $total_members : 0, 0) . '</div>
        </div>
    </div>

    <h3 style="color: #1a5490; margin-top: 30px;">Member Savings Overview</h3>
    <table>
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Date Joined</th>
                <th style="text-align: center;">Transactions</th>
                <th class="amount-column" style="width: 150px;">Total Savings (UGX)</th>
            </tr>
        </thead>
        <tbody>
            ' . generateAllMembersRows($members) . '
        </tbody>
    </table>

    <div class="footer">
        <p>This is an official comprehensive statement of all member savings generated from the 70K Savings & Loans Management System.</p>
        <p>For inquiries, please contact the administrator.</p>
        <p style="margin-top: 15px; font-size: 10px; color: #999;">Report Generated: ' . date('Y-m-d H:i:s') . '</p>
    </div>
</body>
</html>';

// Output PDF
outputPDF($html, 'All_Members_Savings_Statement_' . date('Y-m-d') . '.pdf');

/**
 * Generate table rows for all members
 * @param array $members Array of member records
 * @return string HTML table rows
 */
function generateAllMembersRows($members) {
    $html = '';
    foreach ($members as $member) {
        $name = htmlspecialchars($member['full_name']);
        $email = htmlspecialchars($member['email']);
        $phone = htmlspecialchars($member['phone']);
        $date = date('d/m/Y', strtotime($member['date_joined']));
        $transactions = $member['transaction_count'];
        $savings = number_format($member['savings_amount'], 0);
        
        $html .= "
        <tr>
            <td class='name-column'>$name</td>
            <td>$email</td>
            <td>$phone</td>
            <td>$date</td>
            <td style='text-align: center;'>$transactions</td>
            <td class='amount-column'>$savings</td>
        </tr>";
    }
    return $html;
}
?>
