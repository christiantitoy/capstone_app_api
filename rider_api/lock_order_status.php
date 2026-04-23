<?php
header('Content-Type: application/json');

require '/var/www/html/connection/db_connection.php';

// ✅ Enable error logging
error_log("=== Update Order Status API Called ===");

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

// ✅ Get status message function (ADDED)
function getStatusMessage($order_id, $status) {
    $messages = [
        "assigned" => "Great news! A rider has been assigned to your order #$order_id. They will pick up your package shortly. Track your delivery in real-time!",
        
        "packed" => "Great news! Your order #$order_id has been carefully packed and is ready for shipping. You'll receive tracking information once it's on the way.",
        
        "shipped" => "Your order #$order_id is on the way! It has been shipped and is now waiting for rider assignment. Track your delivery in real-time.",
        
        "delivered" => "Your order #$order_id has been delivered successfully! Thank you for shopping with DaguitZone. We hope you love your purchase!",
        
        "complete" => "Order #$order_id has been completed. Thank you for choosing DaguitZone! Please rate your experience.",
        
        "cancelled" => "Order #$order_id has been cancelled. If you have any questions, please contact our support team."
    ];
    
    return $messages[$status] ?? "Your order #$order_id status has been updated to: $status";
}

// ✅ Get notification title function (ADDED)
function getStatusTitle($status) {
    $titles = [
        "assigned" => "🛵 Rider Assigned to Your Order",
        "packed" => "📦 Order Packed & Ready",
        "shipped" => "🚚 Order On The Way",
        "delivered" => "✅ Order Delivered",
        "complete" => "🎉 Order Complete",
        "cancelled" => "❌ Order Cancelled"
    ];
    
    return $titles[$status] ?? "📋 Order Status Update";
}

// ✅ Check if status should trigger notification (ADDED)
function shouldSendNotification($status) {
    $notify_statuses = ["assigned", "packed", "shipped", "delivered", "complete", "cancelled"];
    return in_array($status, $notify_statuses);
}

// ✅ Send notification function (ADDED)
function sendOrderNotification($conn, $order_id, $status) {
    try {
        // Get buyer_id from orders table
        $stmt = $conn->prepare("SELECT buyer_id FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order || !isset($order['buyer_id'])) {
            error_log("Notification: Could not find buyer_id for order $order_id");
            return ['saved' => false, 'sent' => false]; // ✅ MODIFIED return type
        }
        
        $buyer_id = $order['buyer_id'];
        
        if (shouldSendNotification($status)) {
            $title = getStatusTitle($status);
            $message = getStatusMessage($order_id, $status);
            
            // ✅ ADDED: Save to database directly with title
            $saved = saveNotification($conn, $buyer_id, $title, $message);
            error_log("Notification saved for order $order_id, status $status: " . ($saved ? 'Success' : 'Failed'));
            
            $result = sendPushNotification($buyer_id, $title, $message);
            $sent = $result['success'] ?? false;
            error_log("Notification sent for order $order_id, status $status: " . ($sent ? 'Success' : 'Failed'));
            
            return ['saved' => $saved, 'sent' => $sent]; // ✅ MODIFIED return type
        }
        
        return ['saved' => false, 'sent' => false]; // ✅ MODIFIED return type
        
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
        return ['saved' => false, 'sent' => false]; // ✅ MODIFIED return type
    }
}

try {
    // Handle both JSON and form-data input
    $order_id = 0;
    $status = '';
    $rider_id = 0;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
        $status = isset($data['status']) ? $data['status'] : '';
        $rider_id = isset($data['rider_id']) ? intval($data['rider_id']) : 0;
    } else {
        // Regular form POST
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
    }

    // ✅ Log received parameters
    error_log("Received - order_id: $order_id, status: $status, rider_id: $rider_id");

    if (!$order_id || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    // Allowed statuses
    $allowed_status = ['pending', 'packed', 'shipped', 'delivered', 'locked', 'assigned', 'reassinged', 'complete', 'cancelled'];
    if (!in_array($status, $allowed_status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit;
    }

    // Check rider status BEFORE locking
    if ($status === 'locked') {
        
        error_log("Processing LOCK request for rider_id: $rider_id");
        
        // Require rider_id when locking
        if (!$rider_id) {
            error_log("ERROR: Missing rider_id");
            echo json_encode([
                'status' => 'error', 
                'message' => 'Rider ID is required to lock an order'
            ]);
            exit;
        }

        // Check rider's current status
        $riderStmt = $conn->prepare("SELECT id, status FROM riders WHERE id = :rider_id");
        $riderStmt->execute([':rider_id' => $rider_id]);
        $rider = $riderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$rider) {
            error_log("ERROR: Rider not found - ID: $rider_id");
            echo json_encode([
                'status' => 'error',
                'message' => 'Rider not found'
            ]);
            exit;
        }

        // ✅ Log rider's current status
        error_log("Rider found - ID: {$rider['id']}, Status: {$rider['status']}");

        // ✅ Check if rider is delivering (was 'busy')
        if ($rider['status'] === 'delivering') {
            error_log("BLOCKED: Rider is delivering");
            echo json_encode([
                'status' => 'error',
                'message' => 'You are currently on a delivery. Complete it before accepting new orders.'
            ]);
            exit;
        }

        // ✅ Check if rider is offline
        if ($rider['status'] === 'offline') {
            error_log("BLOCKED: Rider is offline");
            echo json_encode([
                'status' => 'error',
                'message' => 'You are offline. Please go online to accept orders.'
            ]);
            exit;
        }

        error_log("Rider status OK - Proceeding to lock order");
        
        // All good - lock the order
        $stmt = $conn->prepare("UPDATE orders SET status = ?, locked_at = NOW() WHERE id = ?");
        $success = $stmt->execute([$status, $order_id]);
        
    } else {
        // For other statuses, just update status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $success = $stmt->execute([$status, $order_id]);
    }

    if ($success && $stmt->rowCount() > 0) {
        
        // ✅ Send notification if applicable (ADDED)
        $notification_result = ['saved' => false, 'sent' => false]; // ✅ MODIFIED
        if (shouldSendNotification($status)) {
            $notification_result = sendOrderNotification($conn, $order_id, $status); // ✅ MODIFIED
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => "Order $order_id updated to $status",
            'notification_saved' => $notification_result['saved'], // ✅ ADDED
            'notification_sent' => $notification_result['sent']    // ✅ MODIFIED
        ]);
        
    } elseif ($success && $stmt->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "Order not found or status unchanged"
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update order'
        ]);
    }

} catch (PDOException $e) {
    error_log("PDO Exception: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Exception: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>