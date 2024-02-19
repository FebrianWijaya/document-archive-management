<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Change this to your connection info.
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
    $DATABASE_NAME = 'db_dms';
    // Try and connect using the info above.
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if (mysqli_connect_errno()) {
        // If there is an error with the connection, stop the script and display the error.
        exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    }

    // Now we check if the data from the login form was submitted, isset() will check if the data exists.
    if (!isset($_POST['username'], $_POST['password'])) {
        // Could not get the data that should have been sent.
        echo 'Please fill both the username and password fields!';
    } else {
        // Prepare our SQL, preparing the SQL statement will prevent SQL injection.
        if ($stmt = $con->prepare('SELECT id, password, divisi FROM users WHERE username = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('s', $_POST['username']);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $password, $divisi);
                $stmt->fetch();
                // Account exists, now we verify the password.
                // Note: remember to use password_hash in your registration file to store the hashed passwords.
                if ($_POST['password'] === $password) {
                    // Verification success! User has logged in!
                    // Create sessions, so we know the user is logged in, they basically act like cookies but remember the data on the server.
                    session_regenerate_id();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $id;
                    $_SESSION['divisi'] = $divisi;
                    $_SESSION['username'] = $_POST['username'];

                    // Redirect all roles to dashboard.php
                    header('Location: dashboard.php');
                } else {
                    // Incorrect password
                    echo '<script>alert("Incorrect password!");</script>';
                }
            } else {
                // Incorrect username
                echo '<script>alert("Incorrect username");</script>';
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0; /* Optional background color */
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center; /* Center horizontally */
            text-align: center;
        }

        .logo {
            margin-bottom: 20px; /* Add some space below the logo */
        }

        .logo img {
            max-width: 400px; /* Adjust the max-width as needed */
        }

        .login {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px; /* Set the width of the login container */
        }

        .login form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login label {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .login label i {
            margin-right: 10px;
        }

        .login input[type="text"],
        .login input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }.client-login-link {
            margin-top: 10px;
            font-size: 14px;
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease-in-out;
        }

        .client-login-link:hover {
            color: #0056b3;
        }
    </style>
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="image/logo.png" alt="Logo">
        </div>
        <div class="login">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <h1>Staff Login</h1>
                <label for="username">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username" id="username" required autocomplete="off">
                </label>
                <label for="password">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" id="password" required autocomplete="off">
                </label>
                <input type="submit" value="Login">
            </form>

            <!-- Styled Client Login Link -->
            <p class="client-login-link">Client Login: <a href="client_login.php">Click here</a></p>
        </div>
    </div>
</body>
</html>