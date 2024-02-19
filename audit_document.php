<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['divisi'], ['Administrator', 'Admin dan Finance'])) {
    header('Location: login.php');
    exit;
}
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
include 'header.php';

// Include the appropriate sidebar based on the user's role
function getSidebarFileName($divisi) {
    switch ($divisi) {
        case 'Administrator':
            return 'sidebar.php';
        case 'Auditor':
            return 'audit_sidebar.php';
        case 'Admin dan Finance':
            return 'finance_sidebar.php';
        default:
            return '';
    }
}

$sidebarFile = getSidebarFileName($_SESSION['divisi']);
if (!empty($sidebarFile)) {
    include $sidebarFile;
}
?>

<div class="header"></div>

<!-- Content for the Dashboard Function -->
<div class="content">
    <!-- Your specific content for the Dokumen Admin goes here -->
    <h1>Welcome to Dokumen Audit Management</h1>
    <p>This is where you display Dokumen Audit-related information.</p>

    <!-- Modify the search form to accept multiple years separated by commas -->
    <div class="search-form">
        <form method="GET">
            <input type="text" name="company_name" class="search-input" placeholder="Company Name" value="<?= isset($_GET['company_name']) ? htmlspecialchars($_GET['company_name']) : ''; ?>">
            <input type="text" name="years" class="search-input" placeholder="(e.g., 2018,2019)" value="<?= isset($_GET['years']) ? htmlspecialchars($_GET['years']) : ''; ?>">
            <button type="submit" class="search-button">Search</button>
            <button type="button" class="reset-button" onclick="resetSearchForm()">Reset</button>
        </form>
    </div>


    <!-- Create Folder Button (Redirects to create_folder_form.php) -->
    <a href="dokumen_auditor_form.php" class="add-button">Create Folder</a>

    <?php
    // Modify your SQL query to include the selected year and search criteria
    $selectedCompany = isset($_GET['company_name']) ? mysqli_real_escape_string($conn, $_GET['company_name']) : '';
    $fileType = isset($_GET['file_type']) ? mysqli_real_escape_string($conn, $_GET['file_type']) : '';
    $years = isset($_GET['years']) ? mysqli_real_escape_string($conn, $_GET['years']) : '';

    // Check if a specific year is selected
    if (!empty($years)) {
        echo "<h2>Folders for Year $years</h2>";

        // Query to retrieve folders for the selected year with optional search criteria
        $foldersQuery = $conn->prepare("SELECT company_name, folder_name, file_type, years FROM folders 
                WHERE division = 'audit' 
                AND document_type = 'dokumen' 
                AND years LIKE CONCAT('%', ?, '%') 
                ORDER BY company_name, years ASC");
        $foldersQuery->bind_param("s", $years);
        $foldersQuery->execute();
        $foldersResult = $foldersQuery->get_result();

        if (!$foldersResult) {
            die("Folders query failed: " . mysqli_error($conn));
        }

        // Display a table of folders for the selected year with optional search criteria
        echo '<table>';
        echo '<tr>
                <th>Company Name</th>
                <th>Folder Name</th>
                <th>File Type</th>
                <th>Years</th>
                <th>Actions</th>
            </tr>';

        while ($folderRow = mysqli_fetch_assoc($foldersResult)) {
            $companyName = htmlspecialchars($folderRow['company_name']);
            $folderName = htmlspecialchars($folderRow['folder_name']);
            $fileType = htmlspecialchars($folderRow['file_type']);
            $folderYears = htmlspecialchars($folderRow['years']);

            echo '<tr>';
            echo "<td>$companyName</td>";
            echo "<td>$folderName</td>";
            echo "<td>$fileType</td>";
            echo "<td>$folderYears</td>";
            echo '<td>';
            echo "<a href='admin_document_history.php?folder=$folderName' class='history-button'>History</a>";

            // Add the delete button only for administrators
            if ($_SESSION['divisi'] === 'Administrator') {
                echo " | <a href='delete_folder.php?folder=$folderName' class='delete-button'>Delete</a>";
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';

        // Close the folders query result
        mysqli_free_result($foldersResult);
    } else {
        // Check if a specific company name is selected
        if (!empty($selectedCompany)) {
            echo "<h2>Folders for $selectedCompany</h2>";

            // Query to retrieve folders for the selected company with optional search criteria
            $foldersQuery = $conn->prepare("SELECT folder_name, file_type, years FROM folders 
                    WHERE division = 'audit' 
                    AND document_type = 'dokumen' 
                    AND company_name LIKE CONCAT('%', ?, '%') 
                    AND years LIKE CONCAT('%', ?, '%') 
                    ORDER BY years ASC"); // or ASC for ascending order
            $foldersQuery->bind_param("ss", $selectedCompany, $years);
            $foldersQuery->execute();
            $foldersResult = $foldersQuery->get_result();

            if (!$foldersResult) {
                die("Folders query failed: " . mysqli_error($conn));
            }

            // Display a table of folders for the selected company with optional search criteria
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
                echo "<a href='audit_document_history.php?folder=$folderName' class='history-button'>History</a>";

                // Add the delete button only for administrators
                if ($_SESSION['divisi'] === 'Administrator') {
                    echo " | <a href='delete_folder.php?folder=$folderName' class='delete-button'>Delete</a>";
                }

                echo '</td>';
                echo '</tr>';
            }

            echo '</table>';

            // Close the folders query result
            mysqli_free_result($foldersResult);
        } else {
            // Display List of Unique Company Names only when no specific company is selected
            echo '<h2>List of Companies</h2>';
            // Query to retrieve distinct company names
            $companyQuery = "SELECT DISTINCT company_name FROM folders WHERE division = 'audit' AND document_type = 'dokumen'";
            $companyResult = mysqli_query($conn, $companyQuery);

            if (!$companyResult) {
                die("Company query failed: " . mysqli_error($conn));
            }

            // Display companies as a table
            echo '<table>';
            echo '<tr>
                    <th>Company Name</th>
                </tr>';

            while ($companyRow = mysqli_fetch_assoc($companyResult)) {
                $company = htmlspecialchars($companyRow['company_name']);
                echo '<tr>';
                echo '<td><img src="image/folder_icon.png" alt="Folder Icon" class="small-folder-icon"> <a href="?company_name=' . $company . '" style="font-size: 32px;">' . $company . '</a></td>';
                echo '</tr>';
            }

            echo '</table>';

            mysqli_free_result($companyResult);
        }
    }
    ?>


<script>
        function resetSearchForm() {
            // Reset all input fields to their initial values
            document.getElementsByName('company_name')[0].value = '';
            document.getElementsByName('years')[0].value = '';

            // You can also submit the form to show all existing folders after resetting
            document.getElementsByTagName('form')[0].submit();
        }
    </script>
</body>
</html>
