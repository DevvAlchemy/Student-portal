<?php

$host = "localhost";
$dbname = "student_manager";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO students 
        (student_id, first_name, last_name, email, course, enrollment_date) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $_POST['student_id'],
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['course'],
        $_POST['enrollment_date']
    ]);

    header("Location: index.php"); // Redirect back
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
