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

    // Check if the user confirms the deletion
    if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
        // SQL query to delete the user with the given ID
        $sql = "DELETE FROM client_users WHERE id = $userId";

        if ($conn->query($sql) === TRUE) {
            // User deleted successfully, redirect to the list of users
            header("Location: client.php");
            exit();
        } else {
            echo "Error deleting user: " . $conn->error;
        }
    } else {
        // Display a confirmation dialog
        echo "<script>
                var confirmDelete = confirm('Are you sure you want to delete this user?');
                if (confirmDelete) {
                    window.location.href = 'delete_client.php?id=$userId&confirm=true';
                } else {
                    window.location.href = 'client.php';
                }
              </script>";
    }
} else {
    echo "User ID not provided.";
}

// Close the database connection
$conn->close();
?>
