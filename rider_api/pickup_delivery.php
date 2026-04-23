<?php
header("Content-Type: application/json");

require_once "/var/www/html/connection/db_connection.php"; // Updated to match your path

// ✅ Notification function (ADDED)
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// ✅ Get pickup notification message (ADDED)
function getPickupMessage($order_id) {
    return "Great news! Your order #$order_id has been picked up by our rider and is now on its way to you. Track your delivery in real-time!";
}

// ✅ Send pickup notification (ADDED)
function sendPickupNotification($conn, $delivery_id) {
    try {
        // Get order_id and buyer_id from order_deliveries and orders tables
        $sql = "
            SELECT o.id as order_id, o.buyer_id 
            FROM order_deliveries od
            JOIN orders o ON od.order_id = o.id
            WHERE od.id = ?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$delivery_id]);
        $delivery = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$delivery || !isset($delivery['buyer_id'])) {
            error_log("Pickup Notification: Could not find buyer_id for delivery $delivery_id");
            return false;
        }
        
        $buyer_id = $delivery['buyer_id'];
        $order_id = $delivery['order_id'];
        
        $title = "🛵 Order Picked Up!";
        $message = getPickupMessage($order_id);
        
        $result = sendPushNotification($buyer_id, $title, $message);
        error_log("Pickup notification sent for order $order_id: " . ($result['success'] ? 'Success' : 'Failed'));
        
        return $result['success'] ?? false;
        
    } catch (Exception $e) {
        error_log("Pickup Notification error: " . $e->getMessage());
        return false;
    }
}

try {
    // Handle both JSON and form-data input
    $delivery_id = null;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $delivery_id = $data['delivery_id'] ?? null;
    } else {
        // Regular form POST
        $delivery_id = $_POST['delivery_id'] ?? null;
    }

    if (!$delivery_id) {
        echo json_encode([
            "success" => false,
            "message" => "Missing delivery_id"
        ]);
        exit;
    }

    $sql = "
        UPDATE order_deliveries
        SET
            status = 'picked_up',
            picked_up_at = NOW()
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([$delivery_id]);

    if ($success) {
        // Check if any row was actually updated
        if ($stmt->rowCount() > 0) {
            
            // ✅ Send pickup notification (ADDED)
            $notification_sent = sendPickupNotification($conn, $delivery_id);
            
            echo json_encode([
                "success" => true,
                "message" => "Order picked up successfully",
                "notification_sent" => $notification_sent  // ✅ Added to response
            ]);
            
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Delivery not found or already picked up"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update status"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}

// No need to explicitly close the connection with PDO
?>