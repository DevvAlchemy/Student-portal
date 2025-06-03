<?php
echo "Current directory: " . __DIR__ . "<br>";
echo "CSS file exists: " . (file_exists(__DIR__ . '/assets/css/style.css') ? 'Yes' : 'No') . "<br>";
echo "Default image exists: " . (file_exists(__DIR__ . '/assets/images/default.jpg') ? 'Yes' : 'No') . "<br>";
echo "Uploads folder exists: " . (file_exists(__DIR__ . '/uploads/profiles/') ? 'Yes' : 'No') . "<br>";
echo "Uploads folder writable: " . (is_writable(__DIR__ . '/uploads/profiles/') ? 'Yes' : 'No') . "<br>";

// Check PHP upload settings
echo "<br>PHP Settings:<br>";
echo "file_uploads: " . ini_get('file_uploads') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
?>