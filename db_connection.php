<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname = 'capstone_app';

$conn = new mysqli($hostname, $username, $password, $dbname);

if ($conn->connect_error) {
  die(json_encode([
    'status' => 'error',
    'message' => 'Ohmaymay ERROOORRRRR!'
  ]));
}

?>
