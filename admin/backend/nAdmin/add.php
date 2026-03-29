<?php
require_once '/var/www/html/connection/db_connection.php';

// Admin credentials
$email = 'christiantitoy@gmail.com';
$plainPassword = 'admin2x20';

// Hash the password using PHP's password_hash()
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

try {
    // SQL query to insert admin
    $sql = "INSERT INTO admin (email, password) VALUES (:email, :password)";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Email: " . $email . "<br>";
        echo "Hashed Password: " . $hashedPassword;
    } else {
        echo "Error creating admin";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>