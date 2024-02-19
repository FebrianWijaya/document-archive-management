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

// Initialize a variable to store the user deletion status
$deletionStatus = "";

// Check if the user ID is provided in the URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user details including division
    $fetchUserQuery = "SELECT id, divisi FROM users WHERE id = $userId";
    $userResult = $conn->query($fetchUserQuery);

    if ($userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $userDivision = $userRow['divisi'];

        // Check if the user confirms the deletion
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
            // Check if the user is an administrator
            if ($userDivision === 'Administrator') {
                // Check if there is more than one administrator
                $adminCheckQuery = "SELECT COUNT(*) AS adminCount FROM users WHERE divisi = 'Administrator'";
                $adminResult = $conn->query($adminCheckQuery);

                if ($adminResult->num_rows > 0) {
                    $adminRow = $adminResult->fetch_assoc();
                    $adminCount = $adminRow['adminCount'];

                    if ($adminCount > 1) {
                        // SQL query to delete the user with the given ID
                        $deleteUserQuery = "DELETE FROM users WHERE id = $userId";

                        if ($conn->query($deleteUserQuery) === TRUE) {
                            // User deleted successfully, redirect to the list of users
                            header("Location: staff.php");
                            exit();
                        } else {
                            echo "Error deleting user: " . $conn->error;
                        }
                    } else {
                        echo "Cannot delete the only administrator account.";
                    }
                }
            } else {
                // For non-administrator accounts, allow deletion
                $deleteUserQuery = "DELETE FROM users WHERE id = $userId";

                if ($conn->query($deleteUserQuery) === TRUE) {
                    // User deleted successfully, redirect to the list of users
                    header("Location: staff.php");
                    exit();
                } else {
                    echo "Error deleting user: " . $conn->error;
                }
            }
        } else {
            // Display a confirmation dialog
            echo "<script>
                    var confirmDelete = confirm('Are you sure you want to delete this user?');
                    if (confirmDelete) {
                        window.location.href = 'delete_user.php?id=$userId&confirm=true';
                    } else {
                        window.location.href = 'staff.php';
                    }
                  </script>";
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "User ID not provided.";
}

// Close the database connection
$conn->close();
?>
