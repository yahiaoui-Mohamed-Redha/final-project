<?php
// Include database configuration
include 'config.php';
session_start();

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    // Redirect to the login page or show an error message
    header('location: ../dist/index.php');
    exit();
}

// Get the export format from the URL
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

// Fetch all users from the database
$stmt = $conn->prepare("SELECT u.user_id, u.username, u.nom, u.prenom, u.email, e.etablissement_name, 
                        CASE 
                            WHEN u.role_id = 1 THEN 'Admin' 
                            WHEN u.role_id = 2 THEN 'Technicien' 
                            ELSE 'Receveur' 
                        END AS role_name,
                        CASE 
                            WHEN u.etat_compte = 1 THEN 'Activé' 
                            ELSE 'Désactivé' 
                        END AS etat_compte_text
                        FROM Users u 
                        LEFT JOIN Epost e ON u.postal_code = e.postal_code
                        ORDER BY u.user_id");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export based on the requested format
if ($format === 'excel') {
    // Excel export
    require '../vendor/autoload.php'; // Make sure you have PhpSpreadsheet installed via Composer
    
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
    // Create a new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Username');
    $sheet->setCellValue('C1', 'Nom');
    $sheet->setCellValue('D1', 'Prénom');
    $sheet->setCellValue('E1', 'Email');
    $sheet->setCellValue('F1', 'Établissement');
    $sheet->setCellValue('G1', 'Rôle');
    $sheet->setCellValue('H1', 'État du compte');
    
    // Style the header row
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    
    // Add data
    $row = 2;
    foreach ($users as $user) {
        $sheet->setCellValue('A' . $row, $user['user_id']);
        $sheet->setCellValue('B' . $row, $user['username']);
        $sheet->setCellValue('C' . $row, $user['nom']);
        $sheet->setCellValue('D' . $row, $user['prenom']);
        $sheet->setCellValue('E' . $row, $user['email']);
        $sheet->setCellValue('F' . $row, $user['etablissement_name'] ?? 'UPW Boumerdes');
        $sheet->setCellValue('G' . $row, $user['role_name']);
        $sheet->setCellValue('H' . $row, $user['etat_compte_text']);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Set the filename
    $filename = 'liste_utilisateurs_' . date('Y-m-d') . '.xlsx';
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Save to output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} else {
    // PDF export
    require_once '../vendor/autoload.php'; // Make sure you have TCPDF installed via Composer
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('UPW Boumerdes');
    $pdf->SetAuthor('Admin System');
    $pdf->SetTitle('Liste des Utilisateurs');
    $pdf->SetSubject('Liste des Utilisateurs');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'Liste des Utilisateurs', 'Généré le ' . date('d/m/Y H:i:s'));
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Create the table content
    $html = '<table border="1" cellpadding="5">
        <thead>
            <tr style="background-color: #0455b7; color: white; font-weight: bold;">
                <th>ID</th>
                <th>Username</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Établissement</th>
                <th>Rôle</th>
                <th>État</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($users as $user) {
        $html .= '<tr>
            <td>' . $user['user_id'] . '</td>
            <td>' . htmlspecialchars($user['username']) . '</td>
            <td>' . htmlspecialchars($user['nom']) . '</td>
            <td>' . htmlspecialchars($user['prenom']) . '</td>
            <td>' . htmlspecialchars($user['email']) . '</td>
            <td>' . htmlspecialchars($user['etablissement_name'] ?? 'UPW Boumerdes') . '</td>
            <td>' . $user['role_name'] . '</td>
            <td>' . $user['etat_compte_text'] . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdf->Output('liste_utilisateurs_' . date('Y-m-d') . '.pdf', 'D');
    exit;
}
?>