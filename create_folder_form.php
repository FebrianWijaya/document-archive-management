<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Folder</title>
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
        
        /* Center the form horizontally and vertically */
        .user-form {
            justify-content: center;
            align-items: center;
            flex-direction: column; /* Stack form elements vertically */
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

        /* Style for the Create Button */
        .create-button {
            margin-top: 15px; /* Add space between the form and the button */
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

<!-- Content for the Create Folder Form -->
<div class="content">
    <h1>Create Folder</h1>
    <form class="user-form" action="create_folder.php" method="post">

        <div class="form-input">
            <label class="form-label" for="division">Division:</label>
            <input type="text" name="division" id="division" value="Audit" readonly>
        </div>

        <div class="form-input">
            <label class="form-label" for="documentType">Document Type:</label>
            <input type="text" name="documentType" id="documentType" value="Dokumen" readonly>
        </div>

        <div class="form-input">
            <label class="form-label" for="fileType">Folder Name:</label>
            <input type="text" name="fileType" id="fileType" required>
        </div>

        <div class="form-input">
            <label class="form-label" for="companyName">Company Name:</label>
            <input type="text" name="companyName" id="companyName" required>
        </div>

        <div class="form-input">
            <label class="form-label" for="years">Years:</label>
            <select name="years" id="years" required>
                <?php
                $currentYear = date('Y');
                $startYear = 2019;

                for ($year = $currentYear + 1; $year >= $startYear; $year--) {
                    echo '<option value="' . $year . '">' . $year . '</option>';
                }
                ?>
            </select>
        </div>

        <button type="submit" class="center-button">Create Folder</button>
    </form>
</div>

</body>
</html>
