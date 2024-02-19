<?php
if (isset($_GET['folder']) && isset($_GET['file'])) {
    $folder = urldecode($_GET['folder']);
    $file = urldecode($_GET['file']);

    if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
        // This part of the code will run after the confirmation is accepted
        $filePath = "uploads/$folder/$file";

        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                // File successfully deleted from the server

                // Database configuration
                $dbHost = 'localhost';
                $dbUsername = 'root';
                $dbPassword = '';
                $dbName = 'db_dms';

                // Create a database connection
                $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Delete the corresponding database record
                $deleteQuery = "DELETE FROM uploaded_files WHERE folder_name = ? AND file_name = ?";
                $stmt = $conn->prepare($deleteQuery);
                $stmt->bind_param("ss", $folder, $file);

                if ($stmt->execute()) {
                    // Database record deleted successfully

                    // Redirect back to audit_surat_history.php
                    header("Location: audit_surat_history.php?folder=" . urlencode($folder));
                    exit;
                } else {
                    echo "Error deleting database record.";
                }

                $stmt->close();
                $conn->close();
            } else {
                echo "Error deleting file.";
            }
        } else {
            echo "File not found: $filePath";
        }
    } else {
        // Display a confirmation dialog
        echo "<script>
            var confirmDelete = confirm('Are you sure you want to delete this file?');
            if (confirmDelete) {
                window.location.href = 'delete_audit_surat.php?folder=$folder&file=$file&confirm=true';
            } else {
                window.history.back();
            }
        </script>";
    }
} else {
    echo "Invalid request.";
}
?>
