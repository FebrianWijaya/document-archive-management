<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Check the user's role to determine access
$allowed_roles = ['Administrator', 'Auditor','Admin dan Finance'];
if (!in_array($_SESSION['divisi'], $allowed_roles)) {
    // User does not have the required role
    echo 'Access Denied. You do not have permission to access this page.';
    exit;
}

// Include your database connection code here
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_dms";

// Create a database connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$errors = [];
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and process the password change
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_new_password = $_POST["confirm_new_password"];

    // Check if the current password matches the user's actual password
    $current_user_id = $_SESSION['id']; // Assuming you have a user ID in the session

    // Retrieve the user's current password from the database
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $stmt->bind_result($current_user_password);
    $stmt->fetch();
    $stmt->close();

    if ($current_password === $current_user_password) {
        if ($new_password === $confirm_new_password) {
            // Update the user's password in the database
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $mysqli->prepare($update_query);
            $update_stmt->bind_param("si", $new_password, $current_user_id);
            if ($update_stmt->execute()) {
                // Set a success message
                $success_message = "Password changed successfully!";
            } else {
                $errors[] = "Failed to update password. Please try again.";
            }
            $update_stmt->close();
        } else {
            $errors[] = "New password and confirm new password do not match.";
        }
    } else {
        $errors[] = "Current password is incorrect.";
    }
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

<!-- Content for the Password Change Form -->
<div class="content">
    <h1>Password Change</h1>
    <?php
    if (!empty($errors)) {
        echo '<div class="error-message">';
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
        echo '</div>';
    }
    if ($success_message !== "") {
        echo '<div class="success-message">' . $success_message . '</div>';
    }
    ?>
    <form class="user-form" method="post" action="">
        <div class="form-input">
            <label class="form-label" for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="form-input">
            <label class="form-label" for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>

        <div class="form-input">
            <label class="form-label" for="confirm_new_password">Confirm New Password:</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password" required>
        </div>

        <button type="submit" class="center-button">Change password</button>
    </form>
</div>

</body>
</html>
