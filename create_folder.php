<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

?>

<?php
// Database configuration
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'db_dms';

// Get user inputs
$division = $_POST['division'];
$documentType = $_POST['documentType'];
$fileType = $_POST['fileType'];
$companyName = $_POST['companyName'];
$years = $_POST['years'];

// Create the subfolder name without division and documentType
$folderName = "{$fileType}_{$companyName}_{$years}";

// Create the subfolder in the "uploads" directory
$uploadPath = "uploads/$folderName";
if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 0777, true);

    // Insert folder information into the database
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $insertQuery = "INSERT INTO folders (division, document_type, file_type, company_name, years, folder_name) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssssis", $division, $documentType, $fileType, $companyName, $years, $folderName);

    if ($stmt->execute()) {
        // Check division and documentType values and redirect accordingly
        if ($division === "audit" && $documentType === "dokumen") {
            header("Location: audit_document.php");
            exit;
        } elseif ($division === "audit" && $documentType === "surat") {
            header("Location: audit_surat.php");
            exit;
        } elseif ($division === "admin dan finance" && $documentType === "Transaksi Perusahaan") {
            header("Location: admin_document.php");
            exit;
        } elseif ($division === "admin dan finance" && $documentType === "surat") {
            header("Location: admin_surat.php");
            exit;
        } else {
            // Default redirection if no specific conditions match
            $referrer = $_SERVER['HTTP_REFERER'];
            header("Location: $referrer");
            exit;
        }
    } else {
        echo "Error creating folder or saving information to the database: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Folder already exists.";
}
?>
