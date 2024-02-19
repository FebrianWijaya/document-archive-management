<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection configuration (same as your previous code)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_dms";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the updated user data from the form
    $user_id = $_POST['user_id'];
    $new_no_induk = $_POST['no_induk'];
    $new_username = $_POST['username'];

    // Check if the new no_induk is already in use
    $check_no_induk_sql = "SELECT id FROM client_users WHERE no_induk = '$new_no_induk' AND id != $user_id";
    $result_no_induk = $conn->query($check_no_induk_sql);

    // Check if the new username is already in use
    $check_username_sql = "SELECT id FROM client_users WHERE username = '$new_username' AND id != $user_id";
    $result_username = $conn->query($check_username_sql);

    if ($result_no_induk->num_rows > 0) {
        echo "No Induk already exists for another user.";
    } elseif ($result_username->num_rows > 0) {
        echo "Username already exists for another user.";
    } else {
        // Get the other updated user data
        $new_nama_depan = $_POST['nama_depan'];
        $new_nama_belakang = $_POST['nama_belakang'];
        $new_perusahaan = $_POST['perusahaan'];

        // Update the user's information in the database
        $sql = "UPDATE client_users SET no_induk = '$new_no_induk', 
                                  nama_depan = '$new_nama_depan', 
                                  nama_belakang = '$new_nama_belakang', 
                                  username = '$new_username', 
                                  perusahaan = '$new_perusahaan' 
                WHERE id = $user_id";

        if ($conn->query($sql) === TRUE) {
            header('Location: client.php'); // Redirect to the staff page after successful update
            exit;
        } else {
            echo "Error updating user: " . $conn->error;
        }
    }

    $conn->close();
}
?>
