<?php
if (isset($_GET['folder']) && isset($_GET['file'])) {
    $folder = urldecode($_GET['folder']);
    $file = urldecode($_GET['file']);
    
    // Validate the folder and file inputs for security

    $filePath = "uploads/$folder/$file";
    
    if (file_exists($filePath)) {
        // Set appropriate headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        ob_clean();
        flush();
        
        // Read the file and output it to the browser
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
} else {
    echo "Invalid request.";
}
?>
