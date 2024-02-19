<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Check the user's role to determine access
$allowed_roles = ['Administrator'];
if (!in_array($_SESSION['divisi'], $allowed_roles)) {
    // User does not have the required role
    echo 'Access Denied. You do not have permission to access this page.';
    exit;
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
        .search-button:hover {
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
        .edit-button {
        background-color: #28a745;
        color: #fff;
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 5px;
        transition: opacity 0.3s;
        }

        .edit-button:hover {
            opacity: 0.7;
        }

        .delete-button {
            background-color: #dc3545;
            color: #fff;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: opacity 0.3s;
        }

        .delete-button:hover {
            opacity: 0.7;
        }
</style>

    </style>
</head>
<body>

<?php
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

<div class="content">
    <!-- Your specific content for the Staff function goes here -->
    <h1>Welcome to client user Management</h1>
    <p>This is where you display Staff-related information.</p>

    <!-- Search User Form -->
    <form class="search-form" action="" method="GET">
        <input type="text" class="search-input" name="search" placeholder="Search users by name or username">
        <button type="submit" class="search-button">Search</button>
    </form>

    <!-- Check if the user has the 'Administrator' role to display the "Add User" button -->
    <?php if ($_SESSION['divisi'] === 'Administrator'): ?>
    <a href="addclient.php" class="add-button">Add User</a>
    <?php endif; ?>


    

   <!-- Display List of Users in a Table -->
    <h2>List of Users</h2>
    <table>
        <tr>
            <th>No.</th>
            <th>ID</th>
            <th>No Induk</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Perusahaan</th>
            <?php if ($_SESSION['divisi'] === 'Administrator'): ?>
                <th>Action</th>
            <?php endif; ?>
        </tr>
        <?php
        // Database connection configuration
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "db_dms"; // Replace with your actual database name

        // Create a connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Initialize variables for search query and filter results
        $search = "";
        $whereClause = "";

        // Check if a search query is provided
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            // Construct a WHERE clause to filter the results based on the search query
            $whereClause = "WHERE nama_depan LIKE '%$search%' OR nama_belakang LIKE '%$search%' OR username LIKE '%$search%'";
        }

        // SQL query to retrieve user data excluding the password with the search filter
        $sql = "SELECT id, no_induk, nama_depan, nama_belakang, username, perusahaan FROM client_users $whereClause";
        $result = $conn->query($sql);

        // Check if there are users
        if ($result->num_rows > 0) {
            $row_number = 1; // Initialize the row number
        
            // Output data of each row in a table row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row_number . "</td>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["no_induk"] . "</td>";
                echo "<td>" . $row["nama_depan"] . " " . $row["nama_belakang"] . "</td>";
                echo "<td>" . $row["username"] . "</td>";
                echo "<td>" . $row["perusahaan"] . "</td>";
        
                if ($_SESSION['divisi'] === 'Administrator') {
                    // Conditionally display Edit and Delete buttons for Administrators
                    echo "<td>";
                    echo "<a href='edit_user_client.php?id=" . $row["id"] . "' class='edit-button'>Edit</a> | ";
                    echo "<a href='delete_client.php?id=" . $row["id"] . "' class='delete-button'>Delete</a>";
                    echo "</td>";
                }
                
        
                // Increment the row number
                $row_number++;
            }
        } else {
            echo "<tr><td colspan='8'>No users found.</td></tr>";
        }
    
        // Close the database connection
        $conn->close();
        ?>
    </table>
</div>

</body>
</html>