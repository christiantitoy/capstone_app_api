<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db_connection.php'; // Make sure this connects to your DB

// Get POST parameters
$rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($rider_id <= 0 || !in_array($status, ['online', 'offline'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid rider_id or status"
    ]);
    exit;
}

// Prepare and execute update query
$stmt = $conn->prepare("UPDATE riders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $rider_id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Rider status updated successfully",
        "new_status" => $status
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update status"
    ]);
}

$stmt->close();
$conn->close();
?>
