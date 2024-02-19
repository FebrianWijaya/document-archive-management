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

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Database connection configuration (same as your previous code)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_dms";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch the user's data based on the ID
    $sql = "SELECT id, no_induk, nama_depan, nama_belakang, username, perusahaan FROM client_users WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit; // Exit the script if the user is not found
    }

    $conn->close();
} else {
    echo "User ID not provided.";
    exit; // Exit the script if the user ID is not provided
}
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

        /* Style for Form Submit Button (matching the style from the first code) */
        .user-form button {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }

        .user-form button:hover {
            background-color: #0056b3;
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

    <div class="header"></div>

    <div class="content">
        <h1>Edit User</h1>
        <form class="user-form" action="process_edit_user_client.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">

            <div class="form-input">
                <label class="form-label" for="no_induk">No Induk Pekerja:</label>
                <input type="text" id="no_induk" name="no_induk" value="<?php echo $row['no_induk']; ?>" required>
            </div>

            <div class="form-input">
                <label class="form-label" for="nama_depan">Nama Depan:</label>
                <input type="text" id="nama_depan" name="nama_depan" value="<?php echo $row['nama_depan']; ?>" required>
            </div>

            <div class="form-input">
                <label class="form-label" for="nama_belakang">Nama Belakang:</label>
                <input type="text" id="nama_belakang" name="nama_belakang" value="<?php echo $row['nama_belakang']; ?>" required>
            </div>

            <!-- Editable username field -->
            <div class="form-input">
                <label class="form-label" for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo $row['username']; ?>" required>
            </div>

            <div class="form-input">
                <label class="form-label" for="perusahaan">Perusahaan:</label>
                <select id="perusahaan" name="perusahaan" required>
                    <?php
                    // Database connection configuration (same as your previous code)
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "db_dms";

                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Query to retrieve distinct company names from the "folders" table
                    $query = "SELECT DISTINCT company_name FROM folders"; // Adjust the column name accordingly
                    $result = $conn->query($query);

                    // Check if the query was successful
                    if ($result && $result->num_rows > 0) {
                        while ($companyRow = $result->fetch_assoc()) {
                            $companyName = htmlspecialchars($companyRow['company_name']); // Sanitize output
                            $selected = ($companyName == $row['perusahaan']) ? 'selected' : '';
                            echo "<option value=\"$companyName\" $selected>$companyName</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No companies found</option>";
                    }

                    // Close the database connection
                    $conn->close();
                    ?>
                </select>
            </div>

            <button type="submit" class="center-button">Update User</button>
        </form>
    </div>

</body>

</html>
