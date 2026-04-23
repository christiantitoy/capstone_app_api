<?php
// /connection/notif/mark_as_read.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '/var/www/html/connection/db_connection.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $notification_id = $input['notification_id'] ?? $_GET['notification_id'] ?? null;
    
    if (!$notification_id) {
        echo json_encode([
            "success" => false,
            "message" => "notification_id is required"
        ]);
        exit;
    }
    
    $sql = "UPDATE notifications SET is_read = true WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$notification_id]);
    
    echo json_encode([
        "success" => true,
        "message" => "Notification marked as read"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>