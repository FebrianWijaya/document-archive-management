<?php
session_start();

// Establish a database connection (replace these values with your database credentials)
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'db_dms';

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Adjustments for Content Area */

        /* Style for the table */
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

        /* Common style for search input fields */
        .search-input {
            padding: 5px;
            width: 200px; /* Adjust the width as needed */
        }

        .search-button {
            padding: 5px 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-button:hover {
            opacity: 0.7; /* Add opacity when hovering */
        }
        .reset-button{
            padding: 5px 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .reset-button:hover {
            opacity: 0.7; /* Add opacity when hovering */
        }
        .add-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: opacity 0.3s; /* Add a smooth transition effect */

        }

        .add-button:hover {
            opacity: 0.7; /* Add opacity when hovering */
        }
        .history-button {
            background-color: #00CC66;
            color: #fff;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 5px;
        }
        .history-button:hover {
            opacity: 0.7; /* Add opacity when hovering */
        }

        .delete-button {
            background-color: #dc3545;
            color: #fff;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            margin-left: 5px;
        }
        .delete-button:hover {
            opacity: 0.7; /* Add opacity when hovering */
        }
        .small-folder-icon {
            width: 32px;  /* Adjust the width as needed */
            height: 32px; /* Adjust the height as needed */
            margin-right: 5px; /* Adjust the margin as needed to separate the icon from the company name */
        }
    </style>
</head>
<body>

<?php
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

<div class="header"></div>

<!-- Content for the Dashboard Function -->
<div class="content">
    <!-- Your specific content for the Dokumen Admin goes here -->
    <h1>Welcome to Dokumen Admin Management</h1>
    <p>This is where you display Dokumen Admin-related information.</p>

    <!-- Modify the search form to accept multiple years separated by commas -->
    <div class="search-form">
        <form method="GET">
            <input type="text" name="file_type" class="search-input" placeholder="File Type" value="<?= isset($_GET['file_type']) ? htmlspecialchars($_GET['file_type']) : ''; ?>">
            <input type="text" name="years" class="search-input" placeholder="Years" value="<?= isset($_GET['years']) ? htmlspecialchars($_GET['years']) : ''; ?>">
            <button type="submit" class="search-button">Search</button>
            <button type="button" class="reset-button" onclick="resetSearchForm()">Reset</button>
        </form>
    </div>

    <?php
    // Modify your SQL query to include the selected file type, folder name, and search criteria
    $fileType = isset($_GET['file_type']) ? mysqli_real_escape_string($conn, $_GET['file_type']) : '';
    $years = isset($_GET['years']) ? mysqli_real_escape_string($conn, $_GET['years']) : '';

    // Check if a specific file type is selected
    if (!empty($fileType) || !empty($years)) {
        echo "<h2>Folders for File Type: $fileType and Years: $years</h2>";

        // Modify the SQL query to include the selected file type, session divisi, and search criteria
        $foldersQuery = $conn->prepare("SELECT folder_name, file_type, years FROM folders 
                WHERE (division = 'admin dan finance' OR division = 'audit') 
                AND file_type LIKE ?  -- Change this condition to use LIKE
                AND years LIKE ?  -- Add condition to search for years
                AND company_name = ? 
                ORDER BY years ASC"); // or ASC for ascending order
        $likeFileType = "%$fileType%"; // Add % to search for any occurrence of the file type
        $likeYears = "%$years%"; // Add % to search for any occurrence of the years
        $foldersQuery->bind_param("sss", $likeFileType, $likeYears, $_SESSION['divisi']);  // Bind the session company_name
        $foldersQuery->execute();
        $foldersResult = $foldersQuery->get_result();
  

        if (!$foldersResult) {
            die("Folders query failed: " . mysqli_error($conn));
        }

        // Display a table of folders for the selected file type with optional search criteria
        echo '<table>';
        echo '<tr>
                <th>Folder Name</th>
                <th>File Type</th>
                <th>Years</th>
                <th>Actions</th>
            </tr>';

        while ($folderRow = mysqli_fetch_assoc($foldersResult)) {
            $folderName = htmlspecialchars($folderRow['folder_name']);
            $fileType = htmlspecialchars($folderRow['file_type']);
            $years = htmlspecialchars($folderRow['years']);

            echo '<tr>';
            echo "<td>$folderName</td>";
            echo "<td>$fileType</td>";
            echo "<td>$years</td>";
            echo '<td>';
            echo "<a href='client_document_history.php?folder=$folderName' class='history-button'>History</a>";

            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';

        // Close the folders query result
        mysqli_free_result($foldersResult);
    } else {
      
        // Display List of Unique File Types only when no specific file type is selected
        echo '<h2>List of File Types</h2>';

        // Query to retrieve distinct file types for both divisions
        $fileTypeQuery = $conn->prepare("SELECT DISTINCT file_type FROM folders 
            WHERE (division = 'admin dan finance' OR division = 'audit')");
        $fileTypeQuery->execute();
        $fileTypeResult = $fileTypeQuery->get_result();

        if (!$fileTypeResult) {
            die("File type query failed: " . mysqli_error($conn));
        }

        // Display file types as a table
        echo '<table>';
        echo '<tr>
                <th>File Type</th>
            </tr>';

        while ($fileTypeRow = mysqli_fetch_assoc($fileTypeResult)) {
            $fileType = htmlspecialchars($fileTypeRow['file_type']);
            echo '<tr>';
            echo '<td><a href="?file_type=' . $fileType . '" style="font-size: 32px;">' . $fileType . '</a></td>';
            echo '</tr>';
        }

        echo '</table>';

        mysqli_free_result($fileTypeResult);
    }
    ?>


<script>
        function resetSearchForm() {
            // Reset all input fields to their initial values
            document.getElementsByName('file_type')[0].value = '';
            document.getElementsByName('years')[0].value = '';

            // You can also submit the form to show all existing folders after resetting
            document.getElementsByTagName('form')[0].submit();
        }
    </script>
</body>
</html>
