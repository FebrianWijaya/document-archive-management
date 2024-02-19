<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
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
if ($_SESSION['divisi'] === 'Administrator' || $_SESSION['divisi'] === 'Auditor' || $_SESSION['divisi'] === 'Admin dan Finance') {
    include 'header.php'; // Include header.php for specified divisions
} else {
    include 'client_header.php'; // Include client_header.php for other divisions
}

// Include the appropriate sidebar based on the user's role
if ($_SESSION['divisi'] === 'Administrator') {
    include 'sidebar.php';
} elseif ($_SESSION['divisi'] === 'Auditor') {
    include 'audit_sidebar.php';
} elseif ($_SESSION['divisi'] === 'Admin dan Finance') {
    include 'finance_sidebar.php';
} else {
    include 'client_sidebar.php'; // Default sidebar for other divisions
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

        // Display Uploaded Files in a Table
        echo '<h2>Uploaded Files</h2>';
        echo '<table border="1">';
        echo '<thead>
                <tr>
                    <th>File Name</th>
                    <th>Upload Date</th>
                    <th>Publisher</th>
                    <th>Version</th>
                    <th>Comment</th>
                    <th>Action</th>
                </tr>
              </thead>';
        echo '<tbody>';

        // Select files with the specified division and file type
        $selectFilesQuery = "SELECT file_name, upload_date, publisher, version, comment FROM uploaded_files WHERE folder_name = ?";
        $stmt = $conn->prepare($selectFilesQuery);
        $stmt->bind_param("s", $folder);
        $stmt->execute();
        $stmt->bind_result($fileName, $uploadDate, $filePublisher, $fileVersion, $fileComment);
        

        while ($stmt->fetch()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($fileName) . '</td>';
            echo '<td>' . htmlspecialchars($uploadDate) . '</td>';
            echo '<td>' . htmlspecialchars($filePublisher) . '</td>'; // Display the Publisher information
            echo '<td>ver' . htmlspecialchars($fileVersion) . '</td>';

            // Apply the 'comment' class to the comment cell and use a <div> for word wrapping
            echo '<td class="comment"><div>' . htmlspecialchars($fileComment) . '</div></td>';

            // Style the action buttons (View, Download, and Delete) inline with existing class
            echo '<td class="action-buttons">';

            echo '<a class="view-button" href="view_file.php?folder=' . urlencode($folder) . '&file=' . urlencode($fileName) . '" target="_blank" style="background-color: #00CC66; color: #fff; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; display: inline-block; padding: 5px 10px; margin-right: 5px;">View</a>|';
            echo '<a class="download-button" href="download_file.php?folder=' . urlencode($folder) . '&file=' . urlencode($fileName) . '" style="background-color: #007BFF; color: #fff; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; display: inline-block; padding: 5px 10px;margin-left: 5px; margin-right: 5px;">Download</a>';

            // Check if the user's role is "Administrator" before displaying the delete button
            if ($_SESSION['divisi'] === 'Administrator') {
                echo '<a class="delete-button" href="delete_admin_surat.php?folder=' . urlencode($folder) . '&file=' . urlencode($fileName) . '" style="background-color: #FF4D4D; color: #fff; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; display: inline-block; padding: 5px 10px;margin-left: 5px; margin-right: 5px;">Delete</a>';
            }
            echo '</td>';

            echo '</tr>';
        }

        $stmt->close();
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "Folder not specified.";
    }
    ?>
</div>

</body>
</html>
