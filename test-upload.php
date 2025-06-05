<?php
$uploadDir = __DIR__ . '/uploads/profiles/';
echo "Upload directory: " . $uploadDir . "<br>";
echo "Directory exists: " . (is_dir($uploadDir) ? 'Yes' : 'No') . "<br>";
echo "Directory writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "<br>";
echo "Current user: " . exec('whoami') . "<br>";
echo "Directory owner: " . posix_getpwuid(fileowner($uploadDir))['name'] . "<br>";
echo "Directory permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "<br>";
?>