<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Preview Document</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #000;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Preview Document</h2>
    <?php
    require 'vendor/autoload.php'; // Load PHPOffice/PhpSpreadsheet and PHPOffice/PhpWord libraries

    use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory; // Alias the PhpSpreadsheet IOFactory
    use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory; // Alias the PhpWord IOFactory

    if (isset($_GET['folder']) && isset($_GET['file'])) {
        $folder = urldecode($_GET['folder']);
        $fileName = urldecode($_GET['file']);

        // Define the path to the original file
        $originalFilePath = "uploads/$folder/$fileName";
        $fileExtension = pathinfo($originalFilePath, PATHINFO_EXTENSION);

        // Check if the file exists
        if (file_exists($originalFilePath)) {
            if ($fileExtension === 'pdf') {
                // If it's a PDF, display it as is
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="original.pdf"');
                readfile($originalFilePath);
            } elseif ($fileExtension === 'xlsx' || $fileExtension === 'xls') {
                // If it's an Excel file, use PHPOffice/PhpSpreadsheet to display it as an HTML table
                $spreadsheet = PhpSpreadsheetIOFactory::load($originalFilePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $data = $worksheet->toArray();

                echo '<table>';
                foreach ($data as $row) {
                    echo '<tr>';
                    foreach ($row as $cell) {
                        echo '<td>' . htmlspecialchars($cell) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
            } elseif ($fileExtension === 'docx' || $fileExtension === 'doc') {
                // If it's a Word document, use PHPOffice/PhpWord to display it as HTML content
                $phpWord = PhpWordIOFactory::load($originalFilePath);
                $htmlWriter = PhpWordIOFactory::createWriter($phpWord, 'HTML');
                $htmlWriter->save('php://output');
            } else {
                echo "Unsupported file format.";
            }
        } else {
            echo "File not found.";
        }
    } else {
        echo "Invalid request.";
    }
    ?>
</body>
</html>
