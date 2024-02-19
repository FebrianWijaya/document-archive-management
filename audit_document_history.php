<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Check the user's role to determine access
$allowed_roles = ['Administrator', 'Auditor'];
if (!in_array($_SESSION['divisi'], $allowed_roles)) {
    // User does not have the required role
    echo 'Access Denied. You do not have permission to access this page.';
    exit;
}

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

// Include the header
include 'header.php';

// Include the appropriate sidebar based on the user's role
if ($_SESSION['divisi'] === 'Administrator') {
    include 'sidebar.php';
} elseif ($_SESSION['divisi'] === 'Auditor') {
    include 'audit_sidebar.php';
} elseif ($_SESSION['divisi'] === 'Admin dan Finance') {
    include 'finance_sidebar.php';
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Adjustments for Content Area */
        /* Copy the CSS styles for the table from staff.php */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Style for the search form */
        .search-form {
            margin-bottom: 20px;
        }

        .search-input {
            padding: 5px;
            width: 300px;
        }

        .search-button {
            padding: 5px 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        td.comment {
            max-width: 200px; /* Adjust the width as per your design */
            word-wrap: break-word;
        }

        /* Style for the file upload form */
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        /* Adjust this CSS to move only the input fields to the left */
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .form-group label {
            /* Keep label styles as they are */
            margin-right: 5px; /* Add a 5px right margin to create space between label and input fields */
        }

        .form-group input[type="file"],
        .form-group textarea {
            width: 70%; /* Set the width to 100% to move them to the left */
        }
    </style>
</head>
<body>

<!-- Content for the Dashboard Function -->
<div class="content">

    <?php
    if (isset($_GET['folder'])) {
        $folder = urldecode($_GET['folder']);
        list($fileType, $companyName, $years) = explode('_', $folder);
        // Folder Title
        echo "<h1>Folder: $fileType - $companyName - $years</h1>";

        // Handle file upload (the form and processing code from history.php)
        if (isset($_POST['upload'])) {
            $uploadDir = "uploads/$folder/";

            // Check if the uploaded file is of an allowed file type
            //$allowedFileTypes = array('application/pdf', 'application/vnd.ms-excel', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $allowedFileTypes = array('application/pdf');
            $uploadedFileType = $_FILES['file']['type'];

            if (in_array($uploadedFileType, $allowedFileTypes)) {
                // The uploaded file is of an allowed type

                $uploadDate = date('Ymd');

                // Generate a unique version number
                $version = 1;
                $latestVersionQuery = "SELECT MAX(version) FROM uploaded_files WHERE folder_name = ?";
                $stmt = $conn->prepare($latestVersionQuery);
                $stmt->bind_param("s", $folder);
                $stmt->execute();
                $stmt->bind_result($latestVersion);
                $stmt->fetch();
                $stmt->close();

                if ($latestVersion !== null) {
                    $version = $latestVersion + 1;
                }

                // Get the file extension
                $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                // Check the file size
                $maxFileSize = 15 * 1024 * 1024; // 15MB in bytes
                if ($_FILES['file']['size'] > $maxFileSize) {
                    echo "File size exceeds the limit of 15MB.";
                } else {
                    // Generate a unique file name based on your desired format
                    $newFileName = "{$fileType}_{$companyName}_{$years}_ver.$version.{$fileExtension}";

                    $uploadPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
                        // Save file information to the database
                        $comment = $_POST['comment'];
                        $uploadDateFormatted = date('Y-m-d');

                        // Get the division, file type, and publisher from the form
                        $division = $_POST['division'];
                        $fileType = $_POST['file_type'];
                        $publisher = $_POST['publisher'];

                        $insertQuery = "INSERT INTO uploaded_files (folder_name, file_name, company_name, upload_date, version, comment, division, file_type, publisher) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($insertQuery);
                        $stmt->bind_param("ssssissss", $folder, $newFileName, $companyName, $uploadDateFormatted, $version, $comment, $division, $fileType, $publisher);

                        if ($stmt->execute()) {
                            // Redirect back to history.php
                            header("Location: audit_document_history.php?folder=" . urlencode($folder));
                            exit;
                        } else {
                            echo "Error creating files or saving information to the database.";
                        }

                        $stmt->close();
                    } else {
                        echo "Error uploading file.";
                    }
                }
            } else {
                // The uploaded file is not of an allowed type
                // echo "File type not allowed. Please upload a PDF, xlsx, xls, docx and doc files.";
                echo "File type not allowed. Please upload a PDF files.";
            }
        }
        ?>

<!-- File Upload Form -->
<h2>Upload Files to Selected Folder</h2>
<!-- <p><strong>Allowed file types: PDF, xlsx, xls, docx and doc files</strong></p>-->
<p><strong>Allowed file types: PDF files</strong></p>
<form action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="file">Select File to Upload (Max 15MB) :</label>
        <input type="file" name="file" id="file" required>
    </div>
    <div class="form-group">
        <label for="comment">Comment:</label>
        <textarea name="comment" id="comment" required></textarea>
    </div>
    
    <!-- Hidden input fields to capture division, file type, and publisher -->
    <input type="hidden" name="division" value="Auditor"> <!-- You can set the division as needed -->
    <input type="hidden" name="file_type" value="document"> <!-- You can set the file type as needed -->
    <input type="hidden" name="publisher" value="<?php echo $_SESSION['username']; ?>">
    
    <input type="submit" name="upload" value="Upload File">
</form>

        <!-- Display Uploaded Files in a Table -->
        <h2>Uploaded Files</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Upload Date</th>
                    <th>Publisher</th>
                    <th>Version</th>
                    <th>Comment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // select files with the specified division and file type
                $selectFilesQuery = "SELECT file_name, upload_date, publisher, version, comment FROM uploaded_files WHERE folder_name = ? AND division = 'Auditor' AND file_type = 'document'";
                $stmt = $conn->prepare($selectFilesQuery);
                $stmt->bind_param("s", $folder);
                $stmt->execute();
                $stmt->bind_result($fileName, $uploadDate, $filePublisher, $fileVersion, $fileComment);


                while ($stmt->fetch()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($fileName) . '</td>';
                    echo '<td>' . htmlspecialchars($uploadDate) . '</td>';
                    echo '<td>' . htmlspecialchars($filePublisher) . '</td>'; // Display the Publisher information
                    echo '<td>ver.' . htmlspecialchars($fileVersion) . '</td>';
                    
                    // Apply the 'comment' class to the comment cell and use a <div> for word wrapping
                    echo '<td class="comment"><div>' . htmlspecialchars($fileComment) . '</div></td>';

                    // Style the action buttons (View, Download, and Delete) inline with existing class
                    echo '<td class="action-buttons">';
                    
                    echo '<a class="view-button" href="view_file.php?folder=' . urlencode($folder) . '&file=' . urlencode($fileName) . '" target="_blank" style="background-color: #00CC66; color: #fff; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; display: inline-block; padding: 5px 10px; margin-right: 5px;">View</a>|';
                    echo '<a class="download-button" href="download_file.php?folder=' . urlencode($folder) . '&file=' . urlencode($fileName) . '" style="background-color: #007BFF; color: #fff; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; display: inline-block; padding: 5px 10px;margin-left: 5px; margin-right: 5px;">Download</a>|';
                    
                    // Check if the user's role is "Administrator" before displaying the delete button
                    if ($_SESSION['divisi'] === 'Administrator') {
                        echo '<a class="delete-button" href="delete_audit_document.php?folder=' . urlencode($folder) . '&file=' . urlencode($fileName) . '" style="background-color: #FF4D4D; color: #fff; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; display: inline-block; padding: 5px 10px;margin-left: 5px; margin-right: 5px;">Delete</a>';
                    }
                    echo '</td>';
                    
                    echo '</tr>';
                }

                $stmt->close();
                ?>
            </tbody>
        </table>
    <?php
    } else {
        echo "Folder not specified.";
    }
    ?>
</div>

</body>
</html>