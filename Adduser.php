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

// Function to generate the next available No Induk Pekerja
function getNextNoInduk() {
    // Database connection configuration
    $servername = "localhost"; // Your database server name
    $username = "root"; // Your database username
    $password = ""; // Your database password
    $dbname = "db_dms"; // Your database name

    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check for a successful connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to get the maximum value of No Induk Pekerja from the database
    $query = "SELECT MAX(no_induk) AS max_no_induk FROM users"; // Replace 'your_table_name' with your actual table name
    $result = $conn->query($query);

    // Check if the query was successful
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $maxNoInduk = $row['max_no_induk'];

        // Increment the maximum value to get the next available No Induk Pekerja
        $nextNoInduk = $maxNoInduk + 1;
    } else {
        // If no existing data, start from 1
        $nextNoInduk = 1;
    }

    // Close the database connection
    $conn->close();

    // Format the number as three digits with leading zeros
    return str_pad($nextNoInduk, 3, '0', STR_PAD_LEFT);
}

// Get the next No Induk Pekerja
$nextNoInduk = getNextNoInduk();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Adjustments for Content Area */
        .content {
            margin-left: 350px; /* Same as the width of the side menu */
            margin-top: 60px; /* Adjust this value to create space below the header */
            padding: 20px; /* Add padding to create space inside the content area */
        }

        /* Style for the Form */
        .user-form {
            max-width: 400px;
            margin: 0 auto;
        }

        /* Style for Form Input Containers */
        .form-input {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        /* Style for Labels */
        .form-label {
            flex: 1;
            margin-right: 10px;
            text-align: right;
        }

        /* Style for Form Inputs */
        .user-form input,
        .user-form select {
            flex: 2;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .user-form{
            justify-content: center; /* Center vertically */
            align-items: center; /* Center horizontally */
        }
        

        /* Style for Form Submit Button */
        .user-form button {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .user-form button:hover {
            background-color: #0056b3;
        }

        .center-button {
        margin-left: auto;
        margin-right: auto;
        display: block;
    }


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

<!-- The rest of your page content goes here -->



<div class="header"></div>

<!-- Content for the Add User Form -->
<div class="content">
    <h1>Create User</h1>
    <form class="user-form" action="process_create_user.php" method="POST">
        <div class="form-input">
            <label class="form-label" for="no_induk">No Induk Pekerja:</label>
            <input type="text" id="no_induk" name="no_induk" value="<?php echo $nextNoInduk; ?>" readonly>
        </div>

        <div class="form-input">
            <label class="form-label" for="nama_depan">Nama Depan:</label>
            <input type="text" id="nama_depan" name="nama_depan" required>
        </div>

        <div class="form-input">
            <label class="form-label" for="nama_belakang">Nama Belakang:</label>
            <input type="text" id="nama_belakang" name="nama_belakang" required>
        </div>

        <!-- Automatically generate the username by combining "nama_depan" and "no_induk" -->
        <input type="hidden" id="username" name="username" readonly>

        <div class="form-input">
            <label class="form-label" for="password">Password (Default):</label>
            <input type="text" id="password" name="password" value="password" readonly>
        </div>

        <div class="form-input">
            <label class="form-label" for="divisi">Divisi:</label>
            <select id="divisi" name="divisi">
                <option value="Administrator">Administrator</option>
                <option value="Admin dan Finance">Admin dan Finance</option>
                <option value="Auditor">Auditor</option>
            </select>
        </div>

        <div class="form-input">
            <label class="form-label" for="jabatan">Jabatan:</label>
            <select id="jabatan" name="jabatan">
                <option value="Super Admin">Super Admin</option>
                <option value="Manager">Manager</option>
                <option value="Supervisor">Supervisor</option>
                <option value="Senior Staff">Senior Staff</option>
                <option value="Junior Staff">Junior Staff</option>
            </select>
        </div>

        <button type="submit" class="center-button">Create User</button>
    </form>
</div>


</body>
</html>
