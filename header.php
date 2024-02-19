<?php
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'db_dms';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php'); // Redirect to the login page if not logged in
    exit;
}


// Fetch user's first name (nama_depan), last name (nama_belakang), and "divisi" from the session
$user_id = $_SESSION['id'];
$divisi = $_SESSION['divisi'];


if ($stmt = $con->prepare('SELECT nama_depan, nama_belakang FROM users WHERE id = ?')) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($nama_depan, $nama_belakang);
        $stmt->fetch();
    }
    $stmt->close();
}
?>

<div class="user-info">
    <span class="user-info-label">Logged in as:</span> <strong><?php echo $nama_depan . ' ' . $nama_belakang; ?></strong>
    <br>
    <span class="user-info-label user-info-divisi">Division:</span> <strong><?php echo $divisi; ?></strong>
</div>

<style>
    .user-info-divisi {
        margin-left: 35px; /* Adjust the value to control the spacing */
    }
</style>
