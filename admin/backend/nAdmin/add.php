<?php
require_once '/var/www/html/connection/db_connection.php';

// Admin credentials
$email = 'christiantitoy@gmail.com';
$fullName = 'Christian Titoy'; // Add full name
$plainPassword = 'admin2x20';

// Hash the password using PHP's password_hash()
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

try {
    // SQL query to insert admin with full_name
    $sql = "INSERT INTO admin (email, full_name, password) VALUES (:email, :full_name, :password)";
    
    // Prepare and execute with array
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':full_name' => $fullName,
        ':password' => $hashedPassword
    ]);
    
    echo "Admin user created successfully!<br>";
    echo "Email: " . $email . "<br>";
    echo "Full Name: " . $fullName . "<br>";
    echo "Hashed Password: " . $hashedPassword;
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>