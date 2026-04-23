<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

// Function to call sendNotification.php internally
function sendPushNotification($user_id, $title, $message) {
    $url = 'https://capstone-app-api-r1ux.onrender.com/connection/notif/sendNotification.php';
    
    $data = json_encode([
        'user_id' => $user_id,
        'title' => $title,
        'message' => $message
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Don't block the main request
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// ✅ ADDED: Function to save notification directly to database with title
function saveNotification($conn, $user_id, $title, $message) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, notif_message, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $title, $message]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to save notification: " . $e->getMessage());
        return false;
    }
}

// Function to generate professional status messages
function getStatusMessage($order_id, $status) {
    $messages = [
        "packed" => "Great news! Your order #$order_id has been carefully packed and is ready for shipping. You'll receive tracking information once it's on the way.",
        
        "shipped" => "Your order #$order_id is on the way! It has been shipped and is now waiting for rider assignment. Track your delivery in real-time.",
        
        "delivered" => "Your order #$order_id has been delivered successfully! Thank you for shopping with DaguitZone. We hope you love your purchase!",
        
        "assigned" => "A rider has been assigned to your order #$order_id. They will pick up your package shortly.",
        
        "complete" => "Order #$order_id has been completed. Thank you for choosing DaguitZone! Please rate your experience.",
        
        "cancelled" => "Order #$order_id has been cancelled. If you have any questions, please contact our support team."
    ];
    
    return $messages[$status] ?? "Your order #$order_id status has been updated to: $status";
}

// Function to get notification title
function getStatusTitle($status) {
    $titles = [
        "packed" => "📦 Order Packed & Ready",
        "shipped" => "🚚 Order On The Way",
        "delivered" => "✅ Order Delivered",
        "assigned" => "🛵 Rider Assigned",
        "complete" => "🎉 Order Complete",
        "cancelled" => "❌ Order Cancelled"
    ];
    
    return $titles[$status] ?? "📋 Order Status Update";
}

// Statuses that should trigger notifications
function shouldSendNotification($status) {
    $notify_statuses = ["packed", "shipped", "delivered", "assigned", "complete", "cancelled"];
    return in_array($status, $notify_statuses);
}

try {
    // Get inputs
    $buyer_id = $_POST['buyer_id'] ?? $_GET['buyer_id'] ?? null;
    $order_id = $_POST['order_id'] ?? $_GET['order_id'] ?? null;
    $status   = $_POST['status'] ?? $_GET['status'] ?? null;

    if (!$buyer_id || !$order_id || !$status) {
        echo json_encode([
            "status" => "error",
            "message" => "buyer_id, order_id and status are required"
        ]);
        exit;
    }

    // Allowed statuses (match ENUM)
    $allowed_statuses = [
        "pending",
        "packed",
        "shipped",
        "delivered",
        "locked",
        "assigned",
        "reassigned",
        "complete",
        "cancelled"
    ];

    if (!in_array($status, $allowed_statuses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid status value"
        ]);
        exit;
    }

    $buyer_id = intval($buyer_id);
    $order_id = intval($order_id);

    // Update query
    $sql = "UPDATE orders
            SET status = :status, updated_at = NOW()
            WHERE id = :order_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':order_id' => $order_id
    ]);

    $rowCount = $stmt->rowCount();

    if ($rowCount > 0) {
        
        $notification_sent = false;
        $notification_result = null;
        $notification_saved = false; // ✅ ADDED
        
        // Send notification for specific statuses
        if (shouldSendNotification($status)) {
            $title = getStatusTitle($status);
            $message = getStatusMessage($order_id, $status);
            
            // ✅ ADDED: Save to database directly with title
            $notification_saved = saveNotification($conn, $buyer_id, $title, $message);
            
            // Call the notification API
            $notification_result = sendPushNotification($buyer_id, $title, $message);
            $notification_sent = $notification_result['success'] ?? false;
        }
        
        echo json_encode([
            "status" => "success",
            "message" => "Order status updated successfully",
            "order_id" => $order_id,
            "new_status" => $status,
            "notification_saved" => $notification_saved, // ✅ ADDED
            "notification_sent" => $notification_sent
        ]);
        
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Order not found or status already the same"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>