<?php
// Database connection configuration
$servername = "localhost"; // Your database server name
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "db_dms"; // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from the form
$no_induk = $_POST['no_induk'];
$nama_depan = $_POST['nama_depan'];
$nama_belakang = $_POST['nama_belakang'];
$divisi = $_POST['divisi'];
$jabatan = $_POST['jabatan'];

// Check if a user with the same "no_induk" already exists
$check_sql = "SELECT * FROM users WHERE no_induk = '$no_induk'";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows > 0) {
    // User with the same "no_induk" already exists, show an error pop-up message
    echo '<script>alert("User with the same No Induk Pekerja already exists!");</script>';
    echo '<script>window.location.href = "Adduser.php";</script>'; // Redirect back to the Adduser page
    exit(); // Ensure that no further PHP code is executed after the redirect
}

// Generate username by combining "nama_depan" and "no_induk"
$username = $nama_depan . $no_induk;

// Default password (you can change this as needed)
$password = "password";

// SQL query to insert the user data into the database
$sql = "INSERT INTO users (no_induk, nama_depan, nama_belakang, username, password, divisi, jabatan)
        VALUES ('$no_induk', '$nama_depan', '$nama_belakang', '$username', '$password', '$divisi', '$jabatan')";

if ($conn->query($sql) === TRUE) {
    // JavaScript to show a success pop-up message
    echo '<script>alert("User created successfully!");</script>';
    echo '<script>window.location.href = "staff.php";</script>'; // Redirect back to the staff page
    exit(); // Ensure that no further PHP code is executed after the redirect
} else {
    // Display an error message in case of a database insertion error
    echo '<script>alert("Error: ' . $conn->error . '");</script>';
    echo '<script>window.location.href = "Adduser.php";</script>'; // Redirect back to the Adduser page
}

// Close the database connection
$conn->close();
?>
