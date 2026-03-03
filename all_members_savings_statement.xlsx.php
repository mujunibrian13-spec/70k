<?php
/**
 * All Members Savings Statement Excel
 * Generates a comprehensive Excel file containing all member savings
 * excluding Ariganyira Alison
 */

require_once 'config/db_config.php';
require_once 'config/functions.php';

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

// Check if PhpSpreadsheet is available
if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    generateExcelWithPhpSpreadsheet($members, $total_savings, $total_members);
} elseif (class_exists('PHPExcel')) {
    generateExcelWithPHPExcel($members, $total_savings, $total_members);
} else {
    generateCSVAsExcel($members, $total_savings, $total_members);
}

/**
 * Generate Excel using PhpSpreadsheet
 */
function generateExcelWithPhpSpreadsheet($members, $total_savings, $total_members) {
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Font;
    use PhpOffice\PhpSpreadsheet\Style\PatternFill;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    use PhpOffice\PhpSpreadsheet\Style\Color;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Members Savings');

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(25);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(20);

    // Add title
    $sheet->setCellValue('A1', '70K Savings & Loans Management System');
    $sheet->mergeCells('A1:F1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('1a5490'));
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Add subtitle
    $sheet->setCellValue('A2', 'Complete Members Savings Statement');
    $sheet->mergeCells('A2:F2');
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new Color('1a5490'));
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Add note
    $sheet->setCellValue('A3', 'Note: This statement includes all member savings excluding Ariganyira Alison.');
    $sheet->mergeCells('A3:F3');
    $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new Color('666666'));

    // Add date generated
    $sheet->setCellValue('A4', 'Generated: ' . date('d F Y, H:i:s'));
    $sheet->mergeCells('A4:F4');
    $sheet->getStyle('A4')->getFont()->setSize(9)->setColor(new Color('666666'));

    // Add summary section
    $sheet->setCellValue('A6', 'Summary');
    $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(11)->setColor(new Color('1a5490'));

    $sheet->setCellValue('A7', 'Total Members:');
    $sheet->setCellValue('B7', $total_members);
    $sheet->getStyle('B7')->getFont()->setBold(true);

    $sheet->setCellValue('A8', 'Total Savings (UGX):');
    $sheet->setCellValue('B8', $total_savings);
    $sheet->getStyle('B8')->getFont()->setBold(true);
    $sheet->getStyle('B8')->getNumberFormat()->setFormatCode('#,##0');

    $sheet->setCellValue('A9', 'Average per Member (UGX):');
    $sheet->setCellValue('B9', $total_members > 0 ? $total_savings / $total_members : 0);
    $sheet->getStyle('B9')->getFont()->setBold(true);
    $sheet->getStyle('B9')->getNumberFormat()->setFormatCode('#,##0.00');

    // Add header row
    $row = 11;
    $headers = ['Member Name', 'Email', 'Phone', 'Date Joined', 'Transactions', 'Total Savings (UGX)'];
    $headerFill = new PatternFill(PatternFill::FILL_SOLID, '1a5490');
    $headerFont = new Font(['bold' => true, 'color' => new Color('FFFFFF')]);

    foreach ($headers as $col => $header) {
        $cell = $sheet->getCellByColumnAndRow($col + 1, $row);
        $cell->setValue($header);
        $cell->getStyle()->setFill($headerFill)->setFont($headerFont);
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Add member data
    $dataRow = $row + 1;
    foreach ($members as $member) {
        $sheet->setCellValue('A' . $dataRow, $member['full_name']);
        $sheet->setCellValue('B' . $dataRow, $member['email']);
        $sheet->setCellValue('C' . $dataRow, $member['phone']);
        $sheet->setCellValue('D' . $dataRow, date('d/m/Y', strtotime($member['date_joined'])));
        $sheet->setCellValue('E' . $dataRow, $member['transaction_count']);
        $sheet->setCellValue('F' . $dataRow, $member['savings_amount']);

        // Format savings column
        $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $dataRow++;
    }

    // Add borders to data range
    $borderStyle = new Border();
    $borderStyle->setTop(new \PhpOffice\PhpSpreadsheet\Style\BorderStyle(BorderStyle::BORDER_THIN));
    $borderStyle->setBottom(new \PhpOffice\PhpSpreadsheet\Style\BorderStyle(BorderStyle::BORDER_THIN));
    $borderStyle->setLeft(new \PhpOffice\PhpSpreadsheet\Style\BorderStyle(BorderStyle::BORDER_THIN));
    $borderStyle->setRight(new \PhpOffice\PhpSpreadsheet\Style\BorderStyle(BorderStyle::BORDER_THIN));

    $sheet->getStyle('A11:F' . ($dataRow - 1))->setBorder($borderStyle);

    // Output Excel file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="All_Members_Savings_Statement_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

/**
 * Generate Excel using PHPExcel (legacy)
 */
function generateExcelWithPHPExcel($members, $total_savings, $total_members) {
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Members Savings');

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(25);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(20);

    // Add title
    $sheet->setCellValue('A1', '70K Savings & Loans Management System');
    $sheet->mergeCells('A1:F1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new PHPExcel_Style_Color('FF1a5490'));
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    // Add subtitle
    $sheet->setCellValue('A2', 'Complete Members Savings Statement');
    $sheet->mergeCells('A2:F2');
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new PHPExcel_Style_Color('FF1a5490'));
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    // Add note
    $sheet->setCellValue('A3', 'Note: This statement includes all member savings excluding Ariganyira Alison.');
    $sheet->mergeCells('A3:F3');
    $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new PHPExcel_Style_Color('FF666666'));

    // Add date generated
    $sheet->setCellValue('A4', 'Generated: ' . date('d F Y, H:i:s'));
    $sheet->mergeCells('A4:F4');
    $sheet->getStyle('A4')->getFont()->setSize(9)->setColor(new PHPExcel_Style_Color('FF666666'));

    // Add summary section
    $sheet->setCellValue('A6', 'Summary');
    $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(11)->setColor(new PHPExcel_Style_Color('FF1a5490'));

    $sheet->setCellValue('A7', 'Total Members:');
    $sheet->setCellValue('B7', $total_members);
    $sheet->getStyle('B7')->getFont()->setBold(true);

    $sheet->setCellValue('A8', 'Total Savings (UGX):');
    $sheet->setCellValue('B8', $total_savings);
    $sheet->getStyle('B8')->getFont()->setBold(true);
    $sheet->getStyle('B8')->getNumberFormat()->setFormatCode('#,##0');

    $sheet->setCellValue('A9', 'Average per Member (UGX):');
    $sheet->setCellValue('B9', $total_members > 0 ? $total_savings / $total_members : 0);
    $sheet->getStyle('B9')->getFont()->setBold(true);
    $sheet->getStyle('B9')->getNumberFormat()->setFormatCode('#,##0.00');

    // Add header row
    $row = 11;
    $headers = ['Member Name', 'Email', 'Phone', 'Date Joined', 'Transactions', 'Total Savings (UGX)'];
    
    foreach ($headers as $col => $header) {
        $cell = chr(65 + $col) . $row;
        $sheet->setCellValue($cell, $header);
        $sheet->getStyle($cell)->getFont()->setBold(true)->setColor(new PHPExcel_Style_Color('FFFFFFFF'));
        $sheet->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FF1a5490');
        $sheet->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }

    // Add member data
    $dataRow = $row + 1;
    foreach ($members as $member) {
        $sheet->setCellValue('A' . $dataRow, $member['full_name']);
        $sheet->setCellValue('B' . $dataRow, $member['email']);
        $sheet->setCellValue('C' . $dataRow, $member['phone']);
        $sheet->setCellValue('D' . $dataRow, date('d/m/Y', strtotime($member['date_joined'])));
        $sheet->setCellValue('E' . $dataRow, $member['transaction_count']);
        $sheet->setCellValue('F' . $dataRow, $member['savings_amount']);

        $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $dataRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $dataRow++;
    }

    // Output Excel file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="All_Members_Savings_Statement_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit();
}

/**
 * Generate CSV file (as fallback)
 */
function generateCSVAsExcel($members, $total_savings, $total_members) {
    $filename = 'All_Members_Savings_Statement_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');

    // Add BOM for UTF-8
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Write title
    fputcsv($output, ['70K Savings & Loans Management System']);
    fputcsv($output, ['Complete Members Savings Statement']);
    fputcsv($output, ['Note: This statement includes all member savings excluding Ariganyira Alison.']);
    fputcsv($output, ['Generated: ' . date('d F Y, H:i:s')]);
    fputcsv($output, []);

    // Write summary
    fputcsv($output, ['Summary']);
    fputcsv($output, ['Total Members', $total_members]);
    fputcsv($output, ['Total Savings (UGX)', $total_savings]);
    fputcsv($output, ['Average per Member (UGX)', $total_members > 0 ? $total_savings / $total_members : 0]);
    fputcsv($output, []);

    // Write headers
    fputcsv($output, ['Member Name', 'Email', 'Phone', 'Date Joined', 'Transactions', 'Total Savings (UGX)']);

    // Write member data
    foreach ($members as $member) {
        fputcsv($output, [
            $member['full_name'],
            $member['email'],
            $member['phone'],
            date('d/m/Y', strtotime($member['date_joined'])),
            $member['transaction_count'],
            $member['savings_amount']
        ]);
    }

    fclose($output);
    exit();
}
?>
