<?php
header("Content-Type: application/json; charset=UTF-8");

require_once '/var/www/html/connection/db_connection.php';

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

/* Check username */
$stmt = $conn->prepare(
    "SELECT id FROM buyers WHERE username = :username"
);
$stmt->execute(['username' => $username]);

if ($stmt->rowCount() > 0) {
    echo json_encode([
        "status"=>"error",
        "message"=>"Username already used"
    ]);
    exit;
}

/* Check email */
$stmt = $conn->prepare(
    "SELECT id FROM buyers WHERE email = :email"
);
$stmt->execute(['email'=>$email]);

if ($stmt->rowCount() > 0) {
    echo json_encode([
        "status"=>"error",
        "message"=>"Email already used"
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* Insert */
$stmt = $conn->prepare(
"INSERT INTO buyers(username,email,password)
 VALUES(:username,:email,:password)
 RETURNING id"
);

$stmt->execute([
    'username'=>$username,
    'email'=>$email,
    'password'=>$hashedPassword
]);

$user_id = $stmt->fetchColumn();

/* Fetch user */
$stmt = $conn->prepare(
"SELECT id, username, email, avatar_url
 FROM buyers WHERE id = :id"
);

$stmt->execute(['id'=>$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "status"=>"success",
    "message"=>"Account created successfully",
    "id"=>$user["id"],
    "username"=>$user["username"],
    "email"=>$user["email"],
    "avatar_url"=>$user["avatar_url"]
]);
?>