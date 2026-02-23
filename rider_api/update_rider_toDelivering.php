<?php
header("Content-Type: application/json");
require_once "db_connection.php";

$rider_id = $_POST['rider_id'] ?? null;
$status   = $_POST['status'] ?? null;

if (!$rider_id || !$status) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing parameters"
    ]);
    exit;
}

$stmt = $conn->prepare("UPDATE riders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $rider_id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Rider status updated"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update rider status"
    ]);
}

$stmt->close();
$conn->close();
