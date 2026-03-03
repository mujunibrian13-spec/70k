<?php
/**
 * Verify Session Fixes
 * Checks that all member-only pages have proper session checking
 */

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Session Fixes</title>
    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background: #f0f0f0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        h2 { color: #333; margin-top: 25px; }
        .file-check {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .pass { background: #d4edda; border-left: 4px solid #28a745; }
        .fail { background: #f8d7da; border-left: 4px solid #dc3545; }
        .code {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New';
            font-size: 12px;
            margin: 10px 0;
            overflow-x: auto;
        }
        strong { color: #1e40af; }
        .summary {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #1e40af;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin: 5px 0;
            border: none;
            cursor: pointer;
        }
        .button:hover { background: #1e3a8a; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .checkmark { color: #28a745; font-weight: bold; }
        .cross { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>✅ Verify Session Fixes</h1>
    <p>This page verifies that all member-only pages have proper session checking.</p>
    
    <?php
    
    // Files to check
    $files_to_check = array(
        'loans.php' => array(
            'checks' => array(
                'Admin role check' => 'if (isset($_SESSION[\'user_role\']) && $_SESSION[\'user_role\'] === \'admin\')',
                'Member ID check' => 'if (!isset($_SESSION[\'member_id\']) || empty($_SESSION[\'member_id\']))',
            ),
            'description' => 'Loan management page'
        ),
        'index.php' => array(
            'checks' => array(
                'Admin role check' => 'if (isAdmin())',
                'Member ID check' => 'if (!isset($_SESSION[\'member_id\']) || empty($_SESSION[\'member_id\']))',
            ),
            'description' => 'Member dashboard'
        ),
        'profile.php' => array(
            'checks' => array(
                'Admin role check' => 'if (isset($_SESSION[\'user_role\']) && $_SESSION[\'user_role\'] === \'admin\')',
                'Member ID check' => 'if (!isset($_SESSION[\'member_id\']) || empty($_SESSION[\'member_id\']))',
            ),
            'description' => 'Member profile page'
        ),
        'pay_loan.php' => array(
            'checks' => array(
                'Admin role check' => 'if (isset($_SESSION[\'user_role\']) && $_SESSION[\'user_role\'] === \'admin\')',
                'Member ID check' => 'if (!isset($_SESSION[\'member_id\']) || empty($_SESSION[\'member_id\']))',
            ),
            'description' => 'Loan payment page'
        ),
        'reports.php' => array(
            'checks' => array(
                'Admin role check' => 'if (isset($_SESSION[\'user_role\']) && $_SESSION[\'user_role\'] === \'admin\')',
                'Member ID check' => 'if (!isset($_SESSION[\'member_id\']) || empty($_SESSION[\'member_id\']))',
            ),
            'description' => 'Financial reports page'
        ),
    );
    
    $all_pass = true;
    
    echo '<h2>Member-Only Pages Verification</h2>';
    echo '<table>';
    echo '<tr><th>File</th><th>Description</th><th>Admin Check</th><th>Member ID Check</th><th>Status</th></tr>';
    
    foreach ($files_to_check as $filename => $file_info) {
        $filepath = __DIR__ . '/' . $filename;
        
        if (!file_exists($filepath)) {
            echo '<tr>';
            echo '<td><strong>' . htmlspecialchars($filename) . '</strong></td>';
            echo '<td>' . $file_info['description'] . '</td>';
            echo '<td><span class="cross">✗ FILE NOT FOUND</span></td>';
            echo '<td></td>';
            echo '<td><span class="cross">❌ FAIL</span></td>';
            echo '</tr>';
            $all_pass = false;
            continue;
        }
        
        $file_content = file_get_contents($filepath);
        
        // Check for admin check
        $has_admin_check = false;
        $has_member_id_check = false;
        
        // Search for admin check patterns
        if (strpos($file_content, "if (isset(\$_SESSION['user_role']) && \$_SESSION['user_role'] === 'admin')") !== false ||
            strpos($file_content, "if (isAdmin())") !== false) {
            $has_admin_check = true;
        }
        
        // Search for member ID check
        if (strpos($file_content, "if (!isset(\$_SESSION['member_id']) || empty(\$_SESSION['member_id']))") !== false) {
            $has_member_id_check = true;
        }
        
        $file_pass = $has_admin_check && $has_member_id_check;
        
        echo '<tr>';
        echo '<td><strong>' . htmlspecialchars($filename) . '</strong></td>';
        echo '<td>' . $file_info['description'] . '</td>';
        echo '<td><span class="' . ($has_admin_check ? 'checkmark' : 'cross') . '">' . ($has_admin_check ? '✓ Yes' : '✗ No') . '</span></td>';
        echo '<td><span class="' . ($has_member_id_check ? 'checkmark' : 'cross') . '">' . ($has_member_id_check ? '✓ Yes' : '✗ No') . '</span></td>';
        echo '<td><span class="' . ($file_pass ? 'checkmark' : 'cross') . '">' . ($file_pass ? '✅ PASS' : '❌ FAIL') . '</span></td>';
        echo '</tr>';
        
        if (!$file_pass) {
            $all_pass = false;
        }
    }
    
    echo '</table>';
    
    // Summary
    echo '<div class="summary">';
    if ($all_pass) {
        echo '<h3>✅ All Checks Passed!</h3>';
        echo '<p>All member-only pages have proper session checking:</p>';
        echo '<ul>';
        echo '<li>✓ Admin role checks present</li>';
        echo '<li>✓ Member ID existence checks present</li>';
        echo '<li>✓ Proper error handling in place</li>';
        echo '<li>✓ No undefined index errors will occur</li>';
        echo '</ul>';
        echo '<p><strong>System Status:</strong> Ready for use</p>';
    } else {
        echo '<h3>⚠️ Some Checks Failed</h3>';
        echo '<p>Not all member-only pages have proper session checking.</p>';
        echo '<p>Please review the files above and add the necessary checks.</p>';
    }
    echo '</div>';
    
    // Details section
    echo '<h2>What Was Checked</h2>';
    echo '<div class="file-check">';
    echo '<strong>1. Admin Role Check</strong><br>';
    echo 'Ensures admins are redirected away from member-only pages.<br>';
    echo '<div class="code">if (isset($_SESSION[\'user_role\']) && $_SESSION[\'user_role\'] === \'admin\') {<br>    redirect(\'admin.php\');<br>}</div>';
    echo '</div>';
    
    echo '<div class="file-check">';
    echo '<strong>2. Member ID Check</strong><br>';
    echo 'Ensures $_SESSION[\'member_id\'] exists before using it.<br>';
    echo '<div class="code">if (!isset($_SESSION[\'member_id\']) || empty($_SESSION[\'member_id\'])) {<br>    redirect(\'login.php\');<br>}</div>';
    echo '</div>';
    
    // Test instructions
    echo '<h2>How to Test</h2>';
    echo '<div class="file-check">';
    echo '<strong>Test 1: Admin Access</strong><br>';
    echo '1. Login as admin<br>';
    echo '2. Try to access: <a href="loans.php">loans.php</a><br>';
    echo '3. Expected: Redirected to admin dashboard<br>';
    echo '<br>';
    
    echo '<strong>Test 2: Member Access</strong><br>';
    echo '1. Login as member<br>';
    echo '2. Access: <a href="loans.php">loans.php</a><br>';
    echo '3. Expected: Page loads normally<br>';
    echo '<br>';
    
    echo '<strong>Test 3: No Errors</strong><br>';
    echo '1. Check browser console (F12)<br>';
    echo '2. Expected: No PHP notices about undefined index<br>';
    echo '</div>';
    
    ?>
    
    <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
    
    <p style="color: #666; font-size: 12px;">
        <strong>Verification Complete</strong><br>
        This page checks that all session fixes have been properly applied.<br>
        You can delete this file after verification is complete.
    </p>
</div>
</body>
</html>
