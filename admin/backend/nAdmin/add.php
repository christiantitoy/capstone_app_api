<?php
require_once '/var/www/html/connection/db_connection.php';

// Admin credentials
$email = 'christiantitoy@gmail.com';
$plainPassword = 'admin2x20';

// Hash the password using PHP's password_hash()
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

// SQL query to insert admin
$sql = "INSERT INTO admin (email, password) VALUES (?, ?)";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $hashedPassword);

if ($stmt->execute()) {
    echo "Admin user created successfully!<br>";
    echo "Email: " . $email . "<br>";
    echo "Hashed Password: " . $hashedPassword;
} else {
    echo "Error creating admin: " . $stmt->error;
}

// Close the statement
$stmt->close();
?>