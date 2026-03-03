<?php
/**
 * PDF Generator for Member Statements
 * Generates PDF statements for member savings
 */

/**
 * Generate Member Savings Statement PDF
 * @param object $conn Database connection
 * @param int $member_id Member ID
 * @return string PDF file path or error message
 */
function generateSavingsStatementPDF($conn, $member_id) {
    // Get member details
    $member_query = "SELECT id, full_name, email, phone, savings_amount, date_joined FROM members WHERE id = ?";
    $member_stmt = $conn->prepare($member_query);
    $member_stmt->bind_param('i', $member_id);
    $member_stmt->execute();
    $member_result = $member_stmt->get_result();
    
    if ($member_result->num_rows === 0) {
        return false; // Member not found
    }
    
    $member = $member_result->fetch_assoc();
    
    // Get savings history
    $savings_query = "SELECT id, savings_amount, savings_type, payment_method, receipt_number, notes, savings_date 
                     FROM savings WHERE member_id = ? ORDER BY savings_date DESC";
    $savings_stmt = $conn->prepare($savings_query);
    $savings_stmt->bind_param('i', $member_id);
    $savings_stmt->execute();
    $savings_result = $savings_stmt->get_result();
    $savings_list = $savings_result->fetch_all(MYSQLI_ASSOC);
    
    // Create HTML content for PDF
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
            .member-info {
                background: #f8f9fa;
                padding: 15px;
                margin-bottom: 20px;
                border-left: 4px solid #1a5490;
            }
            .member-info p {
                margin: 8px 0;
                font-size: 13px;
            }
            .member-info strong {
                min-width: 120px;
                display: inline-block;
            }
            .summary {
                display: flex;
                gap: 20px;
                margin-bottom: 20px;
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
            .date-column {
                width: 100px;
            }
            .method-column {
                background: #e8f4f8;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 11px;
                display: inline-block;
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
            .no-records {
                padding: 20px;
                text-align: center;
                color: #999;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>70K Savings & Loans Management System</h1>
            <p>Member Savings Statement</p>
            <p>Generated on: ' . date('d F Y, H:i:s') . '</p>
        </div>

        <div class="member-info">
            <p><strong>Member Name:</strong> ' . htmlspecialchars($member['full_name']) . '</p>
            <p><strong>Member ID:</strong> ' . $member['id'] . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($member['email']) . '</p>
            <p><strong>Phone:</strong> ' . htmlspecialchars($member['phone']) . '</p>
            <p><strong>Member Since:</strong> ' . date('d F Y', strtotime($member['date_joined'])) . '</p>
        </div>

        <div class="summary">
            <div class="summary-box">
                <h4>Total Savings</h4>
                <div class="amount">UGX ' . number_format($member['savings_amount'], 0) . '</div>
            </div>
            <div class="summary-box">
                <h4>Savings Entries</h4>
                <div class="amount">' . count($savings_list) . '</div>
            </div>
            <div class="summary-box">
                <h4>Statement Date</h4>
                <div class="amount">' . date('d/m/Y') . '</div>
            </div>
        </div>

        <h3 style="color: #1a5490; margin-top: 30px;">Savings History</h3>
        
        ' . (count($savings_list) > 0 ? '
        <table>
            <thead>
                <tr>
                    <th class="date-column">Date</th>
                    <th>Type</th>
                    <th>Method</th>
                    <th>Receipt Number</th>
                    <th>Notes</th>
                    <th class="amount-column" style="width: 120px;">Amount (UGX)</th>
                </tr>
            </thead>
            <tbody>
                ' . generateSavingsTableRows($savings_list) . '
            </tbody>
        </table>
        ' : '<div class="no-records">No savings records found</div>') . '

        <div class="footer">
            <p>This is an official statement of member savings generated from the 70K Savings & Loans Management System.</p>
            <p>For inquiries, please contact the administrator.</p>
            <p style="margin-top: 15px; font-size: 10px; color: #999;">Document Reference: MBR-' . $member['id'] . '-' . date('YmdHis') . '</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Generate table rows for savings history
 * @param array $savings_list Array of savings records
 * @return string HTML table rows
 */
function generateSavingsTableRows($savings_list) {
    $html = '';
    foreach ($savings_list as $saving) {
        $date = date('d/m/Y', strtotime($saving['savings_date']));
        $type = ucfirst($saving['savings_type']);
        $method = ucfirst(str_replace('_', ' ', $saving['payment_method']));
        $receipt = htmlspecialchars($saving['receipt_number']);
        $notes = htmlspecialchars(isset($saving['notes']) ? $saving['notes'] : '');
        $amount = number_format($saving['savings_amount'], 0);
        
        $html .= "
        <tr>
            <td class='date-column'>$date</td>
            <td>$type</td>
            <td><span class='method-column'>$method</span></td>
            <td>$receipt</td>
            <td>$notes</td>
            <td class='amount-column'>$amount</td>
        </tr>";
    }
    return $html;
}

/**
 * Output HTML as PDF using mPDF or native PHP
 * This uses a simple HTML to PDF conversion approach
 * @param string $html HTML content
 * @param string $filename PDF filename
 * @return void
 */
function outputPDF($html, $filename) {
    // Check if mPDF is available
    if (class_exists('Mpdf\Mpdf')) {
        try {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output($filename, 'D');
            return;
        } catch (Exception $e) {
            // Fall back to alternative method
        }
    }
    
    // Check if TCPDF is available
    if (class_exists('TCPDF')) {
        try {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(TRUE, 10);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output($filename, 'D');
            return;
        } catch (Exception $e) {
            // Fall back to alternative method
        }
    }
    
    // Fallback: Output as HTML with PDF headers (browser will print)
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.html"');
    echo $html;
}
?>
