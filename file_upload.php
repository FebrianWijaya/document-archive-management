<?php
session_start();


?>

<!-- File Upload Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Upload Files to Selected Folder</title>
</head>
<body>
    <h2>Upload Files to Selected Folder</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <label for="file">Select File to Upload:</label>
        <input type="file" name="file" id="file" required><br>
        <label for="comment">Comment:</label>
        <textarea name="comment" id="comment" required></textarea><br>
        <input type="hidden" name="folder" value="<?php echo htmlspecialchars($_GET['folder']); ?>">
        <input type="submit" name="upload" value="Upload File">
    </form>
</body>
</html>
