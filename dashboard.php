<?php
session_start();

// If the user is not logged in or doesn't have the appropriate role, redirect to the login page or display an error message...
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for the combined content */
        .content {
            padding: 20px;
        }
        .count-box {
            border: 2px solid #ccc;
            padding: 10px;
            margin: 10px;
            display: inline-block;
            text-align: center;
            vertical-align: top;
            width: 300px;
            height: 475px;
            background-color: #f4f4f4;
        }
        .count-box h2 {
            font-size: 24px;
            margin: 0;
            margin-top:15px;
        }
        .count {
            font-size: 36px;
        }
        .icon {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .pie-chart-container {
            display: inline-block;
            vertical-align: top;
            width: 300px;
            height: 300px;
            margin-top:25px;
        }
        .pie-chart-container-2 {
            display: inline-block;
            vertical-align: top;
            width: 300px;
            height: 300px;
            margin-top:25px;
        }
        .pie-chart-container-3 {
            display: inline-block;
            vertical-align: top;
            width: 300px;
            height: 300px;
            margin-top:13px;
        }
    </style>
</head>
<body>

<?php
// Include the header
if ($_SESSION['divisi'] === 'Administrator' || $_SESSION['divisi'] === 'Auditor' || $_SESSION['divisi'] === 'Admin dan Finance') {
    include 'header.php'; // Include header.php for specified divisions
} else {
    include 'client_header.php'; // Include client_header.php for other divisions
}

// Include the appropriate sidebar based on the user's role
if ($_SESSION['divisi'] === 'Administrator') {
    include 'sidebar.php';
} elseif ($_SESSION['divisi'] === 'Auditor') {
    include 'audit_sidebar.php';
} elseif ($_SESSION['divisi'] === 'Admin dan Finance') {
    include 'finance_sidebar.php';
} else {
    include 'client_sidebar.php'; // Default sidebar for other divisions
}
?>

<!-- Content for the Dashboard Function -->
<div class="content">
    <h1>Welcome to the Dashboard</h1>
    <p>This is where you display dashboard-related information.</p>

    <!-- Your specific content for the Dashboard goes here -->
    <!-- Merge the 2nd code content here -->

    <?php
    // Database connection configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_dms";

    // Create a database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to get the count of users
    $userCount = 0;
    $sql = "SELECT COUNT(*) as user_count FROM users";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userCount = $row['user_count'];
    }

    // Query to count Auditor division files
    $auditorCount = 0;
    $sql = "SELECT COUNT(*) as auditor_count FROM uploaded_files WHERE division = 'Auditor'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $auditorCount = $row['auditor_count'];
    }

    // Query to count Admin and Finance Division files
    $adminFinanceCount = 0;
    $sql = "SELECT COUNT(*) as admin_finance_count FROM uploaded_files WHERE division = 'Admin dan Finance'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $adminFinanceCount = $row['admin_finance_count'];
    }

    // Define the three divisions for the pie chart
    $divisi = ['Administrator', 'Admin dan Finance', 'Auditor'];

    // Get the count for each division for the pie chart
    $counts = [];
    foreach ($divisi as $division) {
        $sql = "SELECT COUNT(*) as count FROM Users WHERE divisi = '$division'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $counts[] = $row['count'];
    }

    // Get the count of "surat" and "document" for the "Auditor" division
    $division = 'Auditor';
    $fileTypes = ['surat', 'document'];
    $countsAuditor = [];

    foreach ($fileTypes as $fileType) {
        $sql = "SELECT COUNT(*) as count FROM uploaded_files WHERE division = '$division' AND file_type = '$fileType'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $countsAuditor[] = $row['count'];
    }

    // Get the count of "surat" and "Transaksi Perusahaan" for the "Admin dan Finance" division
    $division = 'Admin dan Finance';
    $fileTypes = ['surat', 'Transaksi Perusahaan'];
    $countsAdminFinance = [];

    foreach ($fileTypes as $fileType) {
        $sql = "SELECT COUNT(*) as count FROM uploaded_files WHERE division = '$division' AND file_type = '$fileType'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $countsAdminFinance[] = $row['count'];
    }

    $conn->close();
    ?>

        <div class="count-box">
            <i class="fas fa-users icon"></i>
            <h2>Total Users</h2>
            <span class="count"><?= $userCount ?></span>
            <div class="pie-chart-container">
                <canvas id="userPieChart"></canvas>
            </div>
        </div>
        <?php if ($_SESSION['divisi'] !== 'Auditor'): ?>
        <!-- This code will only be displayed if the user's role is not 'Administrator' -->
        <div class="count-box">
            <i class="fas fa-chart-line icon"></i>
            <h2>Admin & Finance Division Document</h2>
            <span class="count"><?= $adminFinanceCount ?></span>
            <div class="pie-chart-container-3">
                <canvas id="adminFinancePieChart"></canvas>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($_SESSION['divisi'] !== 'Admin dan Finance'): ?>
        <!-- This code will only be displayed if the user's role is not 'Administrator' -->
        <div class="count-box">
            <i class="fas fa-clipboard-check icon"></i>
            <h2>Auditor Division Document</h2>
            <span class="count"><?= $auditorCount ?></span>
            <div class="pie-chart-container-2">
                <canvas id="auditorPieChart"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        const pieChartData = {
            labels: <?= json_encode($divisi) ?>,
            data: <?= json_encode($counts) ?>,
        };

        const ctx = document.getElementById('userPieChart').getContext('2d');

        const userPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: pieChartData.labels,
                datasets: [{
                    data: pieChartData.data,
                    backgroundColor: ['rgb(255, 99, 132)', 'rgb(75, 192, 192)', 'rgb(255, 205, 86)'],
                }],
            },
            options: {
                responsive: false,
            },
        });
    </script>

    <script>
        const auditorPieChartData = {
            labels: <?= json_encode(['Surat Auditor', 'Document Auditor']) ?>, // Update 'Transaksi Perusahaan' to 'Document'
            data: <?= json_encode($countsAuditor) ?>,
        };

        const auditorCtx = document.getElementById('auditorPieChart').getContext('2d');

        const auditorPieChart = new Chart(auditorCtx, {
            type: 'pie',
            data: {
                labels: auditorPieChartData.labels,
                datasets: [{
                    data: auditorPieChartData.data,
                    backgroundColor: ['rgb(255, 99, 132)', 'rgb(75, 192, 192)'], // Customize with your desired colors
                }],
            },
            options: {
                responsive: false,
            },
        });
    </script>

    <script>
        const adminFinancePieChartData = {
            labels: <?= json_encode(['Surat Admin dan Finance', 'Dokumen Transaksi Perusahaan']) ?>, // Change 'Document' to 'Transaksi Perusahaan' here
            data: <?= json_encode($countsAdminFinance) ?>,
        };

        const adminFinanceCtx = document.getElementById('adminFinancePieChart').getContext('2d');

        const adminFinancePieChart = new Chart(adminFinanceCtx, {
            type: 'pie',
            data: {
                labels: adminFinancePieChartData.labels,
                datasets: [{
                    data: adminFinancePieChartData.data,
                    backgroundColor: ['rgb(255, 205, 86)', 'rgb(54, 162, 235)'], // Customize with your desired colors
                }],
            },
            options: {
                responsive: false,
            },
        });
    </script>
</body>
</html>
