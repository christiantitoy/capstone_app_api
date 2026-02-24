<?php
header('Content-Type: application/json');

// Optional: just respond OK
echo json_encode([
    "status" => true,
    "message" => "Server is reachable"
]);
