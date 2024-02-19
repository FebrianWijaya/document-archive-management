<?php
// Database configuration (Update these values with your actual database credentials)
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'db_dms';

// Get the folder name to delete from the query parameter
if (isset($_GET['folder'])) {
    $folderNameToDelete = $_GET['folder'];

    if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
        // Connect to the database
        $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Delete records from the uploaded_files table with the same folder_name
        $deleteFilesQuery = "DELETE FROM uploaded_files WHERE folder_name = ?";
        $stmtFiles = $conn->prepare($deleteFilesQuery);
        $stmtFiles->bind_param("s", $folderNameToDelete);

        // Delete the record from the folders table
        $deleteFolderQuery = "DELETE FROM folders WHERE folder_name = ?";
        $stmtFolder = $conn->prepare($deleteFolderQuery);
        $stmtFolder->bind_param("s", $folderNameToDelete);

        // Delete the folder from the server
        $uploadPath = "uploads/$folderNameToDelete";
        if (is_dir($uploadPath)) {
            // Recursive function to delete directory and its contents
            function deleteDirectory($path) {
                if (is_dir($path)) {
                    $files = glob($path . '/*');
                    foreach ($files as $file) {
                        is_dir($file) ? deleteDirectory($file) : unlink($file);
                    }
                    rmdir($path);
                }
            }

            if ($stmtFiles->execute() && $stmtFolder->execute()) {
                deleteDirectory($uploadPath);
                $message = "Folder and associated database records deleted successfully.";
            } else {
                $message = "Error deleting associated database records.";
            }

            $stmtFiles->close();
            $stmtFolder->close();
        } else {
            $message = "Folder not found on the server.";
        }

        $conn->close();
    } else {
        // Display a confirmation dialog
        echo "<script>
                var confirmDelete = confirm('Are you sure you want to delete this folder and its contents?');
                if (confirmDelete) {
                    window.location.href = 'delete_folder.php?folder=$folderNameToDelete&confirm=true';
                } else {
                    window.history.back();
                }
              </script>";
    }
} else {
    $message = "Folder parameter not provided.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deletion Result</title>
</head>
<body>
    <script>
        // Display a confirmation message and redirect to the previous page
        alert("<?php echo $message; ?>");
        window.history.back();
    </script>
</body>
</html>
