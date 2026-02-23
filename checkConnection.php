<?php
include 'db_connection.php';

$result = pg_query($conn, "SELECT NOW()");
$row = pg_fetch_assoc($result);

echo json_encode([
    "status" => true,
    "time" => $row['now']
]);
?>